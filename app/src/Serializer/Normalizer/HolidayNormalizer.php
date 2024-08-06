<?php

namespace App\Serializer\Normalizer;

use DateTime;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Umulmrum\Holiday\Model\Holiday;

class HolidayNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Holiday $object */
        $data = [
            'id' => $object->getDate()->getTimestamp(),
            'calendarId' => $object->getName(),
            'title' => str_replace('_', ' ', $object->getName()),
            'start' => $object->getDate()->format(DateTime::ATOM),
            'end' => $object->getDate()->format(DateTime::ATOM),
            'category' => 'allday',
            'isReadOnly' => true,
        ];

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Holiday;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Holiday::class => true];
    }
}
