<?php

namespace App\Controller;

use App\Entity\MouvementStock;
use App\Entity\StockCentral;
use App\Form\MouvementStockType;
use App\Repository\MouvementStockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur des mouvements de stock.
 *
 * Rôle:
 * - Créer/éditer/supprimer des mouvements (ENTREE/SORTIE) par matériel et magasin.
 * - Mettre à jour le stock central et exposer des services de lecture de stock.
 * - Fournir une liste filtrable/paginée des mouvements.
 */
class MouvementStockController extends AbstractController
{
    /**
     * Liste paginée des mouvements avec recherche multi-colonnes.
     *
     * Flux:
     * - Construit une requête avec jointures (matériel, magasin), applique filtres et pagination.
     * - Duplique la requête pour obtenir le total avant offset/limit.
     *
     * Entrées:
     * - Query `q` (texte ou nombre pour filtrer la quantité)
     * - `type` (ENTREE/SORTIE), `materiel` (id), `magasin` (id)
     * - `date_from`, `date_to` (YYYY-MM-DD)
     * - `page` (entier ≥ 1)
     * Sorties:
     * - Template `mouvement_stock/index.html.twig` avec liste et pagination.
     * Dépendances: `MouvementStockRepository`, `MaterielRepository`, `MagasinRepository`.
     * Cas limites: bornage des dates, filtrage numérique sur `quantiteMouvement`.
     *
     * @param Request $request
     * @param MouvementStockRepository $repo
     * @param \App\Repository\MaterielRepository $mRepo
     * @param \App\Repository\MagasinRepository $gRepo
     * @return Response
     */
    #[Route('/mouvements', name: 'app_mouvement_index')]
    public function index(Request $request, MouvementStockRepository $repo, \App\Repository\MaterielRepository $mRepo, \App\Repository\MagasinRepository $gRepo): Response
    {
        // Récupère les filtres transmis en query et initialise la pagination
        $type = $request->query->get('type');
        $q = $request->query->get('q');
        $dateFrom = $request->query->get('date_from');
        $dateTo = $request->query->get('date_to');
        $materielId = $request->query->getInt('materiel');
        $magasinId = $request->query->getInt('magasin');
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Construit la requête principale avec jointures pour afficher les colonnes liées
        $qb = $repo->createQueryBuilder('mv')
            ->leftJoin('mv.materiel', 'm')->addSelect('m')
            ->leftJoin('mv.magasin', 'g')->addSelect('g')
            ->orderBy('mv.dateMouvement', 'DESC');

        if ($q) {
            // Filtrage texte multi-colonnes et filtrage numérique sur la quantité si `q` est un nombre
            $cond = 'LOWER(m.nom) LIKE :q OR LOWER(g.nomMagasin) LIKE :q OR LOWER(mv.typeMouvement) LIKE :q';
            if (is_numeric($q)) {
                $cond .= ' OR mv.quantiteMouvement = :qNum';
            }
            $qb->andWhere($cond)
               ->setParameter('q', '%'.mb_strtolower($q).'%');
            if (is_numeric($q)) {
                $qb->setParameter('qNum', $q + 0);
            }
        }

        if ($type) {
            $qb->andWhere('mv.typeMouvement = :t')->setParameter('t', $type);
        }
        if ($materielId) {
            $qb->andWhere('m.id = :mid')->setParameter('mid', $materielId);
        }
        if ($magasinId) {
            $qb->andWhere('g.id = :gid')->setParameter('gid', $magasinId);
        }
        if ($dateFrom) {
            $qb->andWhere('mv.dateMouvement >= :df')->setParameter('df', new \DateTimeImmutable($dateFrom.' 00:00:00'));
        }
        if ($dateTo) {
            $qb->andWhere('mv.dateMouvement <= :dt')->setParameter('dt', new \DateTimeImmutable($dateTo.' 23:59:59'));
        }

        // Duplique la requête pour compter le total (avant application de l'offset/limit)
        $countQb = clone $qb;
        $total = $countQb->select('count(mv.id)')->getQuery()->getSingleScalarResult();
        $totalPages = ceil($total / $limit);

        // Applique la pagination sur la requête principale
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        $mouvements = $qb->getQuery()->getResult();

        // Rendu du template avec les résultats et les listes de filtres disponibles
        return $this->render('mouvement_stock/index.html.twig', [
            'mouvements' => $mouvements,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => $totalPages,
            'total' => $total,
            'q' => $q,
            'type' => $type,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'materiel' => $materielId,
            'magasin' => $magasinId,
            'materiels' => $mRepo->findAll(),
            'magasins' => $gRepo->findBy([], ['nomMagasin' => 'ASC']),
        ]);
    }

    /**
     * Création d'un mouvement.
     *
     * Flux:
     * - Mode liste: traite `quantites[materielId]` pour créer plusieurs mouvements en une soumission.
     * - Mode unitaire: logique de formulaire standard (utilisé aussi en édition).
     *
     * Cas d'usage: retour (ENTREE) depuis un magasin, ajout (SORTIE) vers un magasin.
     * Préremplissages via query: `materiel`, `magasin`, `action` (retour|ajout).
     * Validation: quantité > 0, magasin requis pour retour/ajout.
     * Sorties: redirection vers l'index si succès, sinon rendu du formulaire.
     * Payload:
     * - POST en mode liste: `quantites[<materielId>] = <qty>` (>= 0)
     * API:
     * - Lecture stock central: `app_mouvement_stock_for_materiel`
     * - Lecture stock magasin: `app_mouvement_stock_magasin`
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param \App\Repository\MouvementStockRepository $mvRepo
     * @param LoggerInterface $logger
     * @return Response
     */
    #[Route('/mouvements/nouveau', name: 'app_mouvement_new')]
    public function new(Request $request, EntityManagerInterface $em, \App\Repository\MouvementStockRepository $mvRepo, LoggerInterface $logger): Response
    {
        // Instancie l'entité et applique les éventuels préremplissages depuis la query string
        $mouvement = new MouvementStock();
        $prefMaterielId = $request->query->getInt('materiel');
        $prefMagasinId = $request->query->getInt('magasin');
        $prefAction = $request->query->get('action');
        if ($prefMaterielId) {
            $prefMateriel = $em->getRepository(\App\Entity\Materiel::class)->find($prefMaterielId);
            if ($prefMateriel) { $mouvement->setMateriel($prefMateriel); }
        }
        if ($prefMagasinId) {
            $prefMagasin = $em->getRepository(\App\Entity\Magasin::class)->find($prefMagasinId);
            if ($prefMagasin) { $mouvement->setMagasin($prefMagasin); }
        }
        if ($prefAction === 'retour') {
            $mouvement->setTypeMouvement('ENTREE');
        } elseif ($prefAction === 'ajout') {
            $mouvement->setTypeMouvement('SORTIE');
        }
        // Formulaire standard (utilisé en édition et pour fallback unitaire)
        $form = $this->createForm(MouvementStockType::class, $mouvement);
        $form->handleRequest($request);

        // Soumission en mode liste: le payload contient `quantites[materielId] => qty`
        if ($request->isMethod('POST') && $request->request->has('quantites')) {
            $action = $request->request->get('_action');
            $quantites = $request->request->all('quantites');
            $magasin = $mouvement->getMagasin();
            // Pour retour/ajout, un magasin est obligatoire
            if ($action === 'retour' || $action === 'ajout') {
                if (!$magasin) {
                    $this->addFlash('danger', 'Magasin ALDI requis');
                    return $this->render('mouvement_stock/new.html.twig', [
                        'form' => $form,
                        'is_edit' => false,
                        'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                    ]);
                }
            }
            $now = new \DateTimeImmutable();
            // Parcourt chaque quantité et crée un mouvement par article
            foreach ($quantites as $mid => $qtyVal) {
                $qty = (int)($qtyVal ?? 0);
                if ($qty <= 0) { continue; }
                $materiel = $em->getRepository(\App\Entity\Materiel::class)->find((int)$mid);
                if (!$materiel) { continue; }
                $stock = $materiel->getStockCentral();
                // Crée le stock central inexistant (autorisé uniquement si ENTREE)
                if (!$stock) {
                    if ($action === 'ajout') {
                        $this->addFlash('danger', 'Stock insuffisant pour sortie');
                        return $this->render('mouvement_stock/new.html.twig', [
                            'form' => $form,
                            'is_edit' => false,
                            'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                        ]);
                    }
                    $stock = new StockCentral();
                    $stock->setMateriel($materiel);
                    $stock->setQuantiteStockCentral(0);
                    $em->persist($stock);
                    $materiel->setStockCentral($stock);
                }
                $current = (int)($stock->getQuantiteStockCentral() ?? 0);
                // En sortie, vérifie que la quantité est disponible au central
                if ($action === 'ajout') {
                    if ($current - $qty < 0) {
                        $this->addFlash('danger', 'Stock insuffisant pour sortie');
                        return $this->render('mouvement_stock/new.html.twig', [
                            'form' => $form,
                            'is_edit' => false,
                            'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                        ]);
                    }
                }
                // En retour, s'assure que le magasin possède suffisamment d'unités
                if ($action === 'retour' && $magasin) {
                    $gid = $magasin->getId();
                    $sumSortie = (int)($mvRepo->createQueryBuilder('mv')
                        ->select('COALESCE(SUM(mv.quantiteMouvement), 0)')
                        ->where('mv.typeMouvement = :t AND mv.magasin = :g AND mv.materiel = :m')
                        ->setParameter('t', 'SORTIE')
                        ->setParameter('g', $gid)
                        ->setParameter('m', $materiel->getId())
                        ->getQuery()->getSingleScalarResult());
                    $sumEntreeRetour = (int)($mvRepo->createQueryBuilder('mv')
                        ->select('COALESCE(SUM(mv.quantiteMouvement), 0)')
                        ->where('mv.typeMouvement = :t AND mv.magasin = :g AND mv.materiel = :m')
                        ->setParameter('t', 'ENTREE')
                        ->setParameter('g', $gid)
                        ->setParameter('m', $materiel->getId())
                        ->getQuery()->getSingleScalarResult());
                    $magStockBefore = $sumSortie - $sumEntreeRetour;
                    if ($magStockBefore < $qty) {
                        $this->addFlash('danger', 'Stock magasin insuffisant pour retour');
                        return $this->render('mouvement_stock/new.html.twig', [
                            'form' => $form,
                            'is_edit' => false,
                            'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                        ]);
                    }
                }
                // Instancie le mouvement avec le bon type en fonction de l'action
                $mv = new MouvementStock();
                $mv->setDateMouvement($now);
                if ($action === 'ajout') {
                    $mv->setTypeMouvement('SORTIE');
                } else {
                    $mv->setTypeMouvement('ENTREE');
                }
                $mv->setQuantiteMouvement($qty);
                $mv->setMateriel($materiel);
                if ($action !== 'central' && $magasin) {
                    $mv->setMagasin($magasin);
                }
                // Applique le delta au stock central (+ENTREE / -SORTIE)
                $delta = $qty * ($mv->getTypeMouvement() === 'ENTREE' ? 1 : -1);
                $stock->setQuantiteStockCentral(($stock->getQuantiteStockCentral() ?? 0) + $delta);
                $em->persist($mv);
            }
            // Persiste toutes les lignes en une fois
            $em->flush();
            $this->addFlash('success', 'Mouvements enregistrés');
            return $this->redirectToRoute('app_mouvement_index');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement unitaire (fallback et mode édition)
            $action = $request->request->get('_action');
            $materiel = $mouvement->getMateriel();
            $stock = $materiel->getStockCentral();
            $qty = (int)($mouvement->getQuantiteMouvement() ?? 0);
            if (!$mouvement->getDateMouvement()) {
                $mouvement->setDateMouvement(new \DateTimeImmutable());
            }
            if ($qty <= 0) {
                $this->addFlash('danger', 'Quantité invalide');
                return $this->render('mouvement_stock/new.html.twig', [
                    'form' => $form,
                    'is_edit' => false,
                    'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                ]);
            }
            // Fixe le type selon l'action et impose le magasin si nécessaire
            if ($action === 'retour') {
                $mouvement->setTypeMouvement('ENTREE');
                if (!$mouvement->getMagasin()) {
                    $this->addFlash('danger', 'Magasin ALDI requis pour retour');
                    return $this->render('mouvement_stock/new.html.twig', [
                        'form' => $form,
                        'is_edit' => false,
                        'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                    ]);
                }
            } elseif ($action === 'ajout') {
                $mouvement->setTypeMouvement('SORTIE');
                if (!$mouvement->getMagasin()) {
                    $this->addFlash('danger', 'Magasin requis pour ajout');
                    return $this->render('mouvement_stock/new.html.twig', [
                        'form' => $form,
                        'is_edit' => false,
                        'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                    ]);
                }
            }
            // Delta appliqué au stock central: +q pour ENTREE, -q pour SORTIE
            $delta = (int)$mouvement->getQuantiteMouvement() * ($mouvement->getTypeMouvement() === 'ENTREE' ? 1 : -1);

            // Gestion du stock central (toujours)
            if (!$stock) {
                if ($mouvement->getTypeMouvement() === 'SORTIE') {
                    $this->addFlash('danger', 'Stock insuffisant pour sortie');
                    return $this->render('mouvement_stock/new.html.twig', [
                        'form' => $form,
                        'is_edit' => false,
                        'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                    ]);
                }
                $stock = new StockCentral();
                $stock->setMateriel($materiel);
                $stock->setQuantiteStockCentral(0);
                $em->persist($stock);
                $materiel->setStockCentral($stock);
            }

            // Stock central courant
            $current = (int)($stock->getQuantiteStockCentral() ?? 0);
            if ($mouvement->getTypeMouvement() === 'SORTIE' && $current + $delta < 0) {
                $this->addFlash('danger', 'Stock insuffisant pour sortie');
                return $this->render('mouvement_stock/new.html.twig', [
                    'form' => $form,
                    'is_edit' => false,
                    'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                ]);
            }

            // Calcule les indicateurs avant/après pour le central et éventuellement le magasin
            $gid = $mouvement->getMagasin() ? $mouvement->getMagasin()->getId() : null;
            $mid = $mouvement->getMateriel()->getId();
            $magStockBefore = null;
            if ($gid) {
                // Sorties déjà réalisées dans le magasin
                $sumSortie = (int)($mvRepo->createQueryBuilder('mv')
                    ->select('COALESCE(SUM(mv.quantiteMouvement), 0)')
                    ->where('mv.typeMouvement = :t AND mv.magasin = :g AND mv.materiel = :m')
                    ->setParameter('t', 'SORTIE')
                    ->setParameter('g', $gid)
                    ->setParameter('m', $mid)
                    ->getQuery()->getSingleScalarResult());
                // Retours (ENTREE) déjà enregistrés pour ce magasin
                $sumEntreeRetour = (int)($mvRepo->createQueryBuilder('mv')
                    ->select('COALESCE(SUM(mv.quantiteMouvement), 0)')
                    ->where('mv.typeMouvement = :t AND mv.magasin = :g AND mv.materiel = :m')
                    ->setParameter('t', 'ENTREE')
                    ->setParameter('g', $gid)
                    ->setParameter('m', $mid)
                    ->getQuery()->getSingleScalarResult());
                $magStockBefore = $sumSortie - $sumEntreeRetour;
                if ($mouvement->getTypeMouvement() === 'ENTREE' && $magStockBefore < $qty) {
                    $this->addFlash('danger', 'Stock magasin insuffisant pour retour');
                    return $this->render('mouvement_stock/new.html.twig', [
                        'form' => $form,
                        'is_edit' => false,
                        'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findAll(),
                    ]);
                }
            }

            // Mise à jour du stock central et calcul post-opération
            $stock->setQuantiteStockCentral(($stock->getQuantiteStockCentral() ?? 0) + $delta);
            $centralAfter = (int)($stock->getQuantiteStockCentral() ?? 0);
            $magasinAfter = null;
            if ($gid) {
                if ($action === 'ajout') {
                    $magasinAfter = (int)($magStockBefore ?? 0) + $qty;
                } elseif ($action === 'retour') {
                    $magasinAfter = max(0, (int)($magStockBefore ?? 0) - $qty);
                }
            }

            // Persistance et logging du mouvement unitaire
            $em->persist($mouvement);
            $em->flush();
            $logger->info('mouvement_stock_enregistre', [
                'action' => $action,
                'type' => $mouvement->getTypeMouvement(),
                'materiel_id' => $mid,
                'magasin_id' => $gid,
                'quantite' => $qty,
                'central_before' => $current,
                'central_after' => $centralAfter,
                'magasin_before' => $magStockBefore,
                'magasin_after' => $magasinAfter,
            ]);
            $this->addFlash('success', sprintf(
                'OK — Central: %d → %d%s',
                (int)$current,
                (int)$centralAfter,
                $gid ? sprintf(', Magasin: %d → %d', (int)($magStockBefore ?? 0), (int)($magasinAfter ?? 0)) : ''
            ));
            return $this->redirectToRoute('app_mouvement_index');
        }

        return $this->render('mouvement_stock/new.html.twig', [
            'form' => $form,
            'is_edit' => false,
            'materiels' => $em->getRepository(\App\Entity\Materiel::class)->findBy(array(), array('nom' => 'asc')),
        ]);
    }

    /**
     * Édition d'un mouvement existant.
     *
     * Flux:
     * - Calcul des deltas à appliquer au stock central (ancienne vs nouvelle quantité/type).
     * - Même matériel: applique la différence. Matériel différent: répartit entre anciens/nouveaux stocks.
     *
     * Règle: le type (ENTREE/SORTIE) ne peut pas être modifié.
     * Ajuste le stock central selon la différence (nouvelle quantité/matériel).
     * Sorties: redirection si succès, sinon rendu formulaire avec erreurs.
     *
     * @param MouvementStock $mouvement
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/mouvements/{id}/edition', name: 'app_mouvement_edit')]
    public function edit(MouvementStock $mouvement, Request $request, EntityManagerInterface $em): Response
    {
        // Sauvegarde l'état initial pour calculer les ajustements
        $prevType = $mouvement->getTypeMouvement();
        $prevQty = (int)($mouvement->getQuantiteMouvement() ?? 0);
        $prevMateriel = $mouvement->getMateriel();
        // Form et handling
        $form = $this->createForm(MouvementStockType::class, $mouvement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Met à jour la date si manquante
            $action = $request->request->get('_action');
            if (!$mouvement->getDateMouvement()) {
                $mouvement->setDateMouvement(new \DateTimeImmutable());
            }

            // Interdit le changement de type (ENTREE/SORTIE) après création
            $newType = $mouvement->getTypeMouvement();
            if ($newType !== $prevType) {
                $this->addFlash('danger', 'Le type de mouvement ne peut pas être modifié');
                return $this->render('mouvement_stock/new.html.twig', [
                    'form' => $form,
                    'is_edit' => true,
                ]);
            }
            $newQty = (int)($mouvement->getQuantiteMouvement() ?? 0);
            $newMateriel = $mouvement->getMateriel();

            // Calcule les deltas à appliquer au stock central
            $deltaPrev = $prevQty * ($prevType === 'ENTREE' ? 1 : -1);
            $deltaNew = $newQty * ($newType === 'ENTREE' ? 1 : -1);

            // Ajuste le stock central: même matériel → appliquer la différence; sinon répartir
            if ($prevMateriel && $newMateriel && $prevMateriel->getId() === $newMateriel->getId()) {
                $stock = $newMateriel->getStockCentral();
                if (!$stock) {
                    $stock = new StockCentral();
                    $stock->setMateriel($newMateriel);
                    $stock->setQuantiteStockCentral(0);
                    $em->persist($stock);
                    $newMateriel->setStockCentral($stock);
                }
                $current = (int)($stock->getQuantiteStockCentral() ?? 0);
                $newCurrent = $current + ($deltaNew - $deltaPrev);
                if ($newType === 'SORTIE' && $newCurrent < 0) {
                    $this->addFlash('danger', 'Stock insuffisant après modification');
                    return $this->render('mouvement_stock/new.html.twig', [
                        'form' => $form,
                        'is_edit' => true,
                    ]);
                }
                $stock->setQuantiteStockCentral($newCurrent);
            } else {
                if ($prevMateriel) {
                    $pStock = $prevMateriel->getStockCentral();
                    if (!$pStock) {
                        $pStock = new StockCentral();
                        $pStock->setMateriel($prevMateriel);
                        $pStock->setQuantiteStockCentral(0);
                        $em->persist($pStock);
                        $prevMateriel->setStockCentral($pStock);
                    }
                    $pCurrent = (int)($pStock->getQuantiteStockCentral() ?? 0);
                    $pStock->setQuantiteStockCentral($pCurrent - $deltaPrev);
                }
                if ($newMateriel) {
                    $nStock = $newMateriel->getStockCentral();
                    if (!$nStock) {
                        $nStock = new StockCentral();
                        $nStock->setMateriel($newMateriel);
                        $nStock->setQuantiteStockCentral(0);
                        $em->persist($nStock);
                        $newMateriel->setStockCentral($nStock);
                    }
                    $nCurrent = (int)($nStock->getQuantiteStockCentral() ?? 0);
                    $nStock->setQuantiteStockCentral($nCurrent + $deltaNew);
                }
            }

            // Persistance
            $em->flush();
            $this->addFlash('success', 'Mouvement modifié');
            return $this->redirectToRoute('app_mouvement_index');
        }

        return $this->render('mouvement_stock/new.html.twig', [
            'form' => $form,
            'is_edit' => true,
        ]);
    }

    /**
     * API: stock central pour un matériel.
     *
     * Flux:
     * - Retourne le stock central courant pour l'id matériel en paramètre de route.
     *
     * Entrée: `id` (paramètre de route — identifiant du matériel).
     * Sortie: JSON `{ stock: number }`.
     *
     * @param \App\Entity\Materiel $materiel
     * @return Response
     */
    #[Route('/mouvements/stock/{id}', name: 'app_mouvement_stock_for_materiel', methods: ['GET'])]
    public function stockForMateriel(\App\Entity\Materiel $materiel): Response
    {
        // Retourne le stock central courant pour le matériel donné
        $stock = $materiel->getStockCentral();
        $qty = $stock ? (int)($stock->getQuantiteStockCentral() ?? 0) : 0;
        return $this->json(['stock' => $qty]);
    }

    /**
     * API: stock d'un matériel dans un magasin (sorties - retours).
     *
     * Flux:
     * - Calcule `sorties - retours` pour (magasinId, materielId), borné à 0.
     *
     * Entrées: `magasinId`, `materielId` (params de route).
     * Sortie: JSON `{ stock: number }`.
     *
     * @param int $magasinId
     * @param int $materielId
     * @param \App\Repository\MouvementStockRepository $mvRepo
     * @return Response
     */
    #[Route('/mouvements/stock-magasin/{magasinId}/{materielId}', name: 'app_mouvement_stock_magasin', methods: ['GET'])]
    public function stockForMagasin(int $magasinId, int $materielId, \App\Repository\MouvementStockRepository $mvRepo): Response
    {
        // Calcule le stock magasin comme « sorties - retours » pour le couple (magasin, matériel)
        $sumSortie = (int)($mvRepo->createQueryBuilder('mv')
            ->select('COALESCE(SUM(mv.quantiteMouvement), 0)')
            ->where('mv.typeMouvement = :t AND mv.magasin = :g AND mv.materiel = :m')
            ->setParameter('t', 'SORTIE')
            ->setParameter('g', $magasinId)
            ->setParameter('m', $materielId)
            ->getQuery()->getSingleScalarResult());
        $sumEntreeRetour = (int)($mvRepo->createQueryBuilder('mv')
            ->select('COALESCE(SUM(mv.quantiteMouvement), 0)')
            ->where('mv.typeMouvement = :t AND mv.magasin = :g AND mv.materiel = :m')
            ->setParameter('t', 'ENTREE')
            ->setParameter('g', $magasinId)
            ->setParameter('m', $materielId)
            ->getQuery()->getSingleScalarResult());
        $qty = max(0, $sumSortie - $sumEntreeRetour);
        return $this->json(['stock' => $qty]);
    }

    /**
     * Suppression d'un mouvement.
     *
     * Entrées: token CSRF `delete_mouvement_{id}`.
     * Sorties: flash + redirection index.
     *
     * @param MouvementStock $mouvement
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    

    /**
     * Entrée rapide (approvisionnement central ou retour magasin).
     *
     * Flux:
     * - Crée une ENTREE sur le central (sans magasin) ou un retour magasin (avec magasin).
     *
     * Entrées: `materiel_id`, `magasin_id?`, `qty`.
     * Cas: sans magasin → approvisionnement central; avec magasin → retour.
     * Sortie: redirection vers l'index des matériels avec flash.
     * Validation: CSRF, quantité positive, matériel existant.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param \App\Repository\MaterielRepository $mRepo
     * @param \App\Repository\MagasinRepository $gRepo
     * @return Response
     */
    #[Route('/mouvements/entree-rapide', name: 'app_mouvement_quick_entry', methods: ['POST'])]
    public function quickEntry(Request $request, EntityManagerInterface $em, \App\Repository\MaterielRepository $mRepo, \App\Repository\MagasinRepository $gRepo): Response
    {
        // Lecture des paramètres et validation CSRF
        $materielId = $request->request->getInt('materiel_id');
        $magasinId = $request->request->getInt('magasin_id');
        $qty = $request->request->getInt('qty');
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('quick_entry_materiel_'.$materielId, $token)) {
            $this->addFlash('danger', 'Échec CSRF');
            return $this->redirectToRoute('app_materiel_index');
        }
        // Paramètres de base
        if ($qty <= 0 || !$materielId) {
            $this->addFlash('danger', 'Paramètres invalides');
            return $this->redirectToRoute('app_materiel_index');
        }
        // Récupération des entités concernées
        $materiel = $mRepo->find($materielId);
        if (!$materiel) {
            $this->addFlash('danger', 'Matériel introuvable');
            return $this->redirectToRoute('app_materiel_index');
        }
        $magasin = $magasinId ? $gRepo->find($magasinId) : null;
        // Création du mouvement d'entrée (central ou retour magasin)
        $mouvement = new MouvementStock();
        $mouvement->setDateMouvement(new \DateTimeImmutable());
        $mouvement->setTypeMouvement('ENTREE');
        $mouvement->setQuantiteMouvement($qty);
        $mouvement->setMateriel($materiel);
        if ($magasin) {
            $mouvement->setMagasin($magasin);
        }

        // Mise à jour du stock central
        $stock = $materiel->getStockCentral();
        if (!$stock) {
            $stock = new StockCentral();
            $stock->setMateriel($materiel);
            $stock->setQuantiteStockCentral(0);
            $em->persist($stock);
            $materiel->setStockCentral($stock);
        }
        $current = (int)($stock->getQuantiteStockCentral() ?? 0);
        $stock->setQuantiteStockCentral($current + $qty);

        // Persiste et notifie
        $em->persist($mouvement);
        $em->flush();
        $this->addFlash('success', $magasin ? 'Retour enregistré' : 'Quantité centrale ajoutée');
        return $this->redirectToRoute('app_materiel_index');
    }
}
