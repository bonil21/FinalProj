<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products_index')]
    public function index(): Response
    {
        $products = [
            [
                'name' => 'Garden Glow',
                'price' => 199.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/GardenGlow.jpg',
            ],
            [
                'name' => 'Mediterranean Feta Feast',
                'price' => 229.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/MediterraneanFetaFeast.jpg',
            ],
            [
                'name' => 'Global Grain Bowl',
                'price' => 249.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/GlobalGrainBowl.jpg',
            ],
            [
                'name' => 'Summer Berry Power Bowl',
                'price' => 219.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/SummerBerryPowerBowl.jpg',
            ],
            [
                'name' => 'Harvest Bowl',
                'price' => 199.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/HarvestBowl.jpg',
            ],
            [
                'name' => 'Caprese Avocado Delight',
                'price' => 239.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/CapreseAvocadoDelight.jpg',
            ],
            [
                'name' => 'Classic Avocado',
                'price' => 189.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/ClassisAvocado.jpg',
            ],
            [
                'name' => 'Mediterranean Bliss',
                'price' => 229.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/MaditerraneanBlissSalad.jpg',
            ],
            [
                'name' => 'Rainbow Fresh Salad',
                'price' => 209.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/RainbowFreshSalad.jpg',
            ],
            [
                'name' => 'Sunshine Fruit & Veggie Bowl',
                'price' => 219.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/SunshineFruit&VeggieBowl.jpg',
            ],
            [
                'name' => 'Vitality Crunch Bowl',
                'price' => 229.00,
                'unit' => 'per bowl',
                'image' => 'imageeee/VitalityCurnchBowl.jpg',
            ],
        ];

        return $this->render('products/index.html.twig', [
            'products' => $products,
        ]);
    }
}


