<?php

namespace App\Message;

final class LeaveRequestRejectedMessage
{
    public function __construct(public readonly int $leaveRequestId) {}
}

