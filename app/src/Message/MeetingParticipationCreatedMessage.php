<?php

namespace App\Message;

final class MeetingParticipationCreatedMessage
{
    public function __construct(public readonly int $meetingParticipantId) {}
}
