<?php

namespace App\Form;

use App\Entity\Subscription;
use App\Entity\Customer;
use App\Entity\SubscriptionPlan;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a customer',
                'required' => true,
            ])
            ->add('plan', EntityType::class, [
                'class' => SubscriptionPlan::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a plan',
                'required' => true,
            ])
            ->add('stripeSubscriptionId', TextType::class, [
                'required' => false,
                'label' => 'Stripe Subscription ID',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Active' => 'active',
                    'Inactive' => 'inactive',
                    'Cancelled' => 'cancelled',
                    'Past Due' => 'past_due',
                    'Trialing' => 'trialing',
                ],
                'required' => true,
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Start Date',
            ])
            ->add('currentPeriodStart', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Current Period Start',
            ])
            ->add('currentPeriodEnd', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Current Period End',
            ])
            ->add('cancelAtPeriodEnd', CheckboxType::class, [
                'required' => false,
                'label' => 'Cancel at Period End',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Subscription::class,
        ]);
    }
}
