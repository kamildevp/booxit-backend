<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Service\GetterHelper\Attribute\Getter;
use App\Service\SetterHelper\Attribute\Setter;
use App\Service\GetterHelper\CustomAccessRule\EmailAccessRule;
use App\Service\SetterHelper\Task\EmailTask;
use App\Service\SetterHelper\Task\PasswordTask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['email'],
    message: 'This email is already taken')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        max: 180,
        maxMessage: 'Max length of email is 180 characters'
    )]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

  
    #[Assert\NotBlank(groups: ['plainPassword'], message: 'Password cannot be blank')]
    #[Assert\Regex(
        groups: ['plainPassword'],
        pattern: '/^(?=.*[A-Z])(?=.*\d)[A-Z\d!@#$%?&*]{8,}$/i',
        message: 'Password length must be from 8 to 20 characters, can contain special characters(!#$%?&*) and must have at least one letter and digit'
    )]
    private ?string $plainPassword = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^(?!\s)[^<>]{6,40}$/i',
        message: 'Name must be from 6 to 40 characters long, cannot start from whitespace and contain characters: <>'
    )]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: EmailConfirmation::class, orphanRemoval: true)]
    private Collection $emailConfirmations;

    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\OneToMany(mappedBy: 'appUser', targetEntity: OrganizationMember::class, orphanRemoval: true)]
    private Collection $organizationAssignments;

    public function __construct()
    {
        $this->emailConfirmations = new ArrayCollection();
        $this->organizationAssignments = new ArrayCollection();
    }

    #[Getter(groups: ['schedule-assignments'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Getter(accessRule: EmailAccessRule::class)]
    public function getEmail(): ?string
    {
        return $this->email;
    }

    #[Setter(setterTask: EmailTask::class)]
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
    public function getPassword(): string
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
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    #[Getter(groups: ['schedule-assignments'])]
    public function getName(): ?string
    {
        return $this->name;
    }

    #[Setter]
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    #[Setter(targetParameter: 'password',  setterTask: PasswordTask::class)]
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

}
