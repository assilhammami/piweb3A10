<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('photo_de_profile', FileType::class, [
            'label' => 'Your profile picture :',
            // unmapped means that this field is not associated to any entity property
            'mapped' => false,
            // make it optional so you don't have to re-upload the PDF file
            // every time you edit the Product details
            'required' => true,
            // unmapped fields can't define their validation using attributes
            // in the associated entity, so you can use the PHP constraint classes
            'constraints' => [
                // ...

                                new NotBlank([
                    'message' => 'Please select an image file to upload',
                ]),
                new File([
                    'maxSize' => '2048k',
                    'mimeTypes' => [
                        'image/gif',
                        'image/jpeg',
                        'image/jpg',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid Image',
                ])
            ],
        ])
        
    
            ->add('nom',TextType::class,
            ['label' => 'Your name :'] )
            ->add('prenom'
            ,TextType::class,
            ['label' => 'Your Last Name :'])
            ->add('email',TextType::class,
            ['label' => 'Your Email :'])
            ->add('password'
            ,TextType::class,
            ['label' => 'Your Password :'])
            ->add('username'
                ,TextType::class,
            ['label' => 'Your Username :'])
            ->add('num_telephone', TextType::class, [
                'label' => 'Your Phone Number:',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your phone number',
                    ]),
                    // ...

                                        new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Your phone number should have exactly 8 digits',
                    ]),
                    // ...

                                        new Regex([
                        'pattern' => '/^(2|5|4|9)\d{7}$/',
                        'message' => 'Please enter a valid phone number starting with 2, 5, 4, or 9',
                    ]),
                ],
            ])
            ->add('usertype', ChoiceType::class, [
                'choices' => [
                    'Artiste' => 'ARTISTE',
                    'Client' => 'CLIENT',
                ],
                'multiple' => false,
                'expanded' => true,
                'label' => 'Select your user type:',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select your user type',
                    ]),
                ],
            ])
             ->add('date_de_naissance', DateType::class, [
                'years' => range(date('Y') - 100, date('Y')),
                'label' => 'Your Birth date :']
            )
             ->add('Save',SubmitType::class)
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }   
}