<?php

namespace App\Repository;

use App\Entity\MouvementStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository MouvementStock
 *
 * Fournit l'accès aux mouvements via Doctrine.
 * Exemple d'usage: agrégations SUM pour calculer les stocks magasin.
 */
class MouvementStockRepository extends ServiceEntityRepository
{
    /**
     * Initialise le repository.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementStock::class);
    }
}
