<?php

namespace App\Message;

final class LeaveRequestCreatedMessage
{
    public function __construct(public readonly int $leaveRequestId) {}
}

