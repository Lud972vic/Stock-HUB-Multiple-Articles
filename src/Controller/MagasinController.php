<?php

namespace App\Controller;

use App\Entity\Magasin;
use App\Form\MagasinType;
use App\Repository\MagasinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur des magasins.
 *
 * Gère liste, création, modification et suppression de magasins.
 * Recherche multi-colonnes incluant ville, centrale, statut et type de projet.
 */
class MagasinController extends AbstractController
{
    /**
     * Liste paginée des magasins avec recherche.
     *
     * Entrées:
     * - Query `q` (texte, partiel, insensible à la casse)
     * - Query `page` (entier ≥ 1)
      * Sorties:
     * - Template `magasin/index.html.twig` avec `magasins`, pagination et listes.
     * Dépendances:
     * - `MagasinRepository` + jointures `Ville`, `Centrale`, `Statut`, `TypeProjet`.
     * Cas limites:
     * - Combinaison de filtres + recherche.
     * Exemple: /magasins?q=centre&ville=3&page=1
     *
     * @param Request $request
     * @param MagasinRepository $repo
     * @return Response
     */
    #[Route('/magasins', name: 'app_magasin_index')]
    public function index(Request $request, MagasinRepository $repo, \App\Repository\VilleRepository $vRepo, \App\Repository\CentraleRepository $cRepo, \App\Repository\StatutRepository $sRepo, \App\Repository\TypeProjetRepository $tRepo): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $q = $request->query->get('q');
        $villeId = $request->query->getInt('ville');
        $centraleId = $request->query->getInt('centrale');
        $statutId = $request->query->getInt('statut');
        $typeId = $request->query->getInt('typeProjet');

        $qb = $repo->createQueryBuilder('g')
            ->leftJoin('g.ville', 'v')->addSelect('v')
            ->leftJoin('g.centrale', 'c')->addSelect('c')
            ->leftJoin('g.statut', 's')->addSelect('s')
            ->leftJoin('g.typeProjet', 't')->addSelect('t')
            ->orderBy('g.nomMagasin', 'ASC');

        if ($q) {
            $qb->andWhere('LOWER(g.codeMagasin) LIKE :q OR LOWER(g.nomMagasin) LIKE :q OR LOWER(v.nomVille) LIKE :q OR LOWER(c.nomCentrale) LIKE :q OR LOWER(s.nomStatut) LIKE :q OR LOWER(t.nomTypeProjet) LIKE :q')
               ->setParameter('q', '%'.mb_strtolower($q).'%');
        }

        if ($villeId) {
            $qb->andWhere('v.id = :vid')->setParameter('vid', $villeId);
        }
        if ($centraleId) {
            $qb->andWhere('c.id = :cid')->setParameter('cid', $centraleId);
        }
        if ($statutId) {
            $qb->andWhere('s.id = :sid')->setParameter('sid', $statutId);
        }
        if ($typeId) {
            $qb->andWhere('t.id = :tid')->setParameter('tid', $typeId);
        }

        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        $magasins = $qb->getQuery()->getResult();

        return $this->render('magasin/index.html.twig', [
            'magasins' => $magasins,
            'page' => $page,
            'limit' => $limit,
            'q' => $q,
            'ville' => $villeId,
            'centrale' => $centraleId,
            'statut' => $statutId,
            'typeProjet' => $typeId,
            'villes' => $vRepo->findAll(),
            'centrales' => $cRepo->findAll(),
            'statuts' => $sRepo->findAll(),
            'typesProjet' => $tRepo->findAll(),
        ]);
    }

    /**
     * Création d'un magasin.
     *
     * Entrées: formulaire `MagasinType` (code, nom, ville, centrale, statut, type).
     * Sorties: redirection vers index si succès; sinon rend le formulaire avec erreurs.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/magasins/nouveau', name: 'app_magasin_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $magasin = new Magasin();
        $form = $this->createForm(MagasinType::class, $magasin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($magasin);
                $em->flush();
                $this->addFlash('success', 'Magasin créé');
                return $this->redirectToRoute('app_magasin_index');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('codeMagasin')->addError(new FormError('Ce code magasin existe déjà.'));
                $this->addFlash('danger', 'Le code magasin est déjà utilisé.');
            }
        }

        return $this->render('magasin/new.html.twig', [
            'form' => $form,
            'is_edit' => false,
        ]);
    }

    /**
     * Édition d'un magasin.
     *
     * Entrées: entité `Magasin` et formulaire.
     * Sorties: redirection vers index si succès; sinon rend le formulaire.
     *
     * @param Magasin $magasin
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/magasins/{id}/edition', name: 'app_magasin_edit')]
    public function edit(Magasin $magasin, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MagasinType::class, $magasin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Magasin modifié');
                return $this->redirectToRoute('app_magasin_index');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('codeMagasin')->addError(new FormError('Ce code magasin existe déjà.'));
                $this->addFlash('danger', 'Le code magasin est déjà utilisé.');
            }
        }

        return $this->render('magasin/new.html.twig', [
            'form' => $form,
            'is_edit' => true,
        ]);
    }

    /**
     * Suppression d'un magasin avec confirmation CSRF.
     *
     * Entrées: token CSRF `delete_magasin_{id}`.
     * Sorties: flash + redirection.
     * Cas limites: suppression rejetée si contraintes.
     *
     * @param Magasin $magasin
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/magasins/{id}/supprimer', name: 'app_magasin_delete', methods: ['POST'])]
    public function delete(Magasin $magasin, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_magasin_'.$magasin->getId(), $token)) {
            try {
                $em->remove($magasin);
                $em->flush();
                $this->addFlash('success', 'Magasin supprimé');
            } catch (\Throwable) {
                $this->addFlash('danger', 'Suppression impossible: dépendances existantes');
            }
        } else {
            $this->addFlash('danger', 'Échec de la suppression (CSRF)');
        }
        return $this->redirectToRoute('app_magasin_index');
    }
}
