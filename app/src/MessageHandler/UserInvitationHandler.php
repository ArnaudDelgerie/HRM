<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\UserInvitationMessage;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class UserInvitationHandler
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly WorkflowInterface $userStateMachine,
    ) {}

    public function __invoke(UserInvitationMessage $message): void
    {
        $userId = $message->userId;
        $user = $this->userRepository->find($userId);

        if(!$user instanceof User) return;
        if(!$this->userStateMachine->can($user, 'invite')) return;

        $token = bin2hex(random_bytes(16));
        $subject = $this->translator->trans('user.email.invitation.subject');
        $sended = $this->mailer->sendTemplatedEmail($user->getEmail(), $subject, '/user/mail/invitation.html.twig', [
            'username' => $user->getUsername(),
            'registrationCompletionUrl' => $this->router->generate('app_auth_registration_completion', [
                'token' => $token
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        if($sended) {
            $user->setValidationToken($token);
            $this->userStateMachine->apply($user, 'invite');
            $this->em->flush();
        }
    }
}
