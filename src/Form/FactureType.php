<?php

namespace App\Form;

use App\Entity\Facture;
use App\Entity\TypeFacture;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FactureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('numeroFacture')
            ->add('designation')
            ->add('notes')
            ->add('dateDebut')
            ->add('dateFin')
            ->add('type', EntityType::class, [
            'class' => TypeFacture::class, // Remplacez par votre entité de rôle
            'choice_label' => 'designation', // Remplacez par le nom du champ utilisé pour l'affichage des rôles
            'multiple' => true,
            'expanded' => true,

    ]);

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Facture::class,
        ]);
    }
}
