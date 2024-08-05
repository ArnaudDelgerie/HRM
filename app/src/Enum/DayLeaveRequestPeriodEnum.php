<?php

namespace App\Enum;

use App\Service\CaseConverter;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum DayLeaveRequestPeriodEnum: string implements TranslatableInterface
{
    case AllDay = 'all_day';

    case Morning = 'morning';

    case Afternoon = 'afternoon';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('day_leave_request.period.' . CaseConverter::pascalToSnake($this->name), locale: $locale);
    }
}
