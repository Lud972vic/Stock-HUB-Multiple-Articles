<?php

namespace App\Repository;

use App\Entity\Centrale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository Centrale
 *
 * Accès aux centrales/enseignes.
 */
class CentraleRepository extends ServiceEntityRepository
{
    /**
     * Initialise le repository.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Centrale::class);
    }
}
