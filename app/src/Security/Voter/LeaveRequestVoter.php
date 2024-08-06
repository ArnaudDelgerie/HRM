<?php

namespace App\Security\Voter;

use App\Entity\LeaveRequest;
use App\Entity\User;
use App\Enum\LeaveRequestStateEnum;
use App\Enum\UserRoleEnum;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class LeaveRequestVoter extends Voter
{
    public const EDIT = 'EDIT';

    public const DELETE = 'DELETE';

    public const ACTIONS = [
        self::EDIT,
        self::DELETE,
    ];

    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker) {}

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::ACTIONS) && $subject instanceof LeaveRequest;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->candDelete($subject, $user),
            default => throw new LogicException()
        };
    }

    private function canEdit(LeaveRequest $leaveRequest, User $user): bool
    {
        if (
            $leaveRequest->getState() === LeaveRequestStateEnum::Pending->value
            &&
            $leaveRequest->getUser()->getId() === $user->getId()
        ) {
            return true;
        }

        if (
            $leaveRequest->getState() !== LeaveRequestStateEnum::Pending->value 
            &&
            $this->authorizationChecker->isGranted(UserRoleEnum::LeaveManager->value, $user)
        ) {
            return true;
        }

        return false;
    }

    private function candDelete(LeaveRequest $leaveRequest, User $user): bool
    {
        return $leaveRequest->getUser()->getId() === $user->getId()
            && $leaveRequest->getState() === LeaveRequestStateEnum::Pending->value;
    }
}
