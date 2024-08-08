<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\MeetingParticipant;
use App\Enum\UserRoleEnum;
use App\Form\MeetingType;
use App\Message\MeetingCancelledMessage;
use App\Repository\MeetingRepository;
use App\Security\Voter\MeetingVoter;
use App\Trait\PaginatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/meeting')]
class MeetingController extends AbstractController
{
    use PaginatorTrait;

    public function __construct(private readonly MessageBusInterface $messageBus) {}

    #[Route('', name: 'app_meeting')]
    public function index(Request $request, MeetingRepository $meetingRepository): Response
    {
        $page = (int) $request->get('page', 1);
        $limit = $this->getParameter('paginator_limit');

        $meetingsQuery = $meetingRepository->getMeetings($this->getUser());
        $meetingsPaginator = $this->paginate($meetingsQuery, $page, $limit);
        $paginationData = $this->getPaginationData($meetingsPaginator, $request, $page, $limit);

        return $this->render('meeting/index.html.twig', [
            'paginationData' => $paginationData,
            'meetings' => $meetingsPaginator->getIterator(),
        ]);
    }

    #[IsGranted(UserRoleEnum::MeetingManager->value)]
    #[Route('/create', name: 'app_meeting_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $meeting = new Meeting();
        $meetingParticipant = new MeetingParticipant();
        $meeting->addMeetingParticipant($meetingParticipant);
        $form = $this->createForm(MeetingType::class, $meeting);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $meeting->setCreatedBy($this->getUser());
            $meeting->removeDuplicateParticipants();

            $em->persist($meeting);
            $em->flush();

            $this->addFlash('success', 'meeting.create.message.success');

            return $this->redirectToRoute('app_meeting_show', ['meeting' => $meeting->getId()]);
        }

        return $this->render('meeting/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted(MeetingVoter::VIEW, subject: 'meeting')]
    #[Route('/{meeting}', name: 'app_meeting_show', requirements: ['meeting' => Requirement::POSITIVE_INT])]
    public function show(Meeting $meeting): Response
    {
        return $this->render('meeting/show.html.twig', [
            'meeting' => $meeting,
        ]);
    }

    #[IsGranted('OWNER', subject: 'meeting')]
    #[Route('/{meeting}/update', name: 'app_meeting_update')]
    public function update(Meeting $meeting, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MeetingType::class, $meeting);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $meeting->removeDuplicateParticipants();
            $em->flush();

            $this->addFlash('success', 'meeting.update.message.success');

            return $this->redirectToRoute('app_meeting_show', ['meeting' => $meeting->getId()]);
        }

        return $this->render('meeting/update.html.twig', [
            'form' => $form,
            'meeting' => $meeting,
        ]);
    }

    #[IsGranted('OWNER', subject: 'meeting')]
    #[Route('/{meeting}/cancel', name: 'app_meeting_cancel', methods: 'POST')]
    public function cancel(Meeting $meeting, EntityManagerInterface $em): Response
    {
        $meeting->setCancelled(true);
        $em->flush();

        $this->addFlash('success', 'meeting.cancel.message.success');

       $this->messageBus->dispatch(new MeetingCancelledMessage($meeting->getId())); 

        return $this->redirectToRoute('app_meeting_show', ['meeting' => $meeting->getId()]);
    }
}
