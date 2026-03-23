<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Feedback;
use App\Entity\Order;
use App\Form\CustomerType;
use App\Form\OrderType;
use App\Repository\ActivityLogRepository;
use App\Repository\CustomerRepository;
use App\Repository\CategoryRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Repository\FeedbackRepository;
use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        ProductsRepository $productsRepository,
        OrderRepository $orderRepository,
        PaymentRepository $paymentRepository,
        UserRepository $userRepository,
        ActivityLogRepository $activityLogRepository
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You are not allowed to access the admin dashboard.');
        }
        // Get real-time dashboard data
        $totalProducts = $productsRepository->count([]);
        $totalUsers = $userRepository->count([]);
        $totalStaff = $userRepository->countByRole('ROLE_STAFF');
        $totalAdmins = $userRepository->countByRole('ROLE_ADMIN');
        $activeSubscriptions = $orderRepository->countActiveSubscriptions();
        $ordersToday = $orderRepository->countOrdersToday();
        $todayRevenue = $paymentRepository->getTodayRevenue();
        $recentOrders = $orderRepository->findRecentOrders(5);
        $recentLogs = $activityLogRepository->findBy([], ['createdAt' => 'DESC'], 10);
        $products = $productsRepository->findFeatured(6);

        return $this->render('admin/index.html.twig', [
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'totalStaff' => $totalStaff,
            'totalAdmins' => $totalAdmins,
            'activeSubscriptions' => $activeSubscriptions,
            'ordersToday' => $ordersToday,
            'todayRevenue' => $todayRevenue,
            'recentOrders' => $recentOrders,
            'recentLogs' => $recentLogs,
            'products' => $products,
        ]);
    }

    #[Route('/products', name: 'admin_products')]
    public function products(Request $request, ProductsRepository $productsRepository, CategoryRepository $categoryRepository): Response
    {
        $search = $request->query->get('search', '');
        $categoryFilter = $request->query->get('category', '');

        $categoryId = $categoryFilter !== '' ? (int) $categoryFilter : null;
        $products = $productsRepository->findWithFilters($search, $categoryId);
        
        $categories = $categoryRepository->findBy([], ['name' => 'ASC']);
        
        return $this->render('admin/products.html.twig', [
            'products' => $products,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'categories' => $categories,
        ]);
    }



    #[Route('/customers', name: 'admin_customers')]
    public function customers(CustomerRepository $customerRepository): Response
    {
        $customers = $customerRepository->findAllOrderedByCreatedAt();

        return $this->render('admin/customers.html.twig', [
            'customers' => $customers,
        ]);
    }

    #[Route('/customers/new', name: 'admin_customers_new')]
    public function newCustomer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $customer->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($customer);
                $entityManager->flush();

                return $this->redirectToRoute('admin_customers');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Email already exists. Please use a different email.');
            }
        }

        return $this->render('admin/customers/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/customers/{id}/edit', name: 'admin_customers_edit')]
    public function editCustomer(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_customers');
        }

        return $this->render('admin/customers/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/customers/{id}', name: 'admin_customers_show')]
    public function showCustomer(Customer $customer): Response
    {
        return $this->render('admin/customers/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/customers/{id}/delete', name: 'admin_customers_delete', methods: ['POST'])]
    public function deleteCustomer(Request $request, Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->request->get('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_customers');
    }

    #[Route('/orders', name: 'admin_orders')]
    public function orders(Request $request, OrderRepository $orderRepository, PaymentRepository $paymentRepository, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $statusFilter = $request->query->get('status', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');
        
        $orders = $orderRepository->findAllOrderedByCreatedAt();

        // Reconcile legacy/inconsistent records:
        // if an order has a completed GCash/ATM payment, ensure it is marked as paid.
        $completedOnlineOrderIds = array_flip($paymentRepository->findCompletedOnlineOrderIds());
        $hasOrderStatusUpdates = false;
        foreach ($orders as $order) {
            $orderId = $order->getId();
            if ($orderId !== null && isset($completedOnlineOrderIds[$orderId]) && $order->getStatus() !== 'paid') {
                $order->setStatus('paid');
                $order->setUpdatedAt(new \DateTimeImmutable());
                $hasOrderStatusUpdates = true;
            }
        }
        if ($hasOrderStatusUpdates) {
            $entityManager->flush();
        }
        
        $fromDate = $dateFrom ? new \DateTimeImmutable($dateFrom) : null;
        $toDate = $dateTo ? new \DateTimeImmutable($dateTo . ' 23:59:59') : null;
        $orders = $orderRepository->findWithFilters($search, $statusFilter ?: null, $fromDate, $toDate);
        $statuses = $orderRepository->findDistinctStatuses();
        
        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statuses' => $statuses,
        ]);
    }

    #[Route('/orders/new', name: 'admin_orders_new')]
    public function newOrder(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the creator to the current admin user
            $order->setCreatedBy($this->getUser());
            $order->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('admin_orders');
        }

        return $this->render('admin/orders/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/orders/{id}/edit', name: 'admin_orders_edit')]
    public function editOrder(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        // Admin can edit all orders (including those with null createdBy or created by staff)
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('admin_orders');
        }

        return $this->render('admin/orders/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/orders/{id}', name: 'admin_orders_show')]
    public function showOrder(Order $order): Response
    {
        return $this->render('admin/orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/orders/{id}/delete', name: 'admin_orders_delete', methods: ['POST'])]
    public function deleteOrder(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        // Admin can delete all orders (including those with null createdBy or created by staff)
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_orders');
    }

    #[Route('/payments', name: 'admin_payments')]
    public function payments(): Response
    {
        return $this->redirectToRoute('app_payment_index');
    }

    #[Route('/feedback', name: 'admin_feedback')]
    public function feedback(FeedbackRepository $feedbackRepository): Response
    {
        $feedbacks = $feedbackRepository->findAllOrderedByCreatedAt();

        return $this->render('admin/feedback.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/feedback/{id}/delete', name: 'admin_feedback_delete', methods: ['POST'])]
    public function deleteFeedback(Request $request, Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_feedback_'.$feedback->getId(), $request->request->getString('_token'))) {
            $entityManager->remove($feedback);
            $entityManager->flush();
            $this->addFlash('success', 'Feedback deleted.');
        }

        return $this->redirectToRoute('admin_feedback');
    }

    #[Route('/promotions', name: 'admin_promotions')]
    public function promotions(): Response
    {
        // Promotions feature has been removed; keep route as safe fallback.
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
}
