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
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Authentication required.');
        }

        $roles = $user->getRoles();
        $isAdmin = in_array('ROLE_ADMIN', $roles, true);
        $isStaff = in_array('ROLE_STAFF', $roles, true);

        // Enforce dashboard separation: admins use /admin, staff use /staff.
        if ($isAdmin || !$isStaff) {
            throw $this->createAccessDeniedException('You are not allowed to access the staff dashboard.');
        }

        // Staff can only see their own products and orders
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

