<?php

namespace App\Message;

final class MeetingCancelledMessage
{
    public function __construct(public readonly int $meetingId) {}
}
