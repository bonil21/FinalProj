<?php

namespace App\Form;

use App\Entity\SubscriptionPlan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;

class SubscriptionPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'required' => true,
                'label' => 'Plan Code',
                'attr' => ['placeholder' => 'e.g., BASIC_MONTHLY']
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Plan Name',
                'attr' => ['placeholder' => 'e.g., Basic Monthly Plan']
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'Description',
                'attr' => ['rows' => 4, 'placeholder' => 'Describe what this plan includes...']
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
            ->add('billingInterval', ChoiceType::class, [
                'choices' => [
                    'Monthly' => 'monthly',
                    'Weekly' => 'weekly',
                    'Yearly' => 'yearly',
                ],
                'required' => true,
                'label' => 'Billing Interval',
            ])
            ->add('mealsIncluded', IntegerType::class, [
                'required' => true,
                'label' => 'Meals Included',
                'attr' => ['min' => 1],
                'constraints' => [
                    new NotBlank(['message' => 'Number of meals is required.']),
                    new Positive(['message' => 'Number of meals must be greater than 0.']),
                ],
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'label' => 'Active',
                'data' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubscriptionPlan::class,
        ]);
    }
}
