<?php

namespace App\EventListener;

use App\Entity\MeetingParticipant;
use App\Message\MeetingParticipationCreatedMessage;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: MeetingParticipant::class)]
final class MeetingParticipantListener
{
    public function __construct(private readonly MessageBusInterface $messageBus) {}

    public function postPersist(MeetingParticipant $meetingParticipant): void {
        $this->messageBus->dispatch(new MeetingParticipationCreatedMessage($meetingParticipant->getId()));
    }
}

