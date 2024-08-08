<?php

namespace App\Enum;

use App\Service\CaseConverter;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum MeetingVisibilityEnum: string implements TranslatableInterface
{
    case Public = 'public';

    case Private = 'private';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('meeting.visibility.' . CaseConverter::pascalToSnake($this->name), locale: $locale);
    }
}
