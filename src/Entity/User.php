<?php

namespace App\Entity;

use App\Enum\User\UserNormalizerGroup;
use App\Repository\Filter\EntityFilter\Attribute\Filter;
use App\Repository\Filter\EntityFilter\FieldContains;
use App\Repository\Order\EntityOrder\Attribute\Order;
use App\Repository\Order\EntityOrder\BaseFieldOrder;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups([UserNormalizerGroup::PUBLIC->value, UserNormalizerGroup::PRIVATE->value])]
    #[Order('id', new BaseFieldOrder)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([UserNormalizerGroup::PRIVATE->value])]
    #[Order('email', new BaseFieldOrder)]
    #[Filter('email', new FieldContains)] 
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;


    #[Groups([UserNormalizerGroup::PUBLIC->value, UserNormalizerGroup::PRIVATE->value])]
    #[Order('name', new BaseFieldOrder)]
    #[Filter('name', new FieldContains)] 
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    private ?string $plainPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: EmailConfirmation::class)]
    private Collection $emailConfirmations;

    #[Groups([UserNormalizerGroup::PUBLIC->value, UserNormalizerGroup::PRIVATE->value])]
    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\OneToMany(mappedBy: 'appUser', targetEntity: OrganizationMember::class, orphanRemoval: true)]
    private Collection $organizationAssignments;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiryDate = null;

    #[ORM\OneToMany(mappedBy: 'appUser', targetEntity: RefreshToken::class, orphanRemoval: true)]
    private Collection $refreshTokens;

    #[Groups([UserNormalizerGroup::PUBLIC->value, UserNormalizerGroup::PRIVATE->value])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Timestampable(on: 'create')]
    public ?\DateTimeImmutable $createdAt = null;

    #[Groups([UserNormalizerGroup::PUBLIC->value, UserNormalizerGroup::PRIVATE->value])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Timestampable(on: 'update')]
    public ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->emailConfirmations = new ArrayCollection();
        $this->organizationAssignments = new ArrayCollection();
        $this->refreshTokens = new ArrayCollection();
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

    public function getOrganizations(): Collection
    {
        $organizations = new ArrayCollection([]);
        foreach($this->organizationAssignments as $assignment){
            $organizations->add($assignment->getOrganization());
        }

        return $organizations;
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

}
