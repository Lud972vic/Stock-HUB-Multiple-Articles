<?php

namespace App\Form;

use App\Entity\Fournisseur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FormType Fournisseur
 *
 * Champs:
 * - Code fournisseur (unique)
 * - Nom fournisseur
 */
class FournisseurType extends AbstractType
{
    /**
     * Construit le formulaire de fournisseur.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('codeFournisseur', TextType::class, [
                'label' => 'Code fournisseur',
                'attr' => ['class' => 'form-control', 'placeholder' => 'SUP-001'],
            ])
            ->add('nomFournisseur', TextType::class, [
                'label' => 'Nom fournisseur',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Cisco Systems'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Fournisseur::class,
        ]);
    }
}
