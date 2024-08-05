<?php

namespace App\Message;

final class LeaveRequestAcceptedMessage
{
    public function __construct(public readonly int $leaveRequestId) {}
}

