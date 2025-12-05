<?php

namespace App\Repository;

use App\Entity\Statut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository Statut
 *
 * Accès aux statuts de magasins.
 */
class StatutRepository extends ServiceEntityRepository
{
    /**
     * Initialise le repository.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statut::class);
    }
}
