<?php

namespace App\Controller;

use App\Repository\FournisseurRepository;
use App\Repository\MaterielRepository;
use App\Repository\MagasinRepository;
use App\Repository\MouvementStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur de la page d'accueil.
 *
 * Présente des indicateurs synthétiques et des tableaux de stocks.
 * Agrège les mouvements pour calculer les sorties nettes par matériel.
 *
 * Dépendances: Repositories Doctrine et template `home/index.html.twig`.
 */
class HomeController extends AbstractController
{
    /**
     * Affiche le tableau de bord avec compteurs et agrégations.
     *
     * Entrées: Repositories pour fournisseurs, matériels, magasins, mouvements.
     * Sortie: Response avec variables pour la vue.
     * Cas limites: matériel sans stock, ENTREE sans magasin ignorée pour les retours.
     *
     * @param FournisseurRepository $fRepo
     * @param MaterielRepository $mRepo
     * @param MagasinRepository $gRepo
     * @param MouvementStockRepository $mvRepo
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index(
        FournisseurRepository $fRepo,
        MaterielRepository $mRepo,
        MagasinRepository $gRepo,
        MouvementStockRepository $mvRepo
    ): Response {
        $countFournisseurs = $fRepo->count([]);
        $countMateriels = $mRepo->count([]);
        $countMagasins = $gRepo->count([]);
        $countMouvements = $mvRepo->count([]);

        $materiels = $mRepo->createQueryBuilder('m')
            ->leftJoin('m.fournisseur', 'f')->addSelect('f')
            ->leftJoin('m.stockCentral', 'sc')->addSelect('sc')
            ->orderBy('m.nom', 'ASC')
            ->getQuery()->getResult();

        $stocksMateriels = [];
        $stocksFournisseurs = [];
        $totalValeur = 0.0;
        $totalSortieValeur = 0.0;

        // Sorties par matériel (uniquement mouvements liés à un magasin)
        $sortiesRows = $mvRepo->createQueryBuilder('mv')
            ->select('IDENTITY(mv.materiel) as mid, SUM(mv.quantiteMouvement) as qty')
            ->where('mv.typeMouvement = :t')
            ->andWhere('mv.magasin IS NOT NULL')
            ->setParameter('t', 'SORTIE')
            ->groupBy('mv.materiel')
            ->getQuery()->getArrayResult();
        // Retours (ENTREE) depuis les magasins
        $entreesRows = $mvRepo->createQueryBuilder('mv')
            ->select('IDENTITY(mv.materiel) as mid, SUM(mv.quantiteMouvement) as qty')
            ->where('mv.typeMouvement = :t')
            ->andWhere('mv.magasin IS NOT NULL')
            ->setParameter('t', 'ENTREE')
            ->groupBy('mv.materiel')
            ->getQuery()->getArrayResult();
        $sortiesByMateriel = [];
        $entreesByMateriel = [];
        foreach ($sortiesRows as $row) {
            $sortiesByMateriel[(int)$row['mid']] = (int)$row['qty'];
        }
        foreach ($entreesRows as $row) {
            $entreesByMateriel[(int)$row['mid']] = (int)$row['qty'];
        }

        foreach ($materiels as $m) {
            $quantite = $m->getStockCentral() ? (int) $m->getStockCentral()->getQuantiteStockCentral() : 0;
            $vuht = $m->getValeurUnitaireHt() !== null ? (float) $m->getValeurUnitaireHt() : 0.0;
            $total = $quantite * $vuht;
            $sortieBrut = $sortiesByMateriel[$m->getId()] ?? 0;
            $retourMagasin = $entreesByMateriel[$m->getId()] ?? 0;
            // Sorties nettes (jamais négatives)
            $sortie = max(0, $sortieBrut - $retourMagasin);
            $stocksMateriels[] = [
                'id' => $m->getId(),
                'code' => $m->getCodeArticle(),
                'nom' => $m->getNom(),
                'description' => $m->getDescription(),
                'fournisseur' => $m->getFournisseur() ? $m->getFournisseur()->getNomFournisseur() : null,
                'quantite' => $quantite,
                'sortie' => $sortie,
                'vuht' => $vuht,
                'total' => $total,
            ];

            $fName = $m->getFournisseur() ? $m->getFournisseur()->getNomFournisseur() : '—';
            if (!isset($stocksFournisseurs[$fName])) {
                $stocksFournisseurs[$fName] = [
                    'quantite_stock' => 0,
                    'total_stock' => 0.0,
                    'quantite_sortie' => 0,
                    'total_sortie' => 0.0,
                ];
            }
            $stocksFournisseurs[$fName]['quantite_stock'] += $quantite;
            $stocksFournisseurs[$fName]['total_stock'] += $total;
            $stocksFournisseurs[$fName]['quantite_sortie'] += $sortie;
            $stocksFournisseurs[$fName]['total_sortie'] += $sortie * $vuht;
            $totalValeur += $total;
            $totalSortieValeur += $sortie * $vuht;
        }

        return $this->render('home/index.html.twig', [
            'count_fournisseurs' => $countFournisseurs,
            'count_materiels' => $countMateriels,
            'count_magasins' => $countMagasins,
            'count_mouvements' => $countMouvements,
            'stocks_materiels' => $stocksMateriels,
            'stocks_fournisseurs' => $stocksFournisseurs,
            'stocks_total_valeur' => $totalValeur,
            'stocks_total_sortie_valeur' => $totalSortieValeur,
        ]);
    }

    #[Route('/aide', name: 'app_help')]
    public function help(): Response
    {
        return $this->render('home/help.html.twig');
    }
}
