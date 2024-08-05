<?php

namespace App\MessageHandler;

use App\Entity\LeaveRequest;
use App\Enum\UserRoleEnum;
use App\Message\LeaveRequestCreatedMessage;
use App\Repository\LeaveRequestRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class LeaveRequestCreatedHandler
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly LeaveRequestRepository $leaveRequestRepository,
    ) {}

    public function __invoke(LeaveRequestCreatedMessage $message): void
    {
        $id = $message->leaveRequestId;
        $leaveRequest = $this->leaveRequestRepository->find($id);

        if (!$leaveRequest instanceof LeaveRequest) return;
        
        $subject = $this->translator->trans('leave_request.email.created.subject');
        $leaveRequestUsername = $leaveRequest->getUser()->getUsername();
        $leaveRequestManageUrl = $this->router->generate('app_leave_request_manage', [
            'leaveRequest' => $leaveRequest->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $leaveManagers = $this->userRepository->getUsersByRole([UserRoleEnum::LeaveManager->value, UserRoleEnum::Admin->value]);
        foreach ($leaveManagers->getResult() as $leaveManager) {
            $this->mailer->sendTemplatedEmail($leaveManager->getEmail(), $subject, '/leave_request/mail/created.html.twig', [
                'username' => $leaveManager->getUsername(),
                'leaveRequestUsername' => $leaveRequestUsername, 
                'leaveRequestManageUrl' => $leaveRequestManageUrl,
            ]);
        }
    }
}

