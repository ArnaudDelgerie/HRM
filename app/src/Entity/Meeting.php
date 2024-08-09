<?php

namespace App\Entity;

use App\Enum\MeetingVisibilityEnum;
use App\Interface\OwnedInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\MeetingRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: MeetingRepository::class)]
class Meeting implements OwnedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: MeetingParticipant::class, mappedBy: 'meeting', cascade: ['persist'], orphanRemoval: true)]
    private Collection $meetingParticipants;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[Assert\Length(max: 255, maxMessage: 'meeting.name.assert.length')]
    #[Assert\NotBlank(message: 'meeting.name.assert.not_blank')]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Assert\NotBlank(message: 'meeting.description.assert.not_blank')]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::STRING, enumType: MeetingVisibilityEnum::class)]
    private MeetingVisibilityEnum $visibility = MeetingVisibilityEnum::Public;

    #[Assert\NotBlank(message: 'meeting.start_at.assert.not_blank')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $startAt = null;

    #[Assert\NotBlank(message: 'meeting.end_at.assert.not_blank')]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $endAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column]
    private bool $cancelled = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->meetingParticipants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMeetingParticipants(): Collection
    {
        return $this->meetingParticipants;
    }

    public function isMeetingParticipant(User $user): bool
    {
        $participant = $this->meetingParticipants->filter(function(MeetingParticipant $meetingParticipant) use($user) {
            return $meetingParticipant->getUser()->getId() === $user->getId();
        });

        return $participant->count() > 0;
    }

    public function addMeetingParticipant(MeetingParticipant $meetingParticipant): static
    {
        if (!$this->meetingParticipants->contains($meetingParticipant)) {
            $this->meetingParticipants->add($meetingParticipant);
            $meetingParticipant->setMeeting($this);
        }

        return $this;
    }

    public function removeMeetingParticipant(MeetingParticipant $meetingParticipant): static
    {
        if ($this->meetingParticipants->removeElement($meetingParticipant)) {
            if ($meetingParticipant->getMeeting() === $this) {
                $meetingParticipant->setMeeting(null);
            }
        }

        return $this;
    }

    public function removeDuplicateParticipants(): static
    {
        $existingParticipants = [];
        foreach($this->meetingParticipants as $participant) {
            if (in_array($participant->getUser()->getId(), $existingParticipants, true)) {
                $this->meetingParticipants->removeElement($participant);
            } else {
                $existingParticipants[] = $participant->getUser()->getId();
            }
        }

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->createdBy;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getVisibility(): MeetingVisibilityEnum
    {
        return $this->visibility;
    }

    public function setVisibility(MeetingVisibilityEnum $visibility): static
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(DateTimeInterface $startAt): static
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(DateTimeInterface $endAt): static
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }

    public function setCancelled(bool $cancelled): static
    {
        $this->cancelled = $cancelled;

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
