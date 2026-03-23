<?php

namespace App\Form;

use App\Entity\Feedback;
use App\Entity\Products;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => 'Your feedback or review',
                'attr' => ['rows' => 4, 'placeholder' => 'Share your experience...'],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Rating (optional)',
                'required' => false,
                'choices' => [
                    '5 stars' => 5,
                    '4 stars' => 4,
                    '3 stars' => 3,
                    '2 stars' => 2,
                    '1 star' => 1,
                ],
                'placeholder' => 'Select a rating',
            ]);

        if ($options['include_product']) {
            $builder->add('product', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'General feedback (no specific product)',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
            'include_product' => false,
        ]);
    }
}
