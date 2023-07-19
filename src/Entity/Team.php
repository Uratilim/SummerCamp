<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 30)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $nickname = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\DateTime(

    )]
    private ?\DateTimeInterface $year_of_est = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $motto = null;

    #[ORM\OneToMany(mappedBy: 'team_id', targetEntity: Member::class, orphanRemoval: true)]
    private Collection $players;

    #[ORM\ManyToMany(targetEntity: Sponsor::class, mappedBy: 'team_id')]
    private Collection $sponsors;

    public function __construct()
    {
        $this->players = new ArrayCollection();
        $this->sponsors = new ArrayCollection();
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

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getYearOfEst(): ?\DateTimeInterface
    {
        return $this->year_of_est;
    }

    public function setYearOfEst(\DateTimeInterface $year_of_est): static
    {
        $this->year_of_est = $year_of_est;

        return $this;
    }

    public function getMotto(): ?string
    {
        return $this->motto;
    }

    public function setMotto(?string $motto): static
    {
        $this->motto = $motto;

        return $this;
    }

    /**
     * @return Collection<int, Member>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Member $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setTeamId($this);
        }

        return $this;
    }

    public function removePlayer(Member $player): static
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getTeamId() === $this) {
                $player->setTeamId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sponsor>
     */
    public function getSponsors(): Collection
    {
        return $this->sponsors;
    }

    public function addSponsor(Sponsor $sponsor): static
    {
        if (!$this->sponsors->contains($sponsor)) {
            $this->sponsors->add($sponsor);
            $sponsor->addTeamId($this);
        }

        return $this;
    }

    public function removeSponsor(Sponsor $sponsor): static
    {
        if ($this->sponsors->removeElement($sponsor)) {
            $sponsor->removeTeamId($this);
        }

        return $this;
    }



}
