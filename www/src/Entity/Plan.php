<?php

namespace App\Entity;

use App\Repository\PlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $limitGeneration = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?bool $active = null;

    /**
     * @var Collection<int, Tool>
     */
    #[ORM\ManyToMany(targetEntity: Tool::class, mappedBy: 'plans')]
    private Collection $tools;

    #[ORM\Column(nullable: true)]
    private ?int $limitStorage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePriceId = null;

    public function __construct()
    {
        $this->tools = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLimitGeneration(): ?int
    {
        return $this->limitGeneration;
    }

    public function setLimitGeneration(?int $limitGeneration): static
    {
        $this->limitGeneration = $limitGeneration;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Tool>
     */
    public function getTools(): Collection
    {
        return $this->tools;
    }

    public function addTool(Tool $tool): static
    {
        if (! $this->tools->contains($tool)) {
            $this->tools->add($tool);
            $tool->addPlan($this);
        }

        return $this;
    }

    public function removeTool(Tool $tool): static
    {
        if ($this->tools->removeElement($tool)) {
            $tool->removePlan($this);
        }

        return $this;
    }

    public function getLimitStorage(): ?int
    {
        return $this->limitStorage;
    }

    public function setLimitStorage(?int $limitStorage): static
    {
        $this->limitStorage = $limitStorage;

        return $this;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }

    public function setStripePriceId(?string $stripePriceId): static
    {
        $this->stripePriceId = $stripePriceId;

        return $this;
    }
}
