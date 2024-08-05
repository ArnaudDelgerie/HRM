<?php

namespace App\MessageHandler;

use App\Entity\LeaveRequest;
use App\Message\LeaveRequestRejectedMessage;
use App\Repository\LeaveRequestRepository;
use App\Service\Mailer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final class LeaveRequestRejectedHandler
{
    public function __construct(
        private readonly Mailer $mailer,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly LeaveRequestRepository $leaveRequestRepository,
    ) {}

    public function __invoke(LeaveRequestRejectedMessage $message): void
    {
        $id = $message->leaveRequestId;
        $leaveRequest = $this->leaveRequestRepository->find($id);

        if (!$leaveRequest instanceof LeaveRequest) return;

        $subject = $this->translator->trans('leave_request.email.rejected.subject');
        $username = $leaveRequest->getUser()->getUsername();
        $userShowLeaveUrl = $this->router->generate('app_user_show_leave', [
            'user' => $leaveRequest->getUser()->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->mailer->sendTemplatedEmail($leaveRequest->getUser()->getEmail(), $subject, '/leave_request/mail/rejected.html.twig', [
            'username' => $username,
            'userShowLeaveUrl' => $userShowLeaveUrl,
        ]);
    }
}

