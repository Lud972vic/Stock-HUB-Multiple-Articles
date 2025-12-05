<?php

namespace App\Repository;

use App\Entity\TypeProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository TypeProjet
 *
 * Accès aux types de projet.
 */
class TypeProjetRepository extends ServiceEntityRepository
{
    /**
     * Initialise le repository.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeProjet::class);
    }
}
