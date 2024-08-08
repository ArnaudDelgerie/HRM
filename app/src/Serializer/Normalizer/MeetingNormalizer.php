<?php

namespace App\Serializer\Normalizer;

use App\Entity\Meeting;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MeetingNormalizer implements NormalizerInterface
{
    public function __construct(private readonly RouterInterface $router)
    {
        
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Meeting $object */
        $data = [
            'id' => $this->router->generate('app_meeting_show', [
                'meeting' => $object->getId()
            ]),
            'calendarId' => 'meeting',
            'title' => $object->getName(),
            'body' => $object->getDescription(),
            'start' => $object->getStartAt()->format('Y-m-d H:i:s'),
            'end' => $object->getEndAt()->format('Y-m-d H:i:s'),
            'isReadOnly' => true,
            'category' => 'time',
        ];

        return $data;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Meeting;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Meeting::class => true];
    }
}
