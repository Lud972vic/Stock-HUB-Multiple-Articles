<?php

namespace App\Entity;

use App\Repository\CentraleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité Centrale
 *
 * Centrale de rattachement des magasins.
 */
#[ORM\Entity(repositoryClass: CentraleRepository::class)]
class Centrale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nomCentrale = null;

    #[ORM\OneToMany(mappedBy: 'centrale', targetEntity: Magasin::class)]
    private Collection $magasins;

    public function __construct()
    {
        $this->magasins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCentrale(): ?string
    {
        return $this->nomCentrale;
    }

    public function setNomCentrale(?string $nomCentrale): self
    {
        $this->nomCentrale = $nomCentrale;
        return $this;
    }

    public function getMagasins(): Collection
    {
        return $this->magasins;
    }

    public function addMagasin(Magasin $magasin): self
    {
        if (!$this->magasins->contains($magasin)) {
            $this->magasins->add($magasin);
            $magasin->setCentrale($this);
        }
        return $this;
    }

    public function removeMagasin(Magasin $magasin): self
    {
        if ($this->magasins->removeElement($magasin)) {
            if ($magasin->getCentrale() === $this) {
                $magasin->setCentrale(null);
            }
        }
        return $this;
    }
}
