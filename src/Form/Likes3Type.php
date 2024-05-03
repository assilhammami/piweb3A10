<?php

namespace App\Form;

use App\Entity\Likes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class Likes3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('postId')
            ->add('userId')
            ->add('reactionType', ChoiceType::class, [
                'label' => 'Reaction Type',
                'choices' => [
                    'NON' => 'NON',
                    'LIKE' => 'LIKE',
                    'LOVE' => 'LOVE',
                    'CARE' => 'CARE',
                    'HAHA' => 'HAHA',
                    'WOW' => 'WOW',
                    'SAD' =>'SAD',
                    'ANGRY' =>'ANGRY',
                ],
                
            ])
            ->add('createdAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Likes::class,
        ]);
    }
}