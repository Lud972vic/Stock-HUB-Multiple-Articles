<?php

namespace App\Entity;

use App\Repository\TypeProjetRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeProjetRepository::class)]
class TypeProjet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nomTypeProjet = null;

    #[ORM\OneToMany(mappedBy: 'typeProjet', targetEntity: Magasin::class)]
    private Collection $magasins;

    public function __construct()
    {
        $this->magasins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomTypeProjet(): ?string
    {
        return $this->nomTypeProjet;
    }

    public function setNomTypeProjet(?string $nomTypeProjet): self
    {
        $this->nomTypeProjet = $nomTypeProjet;
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
            $magasin->setTypeProjet($this);
        }
        return $this;
    }

    public function removeMagasin(Magasin $magasin): self
    {
        if ($this->magasins->removeElement($magasin)) {
            if ($magasin->getTypeProjet() === $this) {
                $magasin->setTypeProjet(null);
            }
        }
        return $this;
    }
}
