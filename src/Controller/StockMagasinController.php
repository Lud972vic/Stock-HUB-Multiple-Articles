<?php

namespace App\Controller;

use App\Repository\MouvementStockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur du stock par magasin.
 *
 * Agrège les mouvements pour présenter le stock restant par couple magasin–matériel.
 * Les ENTREE diminuent le stock magasin (retour), les SORTIE l'augmentent.
 */
class StockMagasinController extends AbstractController
{
    /**
     * Liste des stocks par magasin.
     *
     * Entrées: query `q` texte pour filtrer par magasin/article/code.
     * Sorties: tableau des stocks strictement positifs rendu via `stock_magasin/index.html.twig`.
     * Dépendances: `MouvementStockRepository` (agrégations SUM).
     * Cas limites: couples magasin–article avec stock nul exclus.
     * Exemple: /stock-magasins?q=souris.
     *
     * @param Request $request
     * @param MouvementStockRepository $repo
     * @return Response
     */
    #[Route('/stock-magasins', name: 'app_stock_magasin_index')]
    public function index(Request $request, MouvementStockRepository $repo): Response
    {
        $q = $request->query->get('q');
        $qb = $repo->createQueryBuilder('mv')
            ->select('IDENTITY(mv.magasin) as magasinId, g.nomMagasin, IDENTITY(mv.materiel) as materielId, m.description, m.nom, m.codeArticle, mv.typeMouvement, SUM(mv.quantiteMouvement) as qty')
            ->join('mv.magasin', 'g')
            ->join('mv.materiel', 'm')
            ->groupBy('mv.magasin', 'mv.materiel', 'mv.typeMouvement')
            ->orderBy('g.nomMagasin', 'ASC')
            ->addOrderBy('m.nom', 'ASC');

        if ($q) {
            $qb->andWhere('LOWER(g.nomMagasin) LIKE :q OR LOWER(m.nom) LIKE :q OR LOWER(m.codeArticle) LIKE :q')
               ->setParameter('q', '%'.mb_strtolower($q).'%');
        }

        $rows = $qb->getQuery()->getResult();

        $stocks = [];
        foreach ($rows as $row) {
            $magId = $row['magasinId'];
            $matId = $row['materielId'];
            $key = $magId . '-' . $matId;

            if (!isset($stocks[$key])) {
                $stocks[$key] = [
                    'magasin' => $row['nomMagasin'],
                    'materiel' => $row['nom'],
                    'code' => $row['codeArticle'],
                    'stock' => 0,
                    'description' => $row['description']
                ];
            }

            // Convention: sortie => stock magasin augmente; retour (entrée) => diminue
            if ($row['typeMouvement'] === 'SORTIE') {
                $stocks[$key]['stock'] += $row['qty'];
            } elseif ($row['typeMouvement'] === 'ENTREE') {
                $stocks[$key]['stock'] -= $row['qty'];
            }
        }

        $finalStocks = array_filter($stocks, fn($s) => $s['stock'] > 0);

        return $this->render('stock_magasin/index.html.twig', [
            'stocks' => $finalStocks,
            'q' => $q,
        ]);
    }
}
