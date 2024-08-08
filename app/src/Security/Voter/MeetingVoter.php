<?php

namespace App\Security\Voter;

use App\Entity\Meeting;
use App\Entity\User;
use App\Enum\MeetingVisibilityEnum;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MeetingVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const ACTIONS = [
        self::VIEW
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::ACTIONS) && $subject instanceof Meeting;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            default => throw new LogicException()
        };

        return false;
    }

    private function canView(Meeting $meeting, User $user): bool
    {
        if ($meeting->getVisibility() === MeetingVisibilityEnum::Public) {
            return true;
        }

        if ($meeting->getCreatedBy()->getId() === $user->getId()) {
            return true;
        }
        
        return $meeting->isMeetingParticipant($user); 
    }
}
