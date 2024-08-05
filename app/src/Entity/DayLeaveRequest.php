<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Enum\DayLeaveRequestPeriodEnum;
use App\Repository\DayLeaveRequestRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['leaveRequest', 'dayDate'], errorPath: 'dayDate', message: 'day_leave_request.leave_request_day_date.assert.unique')]
#[ORM\Entity(repositoryClass: DayLeaveRequestRepository::class)]
class DayLeaveRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dayLeaveRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LeaveRequest $leaveRequest = null;

    #[Assert\NotBlank(message: 'day_leave_request.day_date.assert.not_blank')]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $dayDate = null;

    #[ORM\Column(type: Types::STRING, enumType: DayLeaveRequestPeriodEnum::class)]
    private DayLeaveRequestPeriodEnum $period = DayLeaveRequestPeriodEnum::AllDay;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLeaveRequest(): ?LeaveRequest
    {
        return $this->leaveRequest;
    }

    public function setLeaveRequest(?LeaveRequest $leaveRequest): static
    {
        $this->leaveRequest = $leaveRequest;

        return $this;
    }

    public function getDayDate(): ?DateTimeInterface
    {
        return $this->dayDate;
    }

    public function setDayDate(DateTimeInterface $dayDate): static
    {
        $this->dayDate = $dayDate;

        return $this;
    }

    public function getPeriod(): DayLeaveRequestPeriodEnum
    {
        return $this->period;
    }

    public function setPeriod(DayLeaveRequestPeriodEnum $period): static
    {
        $this->period = $period;

        return $this;
    }
}
