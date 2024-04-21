<?php

namespace App\Form;

use App\Entity\Products;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Name', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a name.']),
            ],
        ])
        ->add('Description', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a description.']),
            ],
        ])
        ->add('Price', IntegerType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a price.']),
                new GreaterThan([
                    'value' => 0,
                    'message' => 'The price must be greater than {{ compared_value }}.'
                ]),
            ],
        ])
        ->add('Stock', IntegerType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a stock value.']),
                new GreaterThan([
                    'value' => -1,
                    'message' => 'The stock must be greater than {{ compared_value }}.'
                ]),
            ],
        ])
        ->add('Creation_date')
        ->add('Category', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Please enter a category.']),
            ],
        ])
        ->add('Image', FileType::class, [
            'label' => 'Image',
            'required' => false,
            'mapped' => false,
            'attr' => [
                'class' => 'form-control-file',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
