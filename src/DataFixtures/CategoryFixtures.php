<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Salads & Bowls',
            'Wraps & Sandwiches',
            'Smoothies & Juices',
            'Cold-Pressed Drinks',
        ];

        foreach ($categories as $catName) {
            $category = new Category();
            $category->setName($catName);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
