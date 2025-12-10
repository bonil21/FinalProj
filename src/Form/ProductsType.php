<?php

namespace App\Form;

use App\Entity\Products;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class ProductsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    $builder
        ->add('name', null, [
            'constraints' => [
                new NotBlank(['message' => 'Product name is required.']),
            ],
        ])
        ->add('description', null, [
            'constraints' => [
                new NotBlank(['message' => 'Product description is required.']),
            ],
        ])
        ->add('price', NumberType::class, [
            'label' => 'Price (₱)',
            'scale' => 2,
            'html5' => true,
            'attr' => [
                'step' => '0.01',
                'min' => '0',
                'placeholder' => '0.00'
            ],
            'constraints' => [
                new NotBlank(['message' => 'Price is required.']),
                new GreaterThanOrEqual([
                    'value' => 0,
                    'message' => 'Price must be 0 or greater.',
                ]),
            ],
        ])
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
            'mapped' => false, // important — this field isn't automatically saved
            'required' => false,
            'constraints' => $this->getImageConstraints(),
        ]);
    }

    /**
     * Get image validation constraints
     * Handles case where php_fileinfo extension is not available
     */
    private function getImageConstraints(): array
    {
        $constraints = [
            'maxSize' => '5M',
        ];

        // Only add MIME type validation if fileinfo extension is available
        if (extension_loaded('fileinfo')) {
            $constraints['mimeTypes'] = [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];
            $constraints['mimeTypesMessage'] = 'Please upload a valid image file (JPEG, PNG, or WEBP)';
        }

        return [new File($constraints)];
    }
}