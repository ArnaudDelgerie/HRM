<?php

namespace App\MessageHandler;

use App\Entity\MeetingParticipant;
use App\Message\MeetingParticipationCreatedMessage;
use App\Repository\MeetingParticipantRepository;
use App\Service\Mailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class MeetingParticipationCreatedMessageHandler
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly MeetingParticipantRepository $meetingParticipantRepository,
    ) {}

    public function __invoke(MeetingParticipationCreatedMessage $message): void
    {
        $id = $message->meetingParticipantId;
        $meetingParticipant = $this->meetingParticipantRepository->find($id);

        if (!$meetingParticipant instanceof MeetingParticipant) return;
        
        $subject = $this->translator->trans('meeting.email.participation_created.subject');
        $username = $meetingParticipant->getUser()->getUsername();
        $meetingShowUrl = $this->router->generate('app_meeting_show', [
            'meeting' => $meetingParticipant->getMeeting()->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendTemplatedEmail($meetingParticipant->getUser()->getEmail(), $subject, '/meeting/mail/participant_created.html.twig', [
            'username' => $username,
            'name' => $meetingParticipant->getMeeting()->getName(),
            'meetingShowUrl' => $meetingShowUrl,
        ]);
    }
}
