<?php

namespace App\Form;

use App\Entity\Magasin;
use App\Entity\Ville;
use App\Entity\Centrale;
use App\Entity\Statut;
use App\Entity\TypeProjet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType Magasin
 *
 * Champs: code, nom, ville, centrale, statut, type de projet.
 */
class MagasinType extends AbstractType
{
    /**
     * Construit le formulaire de magasin.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codeMagasin', TextType::class, [
                'label' => 'Code magasin',
                'attr' => ['class' => 'form-control', 'placeholder' => 'MAX-001'],
            ])
            ->add('nomMagasin', TextType::class, [
                'label' => 'Nom magasin',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Magasin ALDI Crégy-les-Meaux'],
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => 'nomVille',
                'label' => 'Ville',
                'placeholder' => 'Sélectionner une ville',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('centrale', EntityType::class, [
                'class' => Centrale::class,
                'choice_label' => 'nomCentrale',
                'label' => 'Centrale',
                'placeholder' => 'Sélectionner une centrale',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('statut', EntityType::class, [
                'class' => Statut::class,
                'choice_label' => 'nomStatut',
                'label' => 'Statut',
                'placeholder' => 'Sélectionner un statut',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('typeProjet', EntityType::class, [
                'class' => TypeProjet::class,
                'choice_label' => 'nomTypeProjet',
                'label' => 'Type de projet',
                'placeholder' => 'Sélectionner un type de projet',
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Magasin::class,
        ]);
    }
}
