<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Form\CustomerType;
use App\Form\OrderType;
use App\Repository\ActivityLogRepository;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProductsRepository;
use App\Repository\CategoryRepository;
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
        CategoryRepository $categoryRepository,
        OrderRepository $orderRepository,
        PaymentRepository $paymentRepository,
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        ActivityLogRepository $activityLogRepository
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You are not allowed to access the admin dashboard.');
        }
        $products = $productsRepository->findAll();
        $categories = $categoryRepository->findAll();
        // Get real-time dashboard data
        $totalProducts = count($products);
        $totalUsers = $userRepository->count([]);
        $allUsers = $userRepository->findAll();
        $totalStaff = count(array_filter($allUsers, fn($u) => in_array('ROLE_STAFF', $u->getRoles())));
        $totalAdmins = count(array_filter($allUsers, fn($u) => in_array('ROLE_ADMIN', $u->getRoles())));
        $activeSubscriptions = $orderRepository->countActiveSubscriptions();
        $ordersToday = $orderRepository->countOrdersToday();
        $todayRevenue = $paymentRepository->getTodayRevenue();
        $recentOrders = $orderRepository->findRecentOrders(5);
        $recentLogs = $activityLogRepository->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('admin/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'totalProducts' => $totalProducts,
            'totalCategories' => count($categories),
            'totalUsers' => $totalUsers,
            'totalStaff' => $totalStaff,
            'totalAdmins' => $totalAdmins,
            'activeSubscriptions' => $activeSubscriptions,
            'ordersToday' => $ordersToday,
            'todayRevenue' => $todayRevenue,
            'recentOrders' => $recentOrders,
            'recentLogs' => $recentLogs,
        ]);
    }

    #[Route('/products', name: 'admin_products')]
    public function products(Request $request, ProductsRepository $productsRepository): Response
    {
        $search = $request->query->get('search', '');
        $categoryFilter = $request->query->get('category', '');
        
        $products = $productsRepository->findAll();
        
        // Apply filters
        if ($search) {
            $products = array_filter($products, function($product) use ($search) {
                return stripos($product->getName(), $search) !== false 
                    || stripos($product->getDescription(), $search) !== false;
            });
        }
        
        if ($categoryFilter) {
            $products = array_filter($products, function($product) use ($categoryFilter) {
                return $product->getCategory() === $categoryFilter;
            });
        }
        
        // Get unique categories for filter dropdown
        $allProducts = $productsRepository->findAll();
        $categories = array_unique(array_map(fn($p) => $p->getCategory(), $allProducts));
        sort($categories);
        
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
    public function orders(Request $request, OrderRepository $orderRepository): Response
    {
        $search = $request->query->get('search', '');
        $statusFilter = $request->query->get('status', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');
        
        $orders = $orderRepository->findAllOrderedByCreatedAt();
        
        // Apply filters
        if ($search) {
            $orders = array_filter($orders, function($order) use ($search) {
                return stripos($order->getOrderNumber(), $search) !== false 
                    || ($order->getCustomer() && stripos($order->getCustomer()->getName(), $search) !== false)
                    || ($order->getCustomer() && stripos($order->getCustomer()->getEmail(), $search) !== false);
            });
        }
        
        if ($statusFilter) {
            $orders = array_filter($orders, function($order) use ($statusFilter) {
                return $order->getStatus() === $statusFilter;
            });
        }
        
        if ($dateFrom) {
            $dateFromObj = new \DateTimeImmutable($dateFrom);
            $orders = array_filter($orders, function($order) use ($dateFromObj) {
                return $order->getCreatedAt() && $order->getCreatedAt() >= $dateFromObj;
            });
        }
        
        if ($dateTo) {
            $dateToObj = new \DateTimeImmutable($dateTo . ' 23:59:59');
            $orders = array_filter($orders, function($order) use ($dateToObj) {
                return $order->getCreatedAt() && $order->getCreatedAt() <= $dateToObj;
            });
        }
        
        // Get unique statuses for filter dropdown
        $allOrders = $orderRepository->findAllOrderedByCreatedAt();
        $statuses = array_unique(array_map(fn($o) => $o->getStatus(), $allOrders));
        sort($statuses);
        
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
        return $this->render('admin/payments.html.twig');
    }

    #[Route('/feedback', name: 'admin_feedback')]
    public function feedback(): Response
    {
        return $this->render('admin/feedback.html.twig');
    }

    #[Route('/promotions', name: 'admin_promotions')]
    public function promotions(): Response
    {
        return $this->render('admin/promotions.html.twig');
    }

    #[Route('/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
}
