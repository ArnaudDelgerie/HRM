<?php

namespace App\Serializer\Normalizer;

use App\Entity\DayLeaveRequest;
use DateTime;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DayLeaveRequestNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var DayLeaveRequest $object */
        $leaveRequest = $object->getLeaveRequest();
        $user = $leaveRequest->getUser();
        $data = [
            'id' => $object->getId(),
            'calendarId' => 'day_leave_request_' . $object->getId(),
            'title' => $leaveRequest->getType()->trans($this->translator) . ' - '. $user->getUsername() . ' - ' . $object->getPeriod()->trans($this->translator),
            'start' => $object->getDayDate()->format(DateTime::ATOM),
            'end' => $object->getDayDate()->format(DateTime::ATOM),
            'isReadOnly' => true,
            'category' => 'allday'
        ];

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof DayLeaveRequest;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [DayLeaveRequest::class => true];
    }
}
