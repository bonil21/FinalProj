<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Form\CustomerType;
use App\Form\OrderType;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProductsRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        ProductsRepository $productsRepository,
        CategoryRepository $categoryRepository,
        OrderRepository $orderRepository,
        PaymentRepository $paymentRepository,
        CustomerRepository $customerRepository
    ): Response {
        $products = $productsRepository->findAll();
        $categories = $categoryRepository->findAll();
        
        // Get real-time dashboard data
        $totalProducts = count($products);
        $activeSubscriptions = $orderRepository->countActiveSubscriptions();
        $ordersToday = $orderRepository->countOrdersToday();
        $todayRevenue = $paymentRepository->getTodayRevenue();
        $recentOrders = $orderRepository->findRecentOrders(5);

        return $this->render('admin/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'totalProducts' => $totalProducts,
            'totalCategories' => count($categories),
            'activeSubscriptions' => $activeSubscriptions,
            'ordersToday' => $ordersToday,
            'todayRevenue' => $todayRevenue,
            'recentOrders' => $recentOrders,
        ]);
    }

    #[Route('/products', name: 'admin_products')]
    public function products(ProductsRepository $productsRepository): Response
    {
        $products = $productsRepository->findAll();
        return $this->render('admin/products.html.twig', [
            'products' => $products,
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
    public function orders(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAllOrderedByCreatedAt();

        return $this->render('admin/orders.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Route('/orders/new', name: 'admin_orders_new')]
    public function newOrder(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
