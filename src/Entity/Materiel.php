<?php

namespace App\Entity;

use App\Repository\MaterielRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Entité Materiel
 *
 * Décrit un article (code unique, nom, valeur HT, description optionnelle)
 * relié à un fournisseur, avec un stock central et des mouvements associés.
 */
#[ORM\Entity(repositoryClass: MaterielRepository::class)]
#[UniqueEntity(fields: ['codeArticle'], message: 'Ce code article existe déjà.', errorPath: 'codeArticle')]
class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'materiels')]
    private ?Fournisseur $fournisseur = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    private ?string $codeArticle = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive]
    private ?string $valeurUnitaireHt = null;

    #[ORM\OneToOne(mappedBy: 'materiel', targetEntity: StockCentral::class, cascade: ['persist', 'remove'])]
    private ?StockCentral $stockCentral = null;

    #[ORM\OneToMany(mappedBy: 'materiel', targetEntity: MouvementStock::class)]
    private Collection $mouvementsStock;


    public function __construct()
    {
        $this->mouvementsStock = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): self
    {
        $this->fournisseur = $fournisseur;
        return $this;
    }

    public function getCodeArticle(): ?string
    {
        return $this->codeArticle;
    }

    public function setCodeArticle(?string $codeArticle): self
    {
        $this->codeArticle = $codeArticle;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getValeurUnitaireHt(): ?string
    {
        return $this->valeurUnitaireHt;
    }

    public function setValeurUnitaireHt(?string $valeurUnitaireHt): self
    {
        $this->valeurUnitaireHt = $valeurUnitaireHt;
        return $this;
    }

    public function getStockCentral(): ?StockCentral
    {
        return $this->stockCentral;
    }

    public function setStockCentral(?StockCentral $stockCentral): self
    {
        $this->stockCentral = $stockCentral;
        if ($stockCentral && $stockCentral->getMateriel() !== $this) {
            $stockCentral->setMateriel($this);
        }
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
            $mouvementStock->setMateriel($this);
        }
        return $this;
    }

    public function removeMouvementsStock(MouvementStock $mouvementStock): self
    {
        if ($this->mouvementsStock->removeElement($mouvementStock)) {
            if ($mouvementStock->getMateriel() === $this) {
                $mouvementStock->setMateriel(null);
            }
        }
        return $this;
    }

}
