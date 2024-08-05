<?php

namespace App\Enum;

use App\Service\CaseConverter;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LeaveRequestStateEnum: string implements TranslatableInterface
{
    case Pending = 'pending';

    case Accepted = 'accepted';

    case Rejected = 'rejected';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('leave_request.state.' . CaseConverter::pascalToSnake($this->name), locale: $locale);
    }
}
