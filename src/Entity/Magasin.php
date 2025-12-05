<?php

namespace App\Entity;

use App\Repository\MagasinRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Entité Magasin
 *
 * Décrit un magasin (code, nom) rattaché à une ville, une centrale,
 * un statut et un type de projet. Possède des mouvements et un historique.
 */
#[ORM\Entity(repositoryClass: MagasinRepository::class)]
#[UniqueEntity(fields: ['codeMagasin'], message: 'Ce code magasin ALDI existe déjà.', errorPath: 'codeMagasin')]
class Magasin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    private ?string $codeMagasin = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nomMagasin = null;

    #[ORM\ManyToOne(inversedBy: 'magasins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Ville $ville = null;

    #[ORM\ManyToOne(inversedBy: 'magasins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Centrale $centrale = null;

    #[ORM\ManyToOne(inversedBy: 'magasins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Statut $statut = null;

    #[ORM\ManyToOne(inversedBy: 'magasins')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeProjet $typeProjet = null;

    #[ORM\OneToMany(mappedBy: 'magasin', targetEntity: MouvementStock::class)]
    private Collection $mouvementsStock;


    public function __construct()
    {
        $this->mouvementsStock = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeMagasin(): ?string
    {
        return $this->codeMagasin;
    }

    public function setCodeMagasin(?string $codeMagasin): self
    {
        $this->codeMagasin = $codeMagasin;
        return $this;
    }

    public function getNomMagasin(): ?string
    {
        return $this->nomMagasin;
    }

    public function setNomMagasin(?string $nomMagasin): self
    {
        $this->nomMagasin = $nomMagasin;
        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCentrale(): ?Centrale
    {
        return $this->centrale;
    }

    public function setCentrale(?Centrale $centrale): self
    {
        $this->centrale = $centrale;
        return $this;
    }

    public function getStatut(): ?Statut
    {
        return $this->statut;
    }

    public function setStatut(?Statut $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getTypeProjet(): ?TypeProjet
    {
        return $this->typeProjet;
    }

    public function setTypeProjet(?TypeProjet $typeProjet): self
    {
        $this->typeProjet = $typeProjet;
        return $this;
    }

    public function getMouvementsStock(): Collection
    {
        return $this->mouvementsStock;
    }

    public function addMouvementsStock(MouvementStock $mouvementStock): self
    {
        if (!$this->mouvementsStock->contains($mouvementStock)) {
            $this->mouvementsStock->add($mouvementStock);
            $mouvementStock->setMagasin($this);
        }
        return $this;
    }

    public function removeMouvementsStock(MouvementStock $mouvementStock): self
    {
        if ($this->mouvementsStock->removeElement($mouvementStock)) {
            if ($mouvementStock->getMagasin() === $this) {
                $mouvementStock->setMagasin(null);
            }
        }
        return $this;
    }

}
