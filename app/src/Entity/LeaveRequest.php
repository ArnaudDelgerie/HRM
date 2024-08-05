<?php

namespace App\Entity;

use App\Enum\LeaveRequestStateEnum;
use App\Enum\LeaveRequestTypeEnum;
use App\Repository\LeaveRequestRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: LeaveRequestRepository::class)]
class LeaveRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: DayLeaveRequest::class, mappedBy: 'leaveRequest', cascade: ['persist'], orphanRemoval: true)]
    private Collection $dayLeaveRequests;

    #[ORM\ManyToOne(inversedBy: 'leaveRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?User $processedBy = null;

    #[ORM\Column(type: Types::STRING, enumType: LeaveRequestTypeEnum::class)]
    private LeaveRequestTypeEnum $type = LeaveRequestTypeEnum::Paid;

    #[ORM\Column]
    private ?float $nbDays = null;

    #[ORM\Column(type: Types::STRING, enumType: LeaveRequestStateEnum::class)]
    private LeaveRequestStateEnum $state = LeaveRequestStateEnum::Pending;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requestComment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $responseComment = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $firstDay = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $lastDay = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->dayLeaveRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayLeaveRequests(): Collection
    {
        return $this->dayLeaveRequests;
    }

    public function addDayLeaveRequest(DayLeaveRequest $dayLeaveRequest): static
    {
        if (!$this->dayLeaveRequests->contains($dayLeaveRequest)) {
            $this->dayLeaveRequests->add($dayLeaveRequest);
            $dayLeaveRequest->setLeaveRequest($this);
        }

        return $this;
    }

    public function removeDayLeaveRequest(DayLeaveRequest $dayLeaveRequest): static
    {
        if ($this->dayLeaveRequests->removeElement($dayLeaveRequest)) {
            if ($dayLeaveRequest->getLeaveRequest() === $this) {
                $dayLeaveRequest->setLeaveRequest(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProcessedBy(): ?User
    {
        return $this->processedBy;
    }

    public function setProcessedBy(?User $processedBy): static
    {
        $this->processedBy = $processedBy;

        return $this;
    }

    public function getType(): LeaveRequestTypeEnum
    {
        return $this->type;
    }

    public function setType(LeaveRequestTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getNbDays(): ?float
    {
        return $this->nbDays;
    }

    public function setNbDays(float $nbDays): static
    {
        $this->nbDays = $nbDays;

        return $this;
    }

    public function getState(): string
    {
        return $this->state->value;
    }

    public function getEnumState(): LeaveRequestStateEnum
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = LeaveRequestStateEnum::tryFrom($state);

        return $this;
    }

    public function getRequestComment(): ?string
    {
        return $this->requestComment;
    }

    public function setRequestComment(?string $requestComment): static
    {
        $this->requestComment = $requestComment;

        return $this;
    }

    public function getResponseComment(): ?string
    {
        return $this->responseComment;
    }

    public function setResponseComment(?string $responseComment): static
    {
        $this->responseComment = $responseComment;

        return $this;
    }

    public function getFirstDay(): ?DateTimeInterface
    {
        return $this->firstDay;
    }

    public function setFirstDay(DateTimeInterface $firstDay): static
    {
        $this->firstDay = $firstDay;

        return $this;
    }

    public function getLastDay(): ?DateTimeInterface
    {
        return $this->lastDay;
    }

    public function setLastDay(DateTimeInterface $lastDay): static
    {
        $this->lastDay = $lastDay;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): static
    {
        $this->updatedAt = new DateTime(); 

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): static
    {
        $this->createdAt = new DateTime();

        return $this;
    }
}
