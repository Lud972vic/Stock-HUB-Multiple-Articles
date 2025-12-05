<?php

namespace App\Form;

use App\Entity\MouvementStock;
use App\Entity\Materiel;
use App\Entity\Magasin;
use App\Repository\MagasinRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType MouvementStock
 *
 * Champs:
 * - Type (ENTREE/SORTIE)
 * - Quantité (>0)
 * - Matériel (obligatoire)
 * - Magasin (facultatif pour ENTREE centrale)
 */
class MouvementStockType extends AbstractType
{
    /**
     * Construit le formulaire de mouvement.
     * Entrée: builder/options.
     * Sortie: structure de formulaire avec labels et placeholders.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Type de mouvement: contrôle l'application au stock central et au magasin
            ->add('typeMouvement', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Entrée' => 'ENTREE',
                    'Sortie' => 'SORTIE',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            // Quantité: doit être positive
            ->add('quantiteMouvement', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control', 'min' => 1],
            ])
            // Matériel concerné
            ->add('materiel', EntityType::class, [
                'class' => Materiel::class,
                'choice_label' => 'nom',
                'label' => 'Matériel',
                'placeholder' => 'Sélectionner un matériel',
                'attr' => ['class' => 'form-select'],
            ])
            // Magasin: requis pour les sorties et retours, optionnel pour appro au stock central
            ->add('magasin', EntityType::class, [
                'class' => Magasin::class,
                'choice_label' => 'nomMagasin',
                'label' => 'Magasin',
                'placeholder' => 'Sélectionner un magasin',
                'required' => false,
                'query_builder' => function (MagasinRepository $repo) {
                    return $repo->createQueryBuilder('g')->orderBy('g.nomMagasin', 'ASC');
                },
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MouvementStock::class,
        ]);
    }
}
