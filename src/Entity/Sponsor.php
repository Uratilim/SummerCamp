<?php

namespace App\Entity;

use App\Repository\SponsorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
class Sponsor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $contract_value = null;

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'sponsors')]
    private Collection $Team_id;

    public function __construct()
    {
        $this->Team_id = new ArrayCollection();
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

    public function getContractValue(): ?float
    {
        return $this->contract_value;
    }

    public function setContractValue(float $contract_value): static
    {
        $this->contract_value = $contract_value;

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeamId(): Collection
    {
        return $this->Team_id;
    }

    public function addTeamId(Team $TeamId): static
    {
        if (!$this->Team_id->contains($TeamId)) {
            $this->Team_id->add($TeamId);
        }

        return $this;
    }

    public function removeTeamId(Team $TeamId): static
    {
        $this->Team_id->removeElement($TeamId);

        return $this;
    }
}
