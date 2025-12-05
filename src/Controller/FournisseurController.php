<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Form\FournisseurType;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Materiel;

/**
 * Contrôleur des fournisseurs.
 *
 * Gère liste, création, modification et suppression de fournisseurs.
 * Inclut une recherche simple sur code/nom.
 */
class FournisseurController extends AbstractController
{
    /**
     * Liste paginée des fournisseurs avec recherche.
     *
     * Entrées: Query `q` (texte) et `page`.
     * Sorties: template `fournisseur/index.html.twig`.
     * Dépendances: `FournisseurRepository`.
     * Exemple: /fournisseurs?q=cisco
     *
     * @param Request $request
     * @param FournisseurRepository $repo
     * @return Response
     */
    #[Route('/fournisseurs', name: 'app_fournisseur_index')]
    public function index(Request $request, FournisseurRepository $repo): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;
        $q = $request->query->get('q');

        $qb = $repo->createQueryBuilder('f')
            ->orderBy('f.nomFournisseur', 'ASC');

        if ($q) {
            $qb->andWhere('LOWER(f.codeFournisseur) LIKE :q OR LOWER(f.nomFournisseur) LIKE :q')
               ->setParameter('q', '%'.mb_strtolower($q).'%');
        }

        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        $fournisseurs = $qb->getQuery()->getResult();

        return $this->render('fournisseur/index.html.twig', [
            'fournisseurs' => $fournisseurs,
            'page' => $page,
            'limit' => $limit,
            'q' => $q,
        ]);
    }

    /**
     * Création d'un fournisseur.
     *
     * Entrées: formulaire `FournisseurType`.
     * Sorties: redirection vers index si succès.
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/fournisseurs/nouveau', name: 'app_fournisseur_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $fournisseur = new Fournisseur();
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->persist($fournisseur);
                $em->flush();
                $this->addFlash('success', 'Fournisseur créé');
                return $this->redirectToRoute('app_fournisseur_index');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('codeFournisseur')->addError(new FormError('Ce code fournisseur existe déjà.'));
                $this->addFlash('danger', 'Le code fournisseur est déjà utilisé.');
            }
        }

        return $this->render('fournisseur/new.html.twig', [
            'form' => $form,
            'is_edit' => false,
        ]);
    }

    /**
     * Édition d'un fournisseur.
     *
     * Entrées: entité `Fournisseur` et formulaire.
     * Sorties: redirection vers index si succès.
     *
     * @param Fournisseur $fournisseur
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/fournisseurs/{id}/edition', name: 'app_fournisseur_edit')]
    public function edit(Fournisseur $fournisseur, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(FournisseurType::class, $fournisseur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $em->flush();
                $this->addFlash('success', 'Fournisseur modifié');
                return $this->redirectToRoute('app_fournisseur_index');
            } catch (UniqueConstraintViolationException $e) {
                $form->get('codeFournisseur')->addError(new FormError('Ce code fournisseur existe déjà.'));
                $this->addFlash('danger', 'Le code fournisseur est déjà utilisé.');
            }
        }

        return $this->render('fournisseur/new.html.twig', [
            'form' => $form,
            'is_edit' => true,
        ]);
    }

    /**
     * Suppression d'un fournisseur.
     *
     * Entrées: token CSRF `delete_fournisseur_{id}`.
     * Sorties: flash + redirection index.
     * Cas limites: interdit s'il existe des matériels liés.
     *
     * @param Fournisseur $fournisseur
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/fournisseurs/{id}/supprimer', name: 'app_fournisseur_delete', methods: ['POST'])]
    public function delete(Fournisseur $fournisseur, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_fournisseur_'.$fournisseur->getId(), $token)) {
            $materielCount = (int)$em->getRepository(Materiel::class)->count(['fournisseur' => $fournisseur]);
            if ($materielCount > 0) {
                $this->addFlash('danger', sprintf('Suppression interdite: %d article(s) lié(s)', $materielCount));
                return $this->redirectToRoute('app_fournisseur_index');
            }
            $em->remove($fournisseur);
            $em->flush();
            $this->addFlash('success', 'Fournisseur supprimé');
        } else {
            $this->addFlash('danger', 'Échec de la suppression (CSRF)');
        }
        return $this->redirectToRoute('app_fournisseur_index');
    }
}
