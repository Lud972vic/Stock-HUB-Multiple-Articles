<?php

namespace App\Entity;

use App\Repository\MouvementStockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Entité MouvementStock
 *
 * Représente un mouvement d'ENTREE (retour) ou de SORTIE (ajout) d'un matériel.
 * Si `magasin` est null pour une ENTREE, il s'agit d'un approvisionnement du stock central.
 *
 * Champs principaux:
 * - `dateMouvement`: date/heure immuable
 * - `typeMouvement`: ENTREE/SORTIE
 * - `quantiteMouvement`: quantité > 0
 * - `materiel`: matériel concerné (obligatoire)
 * - `magasin`: magasin concerné (optionnel pour ENTREE centrale)
 */
#[ORM\Entity(repositoryClass: MouvementStockRepository::class)]
class MouvementStock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateMouvement = null;

    #[ORM\Column(length: 50)]
    #[Assert\Choice(choices: ['ENTREE','SORTIE'])]
    private ?string $typeMouvement = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $quantiteMouvement = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementsStock')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Materiel $materiel = null;

    #[ORM\ManyToOne(inversedBy: 'mouvementsStock')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Magasin $magasin = null;

    public function __construct()
    {
        $this->dateMouvement = new \DateTimeImmutable();
    }

    /**
     * Validation métier:
     * - SORTIE: magasin obligatoire
     * - ENTREE: magasin facultatif (retour magasin ou appro central)
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        if ($this->typeMouvement === 'SORTIE' && $this->magasin === null) {
            $context->buildViolation('Le magasin est obligatoire pour une sortie.')
                ->atPath('magasin')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateMouvement(): ?\DateTimeImmutable
    {
        return $this->dateMouvement;
    }

    public function setDateMouvement(?\DateTimeImmutable $dateMouvement): self
    {
        $this->dateMouvement = $dateMouvement;
        return $this;
    }

    public function getTypeMouvement(): ?string
    {
        return $this->typeMouvement;
    }

    public function setTypeMouvement(?string $typeMouvement): self
    {
        $this->typeMouvement = $typeMouvement;
        return $this;
    }

    public function getQuantiteMouvement(): ?int
    {
        return $this->quantiteMouvement;
    }

    public function setQuantiteMouvement(?int $quantiteMouvement): self
    {
        $this->quantiteMouvement = $quantiteMouvement;
        return $this;
    }

    public function getMateriel(): ?Materiel
    {
        return $this->materiel;
    }

    public function setMateriel(?Materiel $materiel): self
    {
        $this->materiel = $materiel;
        return $this;
    }

    public function getMagasin(): ?Magasin
    {
        return $this->magasin;
    }

    public function setMagasin(?Magasin $magasin): self
    {
        $this->magasin = $magasin;
        return $this;
    }
}
