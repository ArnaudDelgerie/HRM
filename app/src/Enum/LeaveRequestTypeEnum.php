<?php

namespace App\Enum;

use App\Service\CaseConverter;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum LeaveRequestTypeEnum: string implements TranslatableInterface
{
    case Paid = 'paid';

    case Unpaid = 'unpaid';

    case Maternity = 'maternity';

    case Parental = 'parental';

    case Sick = 'sick';

    case WorkerCompensationLeave = 'worker_compensation_leave';

    public function trans(TranslatorInterface $translator, string $locale = null): string
    {
        return $translator->trans('leave_request.type.' . CaseConverter::pascalToSnake($this->name), locale: $locale);
    }
}
