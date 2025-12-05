<?php

namespace App\Entity;

use App\Repository\FournisseurRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Entité Fournisseur
 *
 * Désigne un fournisseur (code unique, nom) relié à des matériels.
 * Utilisée pour la recherche et les contraintes de suppression.
 */
#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
#[UniqueEntity(fields: ['codeFournisseur'], message: 'Ce code fournisseur existe déjà.', errorPath: 'codeFournisseur')]
class Fournisseur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    private ?string $codeFournisseur = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nomFournisseur = null;

    #[ORM\OneToMany(mappedBy: 'fournisseur', targetEntity: Materiel::class)]
    private Collection $materiels;

    public function __construct()
    {
        $this->materiels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeFournisseur(): ?string
    {
        return $this->codeFournisseur;
    }

    public function setCodeFournisseur(?string $codeFournisseur): self
    {
        $this->codeFournisseur = $codeFournisseur;
        return $this;
    }

    public function getNomFournisseur(): ?string
    {
        return $this->nomFournisseur;
    }

    public function setNomFournisseur(?string $nomFournisseur): self
    {
        $this->nomFournisseur = $nomFournisseur;
        return $this;
    }

    public function getMateriels(): Collection
    {
        return $this->materiels;
    }

    public function addMateriel(Materiel $materiel): self
    {
        if (!$this->materiels->contains($materiel)) {
            $this->materiels->add($materiel);
            $materiel->setFournisseur($this);
        }
        return $this;
    }

    public function removeMateriel(Materiel $materiel): self
    {
        if ($this->materiels->removeElement($materiel)) {
            if ($materiel->getFournisseur() === $this) {
                $materiel->setFournisseur(null);
            }
        }
        return $this;
    }
}
