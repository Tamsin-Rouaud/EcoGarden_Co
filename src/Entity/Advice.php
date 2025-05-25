<?php

namespace App\Entity;

use App\Repository\AdviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    /**
     * Identifiant unique du conseil.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAdvices'])]
    private ?int $id = null;

    /**
     * Contenu du conseil.
     * Obligatoire, au moins 5 caractères.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['getAdvices'])]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 5,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private ?string $description = null;

    /**
     * Mois associés au conseil (1 à 12).
     * Doit être un tableau d'entiers valides.
     */
    #[ORM\Column(type: 'json')]
    #[Groups(['getAdvices'])]
    #[Assert\NotBlank(message: "Le champ des mois est obligatoire.")]
    #[Assert\Type(type: 'array', message: "Les mois doivent être fournis sous forme de tableau.")]
    #[Assert\All([
        new Assert\Type('integer'),
        new Assert\Range(
            min: 1,
            max: 12,
            notInRangeMessage: "Chaque mois doit être un entier entre {{ min }} et {{ max }}."
        )
    ])]
    private array $month = [];

    /**
     * Utilisateur (admin) ayant créé le conseil.
     */
    #[ORM\ManyToOne(inversedBy: 'advices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getAdvices'])]
    private ?User $createdBy = null;

    // Getters / Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getMonth(): array
    {
        return $this->month;
    }

    public function setMonth(array $month): static
    {
        $this->month = $month;
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
}
