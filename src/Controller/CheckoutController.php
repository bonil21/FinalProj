<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Repository\CustomerRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private ProductsRepository $productsRepository,
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    #[Route('', name: 'app_checkout', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->getSession()->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('error', 'Your cart is empty. Please add items before checkout.');
            return $this->redirectToRoute('app_cart');
        }

        $items = [];
        $total = 0;

        foreach ($cart as $item) {
            if ($item['type'] === 'product') {
                $product = $this->productsRepository->find($item['id']);
                if ($product) {
                    $quantity = $item['quantity'] ?? 1;
                    $items[] = [
                        'type' => 'product',
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'price' => $product->getPrice(),
                        'quantity' => $quantity,
                    ];
                    $total += $product->getPrice() * $quantity;
                }
            }
        }

        if (empty($items)) {
            $this->addFlash('error', 'Your cart is empty. Please add items before checkout.');
            return $this->redirectToRoute('app_cart');
        }

        // Get or create customer
        $customer = $this->getOrCreateCustomer();

        return $this->render('checkout/index.html.twig', [
            'items' => $items,
            'total' => $total,
            'customer' => $customer,
        ]);
    }

    #[Route('/create-order', name: 'app_checkout_create_order', methods: ['POST'])]
    public function createOrder(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $this->getSession()->get('cart', []);
        
        if (empty($cart)) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        $deliveryAddress = $request->request->get('delivery_address');
        
        if (empty($deliveryAddress)) {
            $this->addFlash('error', 'Please provide a delivery address.');
            return $this->redirectToRoute('app_checkout');
        }

        $items = [];
        $total = 0;

        foreach ($cart as $item) {
            if ($item['type'] === 'product') {
                $product = $this->productsRepository->find($item['id']);
                if ($product) {
                    $quantity = $item['quantity'] ?? 1;
                    $items[] = [
                        'type' => 'product',
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'price' => $product->getPrice(),
                        'quantity' => $quantity,
                    ];
                    $total += $product->getPrice() * $quantity;
                }
            }
        }

        if (empty($items)) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart');
        }

        // Get or create customer
        $customer = $this->getOrCreateCustomer();

        // Determine order creator based on product creators
        $productCreators = [];
        foreach ($items as $item) {
            if ($item['type'] === 'product') {
                $product = $this->productsRepository->find($item['id']);
                if ($product && $product->getCreatedBy()) {
                    $creator = $product->getCreatedBy();
                    // Only count staff members (not admins) as creators
                    if (in_array('ROLE_STAFF', $creator->getRoles())) {
                        $creatorId = $creator->getId();
                        if (!isset($productCreators[$creatorId])) {
                            $productCreators[$creatorId] = [
                                'user' => $creator,
                                'count' => 0
                            ];
                        }
                        $productCreators[$creatorId]['count'] += ($item['quantity'] ?? 1);
                    }
                }
            }
        }

        // Set order creator: use the staff member with the most products, or first one found
        $orderCreator = null;
        if (!empty($productCreators)) {
            // Sort by count (descending) to get the staff member with most products
            uasort($productCreators, function($a, $b) {
                return $b['count'] <=> $a['count'];
            });
            // Get the first (highest count) staff member
            $orderCreator = reset($productCreators)['user'];
        }

        // Create order
        $order = new Order();
        $order->setOrderNumber('ORD-' . strtoupper(uniqid()));
        $order->setTotalAmount($total);
        $order->setStatus('pending');
        $order->setDeliveryAddress($deliveryAddress);
        $order->setCustomer($customer);
        $order->setCreatedAt(new \DateTimeImmutable());
        
        // Set the creator if we found a staff member who created products in this order
        if ($orderCreator) {
            $order->setCreatedBy($orderCreator);
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Store order ID in session for payment
        $this->getSession()->set('pending_order_id', $order->getId());

        // Clear cart
        $this->getSession()->set('cart', []);

        // Redirect to payment page
        return $this->redirectToRoute('app_payment', ['orderId' => $order->getId()]);
    }

    private function getOrCreateCustomer(): Customer
    {
        $user = $this->getUser();
        $email = method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier();
        
        if (empty($email)) {
            $email = $user->getUserIdentifier();
        }
        
        $name = null;
        if (method_exists($user, 'getName')) {
            $name = $user->getName();
        }
        if (empty($name) || trim($name) === '') {
            $name = !empty($email) ? $email : 'Customer';
        }

        $customer = $this->customerRepository->findOneBy(['email' => $email]);
        if (!$customer) {
            $customer = new Customer();
            $customer->setEmail($email);
            $customer->setName($name);
            $customer->setPhone('N/A');
            $customer->setCreatedAt(new \DateTimeImmutable());
            $customer->setPassword(bin2hex(random_bytes(16)));
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        } else {
            if (empty($customer->getName()) || $customer->getName() === null) {
                $customer->setName($name);
                $this->entityManager->flush();
            }
        }

        return $customer;
    }
}

