<?php

namespace App\Repository;

use App\Entity\StockCentral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository StockCentral
 *
 * Accès aux stocks centraux des matériels.
 */
class StockCentralRepository extends ServiceEntityRepository
{
    /**
     * Initialise le repository.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockCentral::class);
    }
}
