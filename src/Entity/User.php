<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\Timestampable;
use App\Enum\TranslationsLocale;
use App\Enum\User\UserNormalizerGroup;
use App\Enum\User\UserRole;
use App\Repository\Filter\EntityFilter\FieldContains;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Gedmo\Mapping\Annotation\SoftDeleteable as DoctrineSoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[DoctrineSoftDeleteable]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Timestampable, SoftDeleteableEntity;

    #[Groups([UserNormalizerGroup::BASE_INFO->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([UserNormalizerGroup::SENSITIVE->value])]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;


    #[Groups([UserNormalizerGroup::BASE_INFO->value])]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    private ?string $plainPassword = null;

    #[ORM\Column]
    private ?string $password = null;

    #[Groups([UserNormalizerGroup::SENSITIVE->value])]
    #[ORM\Column]
    private array $roles = [UserRole::REGULAR->value];

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: EmailConfirmation::class, cascade: ['remove'])]
    private Collection $emailConfirmations;

    #[Groups([UserNormalizerGroup::DETAILS->value])]
    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\OneToMany(mappedBy: 'appUser', targetEntity: OrganizationMember::class, cascade: ['remove'])]
    private Collection $organizationAssignments;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiryDate = null;

    #[ORM\OneToMany(mappedBy: 'appUser', targetEntity: RefreshToken::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $refreshTokens;

    #[ORM\OneToMany(mappedBy: 'reservedBy', targetEntity: Reservation::class, fetch: 'EXTRA_LAZY')]
    private Collection $reservations;

    #[Groups([UserNormalizerGroup::DETAILS->value])]
    #[ORM\Column(length: 255)]
    private string $languagePreference = TranslationsLocale::EN->value;

    public function __construct()
    {
        $this->emailConfirmations = new ArrayCollection();
        $this->organizationAssignments = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return Collection<int, EmailConfirmation>
     */
    public function getEmailConfirmations(): Collection
    {
        return $this->emailConfirmations;
    }

    public function addEmailConfirmation(EmailConfirmation $emailConfirmation): self
    {
        if (!$this->emailConfirmations->contains($emailConfirmation)) {
            $this->emailConfirmations->add($emailConfirmation);
            $emailConfirmation->setCreator($this);
        }

        return $this;
    }

    public function removeEmailConfirmation(EmailConfirmation $emailConfirmation): self
    {
        if ($this->emailConfirmations->removeElement($emailConfirmation)) {
            // set the owning side to null (unless already changed)
            if ($emailConfirmation->getCreator() === $this) {
                $emailConfirmation->setCreator(null);
            }
        }

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return Collection<int, OrganizationMember>
     */
    public function getOrganizationAssignments(): Collection
    {
        return $this->organizationAssignments;
    }

    public function addOrganizationAssignment(OrganizationMember $organizationAssignment): self
    {
        if (!$this->organizationAssignments->contains($organizationAssignment)) {
            $this->organizationAssignments->add($organizationAssignment);
            $organizationAssignment->setAppUser($this);
        }

        return $this;
    }

    public function removeOrganizationAssignment(OrganizationMember $organizationAssignment): self
    {
        if ($this->organizationAssignments->removeElement($organizationAssignment)) {
            // set the owning side to null (unless already changed)
            if ($organizationAssignment->getAppUser() === $this) {
                $organizationAssignment->setAppUser(null);
            }
        }

        return $this;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(?\DateTimeInterface $expiryDate): self
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * @return Collection<int, RefreshToken>
     */
    public function getRefreshTokens(): Collection
    {
        return $this->refreshTokens;
    }

    public function addRefreshToken(RefreshToken $refreshToken): self
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
            $refreshToken->setAppUser($this);
        }

        return $this;
    }

    public function removeRefreshToken(RefreshToken $refreshToken): self
    {
        if ($this->refreshTokens->removeElement($refreshToken)) {
            // set the owning side to null (unless already changed)
            if ($refreshToken->getAppUser() === $this) {
                $refreshToken->setAppUser(null);
            }
        }

        return $this;
    }

    public static function getFilterDefs(): array
    {
        return array_merge(self::getTimestampsFilterDefs(), [
            'name' => new FieldContains('name'),
            'email' => new FieldContains('email'),
        ]);
    }

    public static function getOrderDefs(): array
    {
        return array_merge(self::getTimestampsOrderDefs(), [
            'id' => new BaseFieldOrder('id'),
            'name' => new BaseFieldOrder('name'),
            'email' => new BaseFieldOrder('email'),
        ]);
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function hasReservation(Reservation $reservation): bool
    {
        return $this->reservations->contains($reservation);
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setReservedBy($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getReservedBy() === $this) {
                $reservation->setReservedBy(null);
            }
        }

        return $this;
    }

    public function getLanguagePreference(): string
    {
        return $this->languagePreference;
    }

    public function setLanguagePreference(string $languagePreference): static
    {
        $this->languagePreference = $languagePreference;

        return $this;
    }

}
