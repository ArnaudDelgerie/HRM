<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use App\Enum\UserRoleEnum;
use App\Enum\UserStateEnum;
use App\Interface\OwnedInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'user.email.assert.unique')]
#[UniqueEntity(fields: ['username'], message: 'user.username.assert.unique')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EquatableInterface, OwnedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: LeaveRequest::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $leaveRequests;

    #[Assert\Email(message: 'user.email.assert.email')]
    #[Assert\NotBlank(message: 'user.email.assert.not_blank')]
    #[Assert\Length(max: 180, maxMessage: 'user.email.assert.length')]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[Assert\NotBlank(message: 'user.password.assert.not_blank')]
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank(message: 'user.username.assert.not_blank')]
    #[Assert\Length(max: 255, maxMessage: 'user.username.assert.length')]
    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[Assert\NotBlank(message: 'user.firstname.assert.not_blank')]
    #[Assert\Length(max: 255, maxMessage: 'user.firstname.asser.length')]
    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[Assert\NotBlank(message: 'user.lastname.assert.not_blank')]
    #[Assert\Length(max: 255, maxMessage: 'user.lastname.asser.length')]
    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, enumType: UserStateEnum::class)]
    private ?UserStateEnum $state = UserStateEnum::Created;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $validationToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->leaveRequests = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->username;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLeaveRequests(): Collection
    {
        return $this->leaveRequests;
    }

    public function addLeaveRequest(LeaveRequest $leaveRequest): static
    {
        if (!$this->leaveRequests->contains($leaveRequest)) {
            $this->leaveRequests->add($leaveRequest);
            $leaveRequest->setUser($this);
        }

        return $this;
    }

    public function removeLeaveRequest(LeaveRequest $leaveRequest): static
    {
        if ($this->leaveRequests->removeElement($leaveRequest)) {
            if ($leaveRequest->getUser() === $this) {
                $leaveRequest->setUser(null);
            }
        }

        return $this;
    }

    public function getOwner(): User
    {
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function isAdmin(): bool
    {
        return in_array(UserRoleEnum::Admin->value, $this->roles, true);
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = UserRoleEnum::User->value; 

        return array_unique($roles);
    }

    /**
     * @return UserRoleEnum[]
     */
    public function getEnumRoles(): array
    {
        $enumRoles = [];
        foreach($this->getRoles() as $role) {
            $enumRoles[] = UserRoleEnum::from($role);
        }

        return $enumRoles;
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void {}

    public function isEqualTo(UserInterface $user): bool
    {
        /** @var User $user */
        return $this->email === $user->getEmail()
            && $this->getState() === $user->getState()
            && $this->getUsername() === $user->getUsername()
            && $this->getPassword() === $user->getPassword();
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state->value;
    }

    public function getEnumState(): ?UserStateEnum
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = UserStateEnum::tryFrom($state);

        return $this;
    }

    public function getValidationToken(): ?string
    {
        return $this->validationToken;
    }

    public function setValidationToken(?string $validationToken): static
    {
        $this->validationToken = $validationToken;

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
