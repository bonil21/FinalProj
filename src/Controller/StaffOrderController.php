<?php

namespace App\Controller;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff/orders')]
#[IsGranted('ROLE_STAFF')]
class StaffOrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderRepository $orderRepository,
        private ActivityLogger $activityLogger
    ) {}

    #[Route('/', name: 'staff_orders_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Staff can see all orders (shared with admin)
        $search = $request->query->get('search', '');
        $statusFilter = $request->query->get('status', '');
        $dateFrom = $request->query->get('date_from', '');
        $dateTo = $request->query->get('date_to', '');
        
        $orders = $this->orderRepository->findAllOrderedByCreatedAt();
        
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
        $allOrders = $this->orderRepository->findAllOrderedByCreatedAt();
        $statuses = array_unique(array_map(fn($o) => $o->getStatus(), $allOrders));
        sort($statuses);
        
        return $this->render('staff/orders/index.html.twig', [
            'orders' => $orders,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'statuses' => $statuses,
            'active_menu' => 'orders',
        ]);
    }

    #[Route('/new', name: 'staff_orders_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Staff must assign a customer to create an order on their behalf
            if ($order->getCustomer() === null) {
                $this->addFlash('error', 'Please select a customer for this order.');
                return $this->render('staff/orders/new.html.twig', [
                    'order' => $order,
                    'form' => $form,
                    'active_menu' => 'orders',
                ]);
            }

            // Generate an order number if not provided
            if (empty($order->getOrderNumber())) {
                $order->setOrderNumber('ORD-' . strtoupper(uniqid()));
            }

            // Default status to pending when not set
            if (empty($order->getStatus())) {
                $order->setStatus('pending');
            }

            // Set the creator to the current staff user
            $order->setCreatedBy($this->getUser());
            $order->setCreatedAt(new \DateTimeImmutable());
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'CREATE', 'Order', (string)$order->getId(), ['orderNumber' => $order->getOrderNumber()]);
            $this->addFlash('success', 'Order created successfully!');

            return $this->redirectToRoute('staff_orders_index');
        }

        return $this->render('staff/orders/new.html.twig', [
            'order' => $order,
            'form' => $form,
            'active_menu' => 'orders',
        ]);
    }

    #[Route('/{id}', name: 'staff_orders_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('staff/orders/show.html.twig', [
            'order' => $order,
            'active_menu' => 'orders',
        ]);
    }

    #[Route('/{id}/edit', name: 'staff_orders_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order): Response
    {
        // Only allow staff to edit orders they created
        // If createdBy is null, deny access (legacy orders created by admin)
        $currentUser = $this->getUser();
        $createdBy = $order->getCreatedBy();
        if ($createdBy === null || $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only edit orders that you created.');
            return $this->redirectToRoute('staff_orders_index');
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'UPDATE', 'Order', (string)$order->getId(), ['orderNumber' => $order->getOrderNumber()]);
            $this->addFlash('success', 'Order updated successfully!');

            return $this->redirectToRoute('staff_orders_index');
        }

        return $this->render('staff/orders/edit.html.twig', [
            'order' => $order,
            'form' => $form,
            'active_menu' => 'orders',
        ]);
    }

    #[Route('/{id}/delete', name: 'staff_orders_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order): Response
    {
        // Only allow staff to delete orders they created
        // If createdBy is null, deny access (legacy orders created by admin)
        $currentUser = $this->getUser();
        $createdBy = $order->getCreatedBy();
        if ($createdBy === null || $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only delete orders that you created.');
            return $this->redirectToRoute('staff_orders_index');
        }

        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $orderId = $order->getId();
            $orderNumber = $order->getOrderNumber();
            
            $this->entityManager->remove($order);
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'DELETE', 'Order', (string)$orderId, ['orderNumber' => $orderNumber]);
            $this->addFlash('success', 'Order deleted successfully!');
        }

        return $this->redirectToRoute('staff_orders_index');
    }
}

