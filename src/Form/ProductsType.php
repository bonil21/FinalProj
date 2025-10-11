<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class ProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    $builder
        ->add('name')
        ->add('description')
        ->add('price')
        ->add('nutritionalInfo')
        ->add('availability')
        ->add('subscriptionEligible')
        ->add('category', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'placeholder' => 'Select a Category',
            'required' => false,
        ])
        ->add('image', FileType::class, [
            'label' => 'Product Image',
            'mapped' => false, // important — this field isn’t automatically saved
            'required' => false,
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, or WEBP)',
                ])
            ],
        ]);


        }
}