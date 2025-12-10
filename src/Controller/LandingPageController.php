<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use App\Repository\SubscriptionPlanRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Landing Page Controller
 * 
 * IMPORTANT: This controller displays data from the SAME database used by Admin and Staff dashboards.
 * 
 * Data Synchronization:
 * - Subscription Plans: Shows all active plans (created by Admin or Staff)
 * 
 * Any changes made in Admin or Staff dashboards are immediately reflected here.
 * Only items marked as "active" are displayed to customers.
 */
final class LandingPageController extends AbstractController
{
    #[Route('/', name: 'app_landing_page')]
    public function index(
        ProductsRepository $productsRepository,
        CategoryRepository $categoryRepository,
        SubscriptionPlanRepository $planRepository
    ): Response {
        $products = $productsRepository->findAll();
        $categories = $categoryRepository->findAll();
        
        // Get featured products (first 6 products)
        $featuredProducts = array_slice($products, 0, 6);
        
        // Get active subscription plans (synced with Admin/Staff dashboards)
        $plans = $planRepository->findBy(['active' => true]);
        
        return $this->render('landing_page/index.html.twig', [
            'products' => $products,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'plans' => $plans,
        ]);
    }
}
