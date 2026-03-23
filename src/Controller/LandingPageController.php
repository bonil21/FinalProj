<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use App\Repository\SubscriptionPlanRepository;
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
        SubscriptionPlanRepository $planRepository
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($this->isGranted('ROLE_STAFF')) {
            return $this->redirectToRoute('staff_dashboard');
        }

        $featuredProducts = $productsRepository->findFeatured(6);
        
        // Get active subscription plans (synced with Admin/Staff dashboards)
        $plans = $planRepository->findActivePlans();
        
        return $this->render('landing_page/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'plans' => $plans,
        ]);
    }
}
