<?php

namespace App\Form;

use App\Entity\Publication;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
{
    $builder
        ->add('image', FileType::class, [
            'label' => 'Choisir une image',
            'mapped' => false, // Ne pas mapper directement ce champ à une propriété de l'entité Publication
            'required' => true, 
        ])
        ->add('titre')
        ->add('description')
        ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $publication = $event->getData();
            $publication->setDatepublication(new \DateTime()); // Définir la date de publication sur la date actuelle
        });
    ;
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publication::class,
        ]);
    }
}
