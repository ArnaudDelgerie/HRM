<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Interface\OwnedInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string,mixed>
 */
class OwnerVoter extends Voter
{
    public const OWNER = 'OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::OWNER]) && $subject instanceof OwnedInterface;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }
        
        /** @var OwnedInterface $subject */
        return $user->getId() === $subject->getOwner()->getId(); 
    }
}
