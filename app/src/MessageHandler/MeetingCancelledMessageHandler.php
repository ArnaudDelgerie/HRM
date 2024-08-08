<?php

namespace App\MessageHandler;

use App\Entity\Meeting;
use App\Message\MeetingCancelledMessage;
use App\Repository\MeetingRepository;
use App\Service\Mailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class MeetingCancelledMessageHandler
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly MeetingRepository $meetingRepository,
    ) {}

    public function __invoke(MeetingCancelledMessage $message): void
    {
        $id = $message->meetingId;
        $meeting = $this->meetingRepository->find($id);

        if (!$meeting instanceof Meeting) return;

        $subject = $this->translator->trans('meeting.email.cancelled.subject');
        $meetingShowUrl = $this->router->generate('app_meeting_show', [
            'meeting' => $meeting->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        foreach ($meeting->getMeetingParticipants() as $participant) {
            $username = $participant->getUser()->getUsername();
            $this->mailer->sendTemplatedEmail($participant->getUser()->getEmail(), $subject, '/meeting/mail/cancelled.html.twig', [
                'username' => $username,
                'name' => $meeting->getName(),
                'meetingShowUrl' => $meetingShowUrl,
            ]);
        }
    }
}
