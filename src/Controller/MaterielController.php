<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Form\MaterielType;
use App\Repository\MaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur des matériels.
 *
 * Gère la liste, création, modification et suppression des matériels.
 * Fournit une recherche multi-colonnes et la pagination.
 */
class MaterielController extends AbstractController
{
    /**
     * Liste paginée des matériels avec recherche.
     *
     * Entrées:
     * - Query `q` (texte, partiel, insensible à la casse)
     * - Query `page` (entier ≥ 1)
     * Sorties:
     * - Template `materiel/index.html.twig` avec `materiels`, `page`, `limit`, `q`
     * Dépendances:
     * - `MaterielRepository` (jointure fournisseur)
     * - Interagit avec la vue pour afficher les listes `fournisseurs` et `magasins`
     * Cas limites:
     * - Si `q` est numérique, filtre aussi `valeurUnitaireHt`
     * - Pagination bornée par `limit`
     * Exemple:
     * - /materiels?q=souris&page=2
     *
     * @param Request $request
     * @param MaterielRepository $repo
     * @param \App\Repository\FournisseurRepository $fRepo
     * @param \App\Repository\MagasinRepository $gRepo
     * @return Response
     */
    #[Route('/materiels', name: 'app_materiel_index')]
    public function index(Request $request, MaterielRepository $repo, \App\Repository\FournisseurRepository $fRepo, \App\Repository\MagasinRepository $gRepo): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $q = $request->query->get('q');
        $fournisseurId = $request->query->getInt('fournisseur');

        $qb = $repo->createQueryBuilder('m')
            ->leftJoin('m.fournisseur', 'f')->addSelect('f')
            ->orderBy('m.nom', 'ASC');

        if ($q) {
            $cond = 'LOWER(m.codeArticle) LIKE :q OR LOWER(m.nom) LIKE :q OR LOWER(f.nomFournisseur) LIKE :q';
            if (is_numeric($q)) {
                $cond .= ' OR m.valeurUnitaireHt = :qNum';
            }
            $qb->andWhere($cond)
               ->setParameter('q', '%'.mb_strtolower($q).'%');
            if (is_numeric($q)) {
                $qb->setParameter('qNum', $q + 0);
            }
        }

        if ($fournisseurId) {
            $qb->andWhere('f.id = :fid')->setParameter('fid', $fournisseurId);
        }

        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        $materiels = $qb->getQuery()->getResult();

        return $this->render('materiel/index.html.twig', [
            'materiels' => $materiels,
            'page' => $page,
            'limit' => $limit,
            'q' => $q,
            'fournisseur' => $fournisseurId,
            'fournisseurs' => $fRepo->findAll(),
            'magasins' => $gRepo->findAll(),
        ]);
    }

    /**
     * Création d'un nouveau matériel.
     *
     * Entrées: formulaire `MaterielType` (code, nom, description, valeur HT, fournisseur)
     * Sorties: redirection vers index si succès; sinon rend le formulaire avec erreurs.
     * Dépendances: Doctrine `EntityManagerInterface`.
     * Cas limites: validations `NotBlank`, `Positive`.
     * Exemple: POST /materiels/nouveau.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/materiels/nouveau', name: 'app_materiel_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $materiel = new Materiel();
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($materiel);
                $em->flush();
                $this->addFlash('success', 'Matériel créé');
                return $this->redirectToRoute('app_materiel_index');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('codeArticle')->addError(new FormError('Ce code article existe déjà.'));
                $this->addFlash('danger', 'Le code article est déjà utilisé.');
            }
        }

        return $this->render('materiel/new.html.twig', [
            'form' => $form,
            'is_edit' => false,
        ]);
    }

    /**
     * Édition d'un matériel existant.
     *
     * Entrées: entité `Materiel` et formulaire `MaterielType`.
     * Sorties: redirection vers index si succès; sinon rend le formulaire avec erreurs.
     * Exemple: POST /materiels/{id}/edition.
     *
     * @param Materiel $materiel
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/materiels/{id}/edition', name: 'app_materiel_edit')]
    public function edit(Materiel $materiel, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Matériel modifié');
                return $this->redirectToRoute('app_materiel_index');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('codeArticle')->addError(new FormError('Ce code article existe déjà.'));
                $this->addFlash('danger', 'Le code article est déjà utilisé.');
            }
        }

        return $this->render('materiel/new.html.twig', [
            'form' => $form,
            'is_edit' => true,
        ]);
    }

    /**
     * Suppression d'un matériel.
     *
     * Entrées: token CSRF `delete_materiel_{id}`.
     * Sorties: flash de résultat et redirection vers l'index.
     * Dépendances: Doctrine `EntityManagerInterface`.
     * Cas limites: suppression impossible en présence de dépendances.
     *
     * @param Materiel $materiel
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/materiels/{id}/supprimer', name: 'app_materiel_delete', methods: ['POST'])]
    public function delete(Materiel $materiel, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_materiel_'.$materiel->getId(), $token)) {
            try {
                $em->remove($materiel);
                $em->flush();
                $this->addFlash('success', 'Matériel supprimé');
            } catch (\Throwable $e) {
                $this->addFlash('danger', 'Suppression impossible: dépendances existantes');
            }
        } else {
            $this->addFlash('danger', 'Échec de la suppression (CSRF)');
        }
        return $this->redirectToRoute('app_materiel_index');
    }
}
