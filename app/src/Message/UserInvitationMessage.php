<?php

namespace App\Message;

final class UserInvitationMessage
{
    public function __construct(public readonly int $userId) {}
}
