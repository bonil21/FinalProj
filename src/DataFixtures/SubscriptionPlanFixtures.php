<?php

namespace App\DataFixtures;

use App\Entity\SubscriptionPlan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SubscriptionPlanFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $plans = [
            [
                'code' => 'BASIC_WEEKLY',
                'name' => 'Basic Weekly Plan',
                'description' => 'Perfect for individuals who want fresh, healthy meals delivered weekly. Includes 5 nutritious meals with locally sourced ingredients.',
                'price' => '29.99',
                'billingInterval' => 'weekly',
                'mealsIncluded' => 5,
                'active' => true,
            ],
            [
                'code' => 'PREMIUM_WEEKLY',
                'name' => 'Premium Weekly Plan',
                'description' => 'Our most popular plan! Get 10 delicious, chef-prepared meals delivered weekly. Perfect for couples or families who love variety.',
                'price' => '49.99',
                'billingInterval' => 'weekly',
                'mealsIncluded' => 10,
                'active' => true,
            ],
            [
                'code' => 'FAMILY_MONTHLY',
                'name' => 'Family Monthly Plan',
                'description' => 'Great value for families! 40 meals per month with flexible delivery options. Save 15% compared to weekly plans.',
                'price' => '159.99',
                'billingInterval' => 'monthly',
                'mealsIncluded' => 40,
                'active' => true,
            ],
            [
                'code' => 'PREMIUM_MONTHLY',
                'name' => 'Premium Monthly Plan',
                'description' => 'Ultimate convenience with 60 premium meals per month. Includes specialty dietary options and priority delivery.',
                'price' => '199.99',
                'billingInterval' => 'monthly',
                'mealsIncluded' => 60,
                'active' => true,
            ],
            [
                'code' => 'TRIAL_WEEKLY',
                'name' => 'Trial Weekly Plan',
                'description' => 'Try our service risk-free! 3 meals for just $9.99. Perfect for first-time customers.',
                'price' => '9.99',
                'billingInterval' => 'weekly',
                'mealsIncluded' => 3,
                'active' => true,
            ],
            [
                'code' => 'LEGACY_PLAN',
                'name' => 'Legacy Plan (Discontinued)',
                'description' => 'This plan is no longer available for new subscriptions but remains active for existing customers.',
                'price' => '39.99',
                'billingInterval' => 'weekly',
                'mealsIncluded' => 7,
                'active' => false,
            ],
        ];

        foreach ($plans as $planData) {
            $plan = new SubscriptionPlan();
            $plan->setCode($planData['code']);
            $plan->setName($planData['name']);
            $plan->setDescription($planData['description']);
            $plan->setPrice($planData['price']);
            $plan->setBillingInterval($planData['billingInterval']);
            $plan->setMealsIncluded($planData['mealsIncluded']);
            $plan->setActive($planData['active']);

            $manager->persist($plan);
        }

        $manager->flush();
    }
}
