<?php

namespace App\Command;

use App\Repository\MaterielRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-filter',
    description: 'Teste le filtre materiel',
)]
class TestFilterCommand extends Command
{
    public function __construct(private MaterielRepository $repo)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('term', InputArgument::REQUIRED, 'Terme de recherche (nom)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $term = $input->getArgument('term');

        $io->title("Test de filtrage pour : " . $term);

        $qb = $this->repo->createQueryBuilder('m')
            ->leftJoin('m.fournisseur', 'f')->addSelect('f')
            ->orderBy('m.nom', 'ASC');

        // Logique exacte du contrôleur
        $qb->andWhere('LOWER(m.nom) LIKE :nom')
           ->setParameter('nom', '%'.mb_strtolower($term).'%');

        $sql = $qb->getQuery()->getSQL();
        $params = $qb->getQuery()->getParameters();

        $io->section("SQL Généré");
        $io->text($sql);
        
        $io->section("Paramètres");
        foreach ($params as $param) {
            $io->text($param->getName() . ': ' . $param->getValue());
        }

        $results = $qb->getQuery()->getResult();

        $io->section("Résultats (" . count($results) . ")");
        foreach ($results as $m) {
            $io->text("- " . $m->getNom() . " (Code: " . $m->getCodeArticle() . ")");
        }

        return Command::SUCCESS;
    }
}
