<?php

namespace App\Enum;

use App\Service\CaseConverter;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum UserRoleEnum: string implements TranslatableInterface
{
    case User = 'ROLE_USER';

    case Admin = 'ROLE_ADMIN';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('user.role.' . CaseConverter::pascalToSnake($this->name), locale: $locale);
    }
}
