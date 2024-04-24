<?php

namespace App\Form;

use App\Entity\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $builder
        ->add('nom')
        ->add('date')
        ->add('capacity')
        ->add('place')
        ->add('description')
        ->add('image', FileType::class, [
            'label' => 'Choisir une image',
            'required'=>false, // Facultatif: indiquez si le champ est obligatoire ou non
            'data_class' => null, // Accepte une chaîne au lieu d'une instance de File
            'attr' => ['accept' => 'image/*'] // Facultatif: permet de limiter le choix aux types d'images
        ]);
    
        

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
