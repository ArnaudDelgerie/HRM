<?php

namespace App\Enum;

use App\Service\CaseConverter;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum UserRoleEnum: string implements TranslatableInterface
{
    case User = 'ROLE_USER';

    case Admin = 'ROLE_ADMIN';
    
    case UserManager = 'ROLE_USER_MANAGER';
    
    case MeetingManager = 'ROLE_MEETING_MANAGER';
    
    case LeaveManager = 'ROLE_LEAVE_MANAGER';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans(self::transKey($this->name), locale: $locale);
    }

    public static function formChoices(): array
    {
        $formChoices = [];
        foreach(self::cases() as $enum) {
            if($enum === self::User) continue;

            $formChoices[self::transKey($enum->name)] = $enum->value;
        }

        return $formChoices;
    }

    public static function transKey(string $caseName): string
    {
        return 'user.role.' . CaseConverter::pascalToSnake($caseName);
    }
}
