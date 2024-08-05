<?php

namespace App\Controller;

use App\Entity\DayLeaveRequest;
use App\Entity\LeaveRequest;
use App\Entity\User;
use App\Enum\DayLeaveRequestPeriodEnum;
use App\Enum\LeaveRequestStateEnum;
use App\Form\LeaveRequestType;
use App\Form\ManageLeaveRequestType;
use App\Message\LeaveRequestAcceptedMessage;
use App\Message\LeaveRequestCreatedMessage;
use App\Message\LeaveRequestRejectedMessage;
use App\Repository\LeaveRequestRepository;
use App\Security\Voter\LeaveRequestVoter;
use App\Trait\PaginatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/leave-request')]
class LeaveRequestController extends AbstractController
{
    use PaginatorTrait;

    public function __construct(private readonly MessageBusInterface $messageBus) {}

    #[Route('', name: 'app_leave_request')]
    public function index(Request $request, LeaveRequestRepository $leaveRequestRepository): Response
    {
        $page = (int) $request->get('page', 1);
        $limit = $this->getParameter('paginator_limit');

        $leaveRequestsQuery = $leaveRequestRepository->getLeaveRequests();
        $leaveRequestsPaginator = $this->paginate($leaveRequestsQuery, $page, $limit);
        $paginationData = $this->getPaginationData($leaveRequestsPaginator, $request, $page, $limit);

        return $this->render('leave_request/index.html.twig', [
            'paginationData' => $paginationData,
            'leaveRequests' => $leaveRequestsPaginator->getIterator(),
        ]);
    }

    #[Route('/create', name: 'app_leave_request_create')]
    public function create(Request $request, EntityManagerInterface $em, WorkflowInterface $leaveRequestStateMachine): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $leaveRequest = new LeaveRequest();
        $dayLeaveRequest = new DayLeaveRequest();
        $leaveRequest->addDayLeaveRequest($dayLeaveRequest);
        $form = $this->createForm(LeaveRequestType::class, $leaveRequest);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $leaveRequest->setUser($user);
            $this->computeLeaveRequest($leaveRequest);

            if ($leaveRequestStateMachine->can($leaveRequest, 'accept')) {
                $leaveRequestStateMachine->apply($leaveRequest, 'accept');
            }

            $em->persist($leaveRequest);
            $em->flush();

            if ($leaveRequest->getState() === LeaveRequestStateEnum::Accepted->value) {
                $this->addFlash('success', 'leave_request.manage.message.accept_success');
                $leaveRequest->setProcessedBy($this->getUser());
                $em->flush();
            } else {
                $this->addFlash('success', 'leave_request.create.message.success');
                $this->messageBus->dispatch(new LeaveRequestCreatedMessage($leaveRequest->getId()));
            }

            return $this->redirectToRoute('app_user_show_leave', ['user' => $user->getId()]);
        }

        return $this->render('leave_request/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted(LeaveRequestVoter::EDIT, subject: 'leaveRequest')]
    #[Route('/{leaveRequest}/update', name: 'app_leave_request_update')]
    public function update(LeaveRequest $leaveRequest, Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(LeaveRequestType::class, $leaveRequest);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->computeLeaveRequest($leaveRequest);
            $em->flush();

            $this->addFlash('success', 'leave_request.update.message.success');

            return $this->redirectToRoute('app_user_show_leave', ['user' => $user->getId()]);
        }

        return $this->render('leave_request/update.html.twig', [
            'form' => $form,
            'leaveRequest' => $leaveRequest,
        ]);
    }

    #[IsGranted(
        new Expression("workflow_can(subject, 'accept') or workflow_can(subject, 'reject')"), 
        subject: 'leaveRequest'
    )]
    #[Route('/{leaveRequest}/manage', name: 'app_leave_request_manage')]
    public function manage(LeaveRequest $leaveRequest, Request $request, WorkflowInterface $leaveRequestStateMachine, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ManageLeaveRequestType::class, $leaveRequest);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $leaveRequest->setProcessedBy($this->getUser());

            /** @var SubmitButton $submitAccept */
            $submitAccept = $form->get('submitAccept');
            if ($submitAccept->isClicked() && $leaveRequestStateMachine->can($leaveRequest, 'accept')) {
                $leaveRequestStateMachine->apply($leaveRequest, 'accept');
                $em->flush();
                $this->messageBus->dispatch(new LeaveRequestAcceptedMessage($leaveRequest->getId()));
                $this->addFlash('success', 'leave_request.manage.message.accept_success');
            }

            /** @var SubmitButton $submitReject */
            $submitReject = $form->get('submitReject');
            if ($submitReject->isClicked() && $leaveRequestStateMachine->can($leaveRequest, 'reject')) {
                $leaveRequestStateMachine->apply($leaveRequest, 'reject');
                $em->flush();
                $this->messageBus->dispatch(new LeaveRequestRejectedMessage($leaveRequest->getId()));
                $this->addFlash('success', 'leave_request.manage.message.reject_success');
            }

            return $this->redirectToRoute('app_leave_request');
        }

        return $this->render('/leave_request/manage.html.twig', [
            'form' => $form,
            'leaveRequest' => $leaveRequest,
        ]);
    }

    #[IsGranted(LeaveRequestVoter::DELETE, subject: 'leaveRequest')]
    #[Route('/{leaveRequest}/delete', name: 'app_leave_request_delete')]
    public function delete(LeaveRequest $leaveRequest, EntityManagerInterface $em): Response
    {
        $user = $leaveRequest->getUser();

        $em->remove($leaveRequest);
        $em->flush();

        $this->addFlash('success', 'leave_request.delete.message.success');

        return $this->redirectToRoute('app_user_show_leave', ['user' => $user->getId()]);
    }

    private function computeLeaveRequest(LeaveRequest $leaveRequest): void
    {
        $nbDays = 0;
        $firstDay = $lastDay = null;
        /** @var DayLeaveRequest $dayLeaveRequest */
        foreach ($leaveRequest->getDayLeaveRequests() as $dayLeaveRequest) {
            if ($dayLeaveRequest->getPeriod() === DayLeaveRequestPeriodEnum::AllDay) {
                $nbDays += 1;
            } else {
                $nbDays += 0.5;
            }

            if (null === $firstDay || $firstDay > $dayLeaveRequest->getDayDate()) {
                $firstDay = $dayLeaveRequest->getDayDate();
            }

            if (null === $lastDay || $lastDay < $dayLeaveRequest->getDayDate()) {
                $lastDay = $dayLeaveRequest->getDayDate();
            }
        }

        $leaveRequest->setNbDays($nbDays)->setLastDay($lastDay)->setFirstDay($firstDay);
    }
}
