<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRoleEnum;
use App\Form\CreateUserType;
use App\Form\UpdateUserType;
use App\Form\UserPasswordType;
use App\Message\UserInvitationMessage;
use App\Repository\LeaveRequestRepository;
use App\Repository\MeetingRepository;
use App\Repository\UserRepository;
use App\Trait\PaginatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    use PaginatorTrait;

    public function __construct(private readonly MessageBusInterface $messageBus) {}

    #[Route('', name: 'app_user')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $page = (int) $request->get('page', 1);
        $limit = $this->getParameter('paginator_limit');

        $usersQuery = $userRepository->getUsers();
        $userPaginator = $this->paginate($usersQuery, $page, $limit);
        $paginationData = $this->getPaginationData($userPaginator, $request, $page, $limit);

        return $this->render('user/index.html.twig', [
            'users' => $userPaginator->getIterator(),
            'paginationData' => $paginationData,
        ]);
    }

    #[Route('/{user}/leave', name: 'app_user_show_leave', requirements: ['user' => Requirement::POSITIVE_INT])]
    public function showLeave(User $user, Request $request, LeaveRequestRepository $leaveRequestRepository): Response
    {
        $page = (int) $request->get('page', 1);
        $limit = $this->getParameter('paginator_limit');

        $leaveRequestsQuery = $leaveRequestRepository->getLeaveRequestsByUser($user);
        $leaveRequestsPaginator = $this->paginate($leaveRequestsQuery, $page, $limit);
        $paginationData = $this->getPaginationData($leaveRequestsPaginator, $request, $page, $limit);

        return $this->render('user/show_leave.html.twig', [
            'user' => $user,
            'paginationData' => $paginationData,
            'leaveRequests' => $leaveRequestsPaginator->getIterator(),
        ]);
    }

    #[Route('/{user}/meeting', name: 'app_user_show_meeting', requirements: ['user' => Requirement::POSITIVE_INT])]
    public function showMeeting(User $user, Request $request, MeetingRepository $meetingRepository): Response
    {
        $page = (int) $request->get('page', 1);
        $limit = $this->getParameter('paginator_limit');

        /** @var User $loggedUser */
        $loggedUser = $this->getUser();
        $meetingQuery = $meetingRepository->getUserMeetings($user, $loggedUser->getId() === $user->getId());
        $meetingPaginator = $this->paginate($meetingQuery, $page, $limit);
        $paginationData = $this->getPaginationData($meetingPaginator, $request, $page, $limit);

        return $this->render('user/show_meeting.html.twig', [
            'user' => $user,
            'paginationData' => $paginationData,
            'meetings' => $meetingPaginator->getIterator(),
        ]);
    }

    #[IsGranted(UserRoleEnum::UserManager->value)]
    #[Route('/create', name: 'app_user_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $user = (new User())->setPassword(bin2hex(random_bytes(16)));
        $form = $this->createForm(CreateUserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();

            if ($form->get('invite')->getData()) {
                $this->messageBus->dispatch(new UserInvitationMessage($user->getId()));
                $this->addFlash('success', 'user.create_and_invite.message.success');
            } else {
                $this->addFlash('success', 'user.create.message.success');
            }

            return $this->redirectToRoute('app_user_show_leave', ['user' => $user->getId()]);
        }

        return $this->render('user/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted(
        new Expression(
            'is_granted("' . UserRoleEnum::UserManager->value . '") or ' .
            'is_granted("OWNER", subject)'
        ),
        subject: 'user'
    )]
    #[Route('/{user}/update', name: 'app_user_update')]
    public function update(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UpdateUserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'user.update.message.success');

            return $this->redirectToRoute('app_user_show_leave', ['user' => $user->getId()]);
        }

        return $this->render('user/update.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[IsGranted('OWNER', 'user')]
    #[Route('/{user}/update-password', name: 'app_user_update_password')]
    public function updatePassword(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UserPasswordType::class, $user);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $em->flush();

            $this->addFlash('success', 'user.update_password.message.success');

            return $this->redirectToRoute('app_user_show_leave', ['user' => $user->getId()]);
        }

        return $this->render('user/update_password.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted(UserRoleEnum::UserManager->value)]
    #[Route('/{user}/invite', name: 'app_user_invite', methods:['POST'])]
    public function invite(Request $request, User $user, WorkflowInterface $userStateMachine): Response
    {
        if (!$userStateMachine->can($user, 'invite')) {
            return $this->redirectToRoute('app_user');
        }

        $this->messageBus->dispatch(new UserInvitationMessage($user->getId()));
        $this->addFlash('success', 'user.invite.message.success');

        return $this->redirect($request->headers->get('referer'));
    }

    #[IsGranted(UserRoleEnum::UserManager->value)]
    #[Route('/{user}/disable', name: 'app_user_disable', methods:['POST'])]
    public function disable(Request $request, User $user, WorkflowInterface $userStateMachine, EntityManagerInterface $em): Response
    {
        if (!$userStateMachine->can($user, 'disable')) {
            return $this->redirectToRoute('app_user');
        }

        $userStateMachine->apply($user, 'disable');
        $user->setValidationToken(null);
        $em->flush();

        $this->addFlash('success', 'user.disable.message.success');

        return $this->redirect($request->headers->get('referer'));
    }
}
