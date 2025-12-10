<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff')]
#[IsGranted('ROLE_STAFF')]
class StaffController extends AbstractController
{
    #[Route('/dashboard', name: 'staff_dashboard')]
    public function dashboard(
        ProductsRepository $productsRepository,
        OrderRepository $orderRepository
    ): Response {
        // Staff can only see their own products and orders
        $user = $this->getUser();
        $myProducts = $productsRepository->findBy(['createdBy' => $user]);
        $myOrders = $orderRepository->findBy(['createdBy' => $user], ['createdAt' => 'DESC']);

        return $this->render('staff/dashboard.html.twig', [
            'myProducts' => $myProducts,
            'myOrders' => $myOrders,
            'totalProducts' => count($myProducts),
            'totalOrders' => count($myOrders),
        ]);
    }
}

