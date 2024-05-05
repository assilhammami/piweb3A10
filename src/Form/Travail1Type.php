<?php

namespace App\Form;

use App\Entity\Travail;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class Travail1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('description', TextareaType::class, [
            'attr' => ['class' => 'form-control'] // Ajouter la classe 'form-control' pour le champ description
        ])
        ->add('prix', null, [
            'attr' => ['class' => 'form-control'] // Ajouter la classe 'form-control' pour le champ prix
        ])
        ->add('type', null, [
            'attr' => ['class' => 'form-control'] // Ajouter la classe 'form-control' pour le champ type
        ])
        ->add('date_demande', DateType::class, [
            'disabled' => true,
            'attr' => ['class' => 'form-control'] // Ajouter la classe 'form-control' pour le champ date_demande
        ])
        ->add('date_fin', null, [
            'attr' => ['class' => 'form-control'] // Ajouter la classe 'form-control' pour le champ date_fin
        ])
        ->add('titre', null, [
            'attr' => ['class' => 'form-control'] // Ajouter la classe 'form-control' pour le champ titre
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Travail::class,
        ]);
    }
}
