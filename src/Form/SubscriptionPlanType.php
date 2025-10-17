<?php

namespace App\Form;

use App\Entity\SubscriptionPlan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('price', MoneyType::class, [
                'currency' => 'USD',
                'required' => true,
                'label' => 'Price',
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
                'attr' => ['min' => 1]
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
