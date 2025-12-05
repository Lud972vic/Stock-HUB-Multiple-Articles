<?php

namespace App\Entity;

use App\Repository\StockCentralRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité StockCentral
 *
 * Représente la quantité disponible au dépôt central (Le HUB de DAM pour approvisionner les magasins) pour un matériel.
 * Mise à jour lors des mouvements ENTREE/SORTIE.
 */
#[ORM\Entity(repositoryClass: StockCentralRepository::class)]
class StockCentral
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'stockCentral')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Materiel $materiel = null;

    #[ORM\Column]
    private ?int $quantiteStockCentral = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMateriel(): ?Materiel
    {
        return $this->materiel;
    }

    public function setMateriel(Materiel $materiel): self
    {
        $this->materiel = $materiel;
        return $this;
    }

    public function getQuantiteStockCentral(): ?int
    {
        return $this->quantiteStockCentral;
    }

    public function setQuantiteStockCentral(?int $quantiteStockCentral): self
    {
        $this->quantiteStockCentral = $quantiteStockCentral;
        return $this;
    }
}
