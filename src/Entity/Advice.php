<?php

namespace App\Entity;

use App\Repository\AdviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdviceRepository::class)]
class Advice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAdvices'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['getAdvices'])]
    private ?string $description = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['getAdvices'])]
    private array $month = [];

    #[ORM\ManyToOne(inversedBy: 'advices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getAdvices'])]
    private ?User $createdBy = null;

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
