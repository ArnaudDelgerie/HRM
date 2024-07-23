<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserStateEnum;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (UserStateEnum::Enabled !== $user->getEnumState()) {
            throw new CustomUserMessageAccountStatusException('auth.user_checker_not_enabled');
        }
    }

    public function checkPostAuth(UserInterface $user): void {}
}
