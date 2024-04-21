<?php

namespace App\Form;

use App\Entity\Orders;
use App\Entity\Products;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\GreaterThan;

class OrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('Order_date')
        ->add('Total_price', IntegerType::class, [
            'constraints' => [
                new GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'The total price must be greater than or equal to {{ compared_value }}.'
                ]),
            ],
        ])
        ->add('Quantity', IntegerType::class, [
            'constraints' => [
                new GreaterThan([
                    'value' => 0,
                    'message' => 'The quantity must be greater than {{ compared_value }}.'
                ]),
            ],
        ])
        ->add('Product_name', TextType::class)
        ->add('User_name', TextType::class)
        ->add('products', EntityType::class, [
            'class' => Products::class,
            'choice_label' => 'id',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
}
