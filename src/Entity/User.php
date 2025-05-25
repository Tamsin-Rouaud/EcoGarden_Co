<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Identifiant unique de l’utilisateur.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUsers'])]
    private ?int $id = null;

    /**
     * Adresse email (identifiant principal de connexion).
     * Validation : format d'email requis et non vide.
     */
    #[ORM\Column(length: 180)]
    #[Groups(['getAdvices', 'getUsers'])]
    #[Assert\NotBlank(message: "L'adresse email est obligatoire.")]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    /**
     * Rôles de l'utilisateur (ex : ROLE_USER, ROLE_ADMIN).
     * Par défaut, chaque utilisateur possède au moins ROLE_USER.
     */
    #[ORM\Column]
    #[Groups(['getAdvices', 'getUsers'])]
    private array $roles = [];

    /**
     * Mot de passe (hashé).
     * Validation : obligatoire, minimum 6 caractères.
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.")]
    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.")]
    private ?string $password = null;

    /**
     * Ville de résidence de l’utilisateur.
     * Requise pour les données météo personnalisées.
     */
    #[ORM\Column(length: 255)]
    #[Groups(['getAdvices', 'getUsers'])]
    #[Assert\NotBlank(message: "La ville est obligatoire.")]
    #[Assert\Length(min: 2, minMessage: "Le nom de la ville est trop court.")]
    private ?string $city = null;

    /**
     * Conseils créés par l’utilisateur (relation inverse).
     */
    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Advice::class)]
    private Collection $advices;

    public function __construct()
    {
        $this->advices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * Symfony utilise cette méthode pour identifier l’utilisateur.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Retourne les rôles de l’utilisateur.
     * Ajoute toujours ROLE_USER par défaut.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Méthode requise par l’interface UserInterface.
     * Sert à effacer les données sensibles si nécessaire.
     */
    public function eraseCredentials(): void
    {
        // $this->plainPassword = null;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Retourne la liste des conseils créés par l’utilisateur.
     */
    public function getAdvices(): Collection
    {
        return $this->advices;
    }

    public function addAdvice(Advice $advice): static
    {
        if (!$this->advices->contains($advice)) {
            $this->advices->add($advice);
            $advice->setCreatedBy($this);
        }

        return $this;
    }

    public function removeAdvice(Advice $advice): static
    {
        if ($this->advices->removeElement($advice)) {
            if ($advice->getCreatedBy() === $this) {
                $advice->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * Méthode héritée de UserInterface, alias de getUserIdentifier().
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }
}
