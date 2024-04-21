<?php

namespace App\Form;
use App\Entity\Event;
use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class Reservation2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        // autres champs de formulaire...
        ->add('idevent', EntityType::class, [
            'class' => Event::class,
            'choice_label' => 'nom', // Champ utilisé comme libellé dans le menu déroulant

        ])
        ->add('date')
        ->add('nbplaces')
    ;
}
    

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
