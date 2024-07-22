<?php

namespace App\Enum;

use App\Service\CaseConverter;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum UserStateEnum: string implements TranslatableInterface
{
    case Created = 'created';

    case Invited = 'invited';

    case Enabled = 'enabled';

    case Disabled = 'disabled';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('user.state.' . CaseConverter::pascalToSnake($this->name), locale: $locale);
    }
}
