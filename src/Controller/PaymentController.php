<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\Transaction;
use App\Entity\Subscription;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
#[IsGranted('ROLE_USER')]
class PaymentController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private OrderRepository $orderRepository,
        private SubscriptionRepository $subscriptionRepository,
        private CustomerRepository $customerRepository,
        private PaymentRepository $paymentRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    #[Route('/order/{orderId}', name: 'app_payment', methods: ['GET'])]
    public function index(int $orderId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $order = $this->orderRepository->find($orderId);
        
        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('app_cart');
        }

        // Verify order belongs to current user
        $customer = $this->getOrCreateCustomer();
        if ($order->getCustomer() !== $customer) {
            $this->addFlash('error', 'You do not have permission to view this order.');
            return $this->redirectToRoute('app_cart');
        }

        return $this->render('payment/index.html.twig', [
            'order' => $order,
            'type' => 'order',
        ]);
    }

    #[Route('/subscription/{subscriptionId}', name: 'app_payment_subscription', methods: ['GET'])]
    public function subscriptionPayment(int $subscriptionId): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $subscription = $this->subscriptionRepository->find($subscriptionId);
        
        if (!$subscription) {
            $this->addFlash('error', 'Subscription not found.');
            return $this->redirectToRoute('customer_subscriptions');
        }

        // Verify subscription belongs to current user
        $customer = $this->getOrCreateCustomer();
        if ($subscription->getCustomer() !== $customer) {
            $this->addFlash('error', 'You do not have permission to view this subscription.');
            return $this->redirectToRoute('customer_subscriptions');
        }

        $plan = $subscription->getPlan();
        $amount = $plan ? (float)$plan->getPrice() : 0;

        return $this->render('payment/index.html.twig', [
            'subscription' => $subscription,
            'amount' => $amount,
            'type' => 'subscription',
        ]);
    }

    #[Route('/process', name: 'app_payment_process', methods: ['POST'])]
    public function process(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('payment_process', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            if ($request->request->get('subscription_id')) {
                return $this->redirectToRoute('customer_subscriptions');
            }

            return $this->redirectToRoute('app_cart');
        }

        $paymentMethod = $request->request->get('payment_method');
        $isCashOnDelivery = $paymentMethod === 'cash';
        $isOnlinePayment = in_array($paymentMethod, ['gcash', 'atm'], true);
        $orderId = $request->request->get('order_id');
        $subscriptionId = $request->request->get('subscription_id');

        if (!in_array($paymentMethod, ['cash', 'gcash', 'atm'])) {
            $this->addFlash('error', 'Invalid payment method selected.');
            return $this->redirectToRoute('app_cart');
        }

        $customer = $this->getOrCreateCustomer();

        if ($orderId) {
            // Process order payment
            $order = $this->orderRepository->find($orderId);
            
            if (!$order || $order->getCustomer() !== $customer) {
                $this->addFlash('error', 'Order not found or access denied.');
                return $this->redirectToRoute('app_cart');
            }

            // Create payment
            $payment = new Payment();
            $payment->setCustomer($customer);
            $payment->setOrder($order);
            $payment->setAmount((string)$order->getTotalAmount());
            $payment->setCurrency('PHP');
            $payment->setPaymentMethod($paymentMethod);
            $payment->setStatus($isCashOnDelivery ? 'pending' : 'completed');
            $payment->setDescription('Payment for Order #' . $order->getOrderNumber());
            $payment->setCreatedAt(new \DateTimeImmutable());
            $payment->setPaidAt($isCashOnDelivery ? null : new \DateTimeImmutable());

            $this->entityManager->persist($payment);

            // Create transaction record
            $transaction = new Transaction();
            $transaction->setCustomer($customer);
            $transaction->setPayment($payment);
            $transaction->setType('payment');
            $transaction->setAmount((string)$order->getTotalAmount());
            $transaction->setCurrency('PHP');
            $transaction->setStatus($isCashOnDelivery ? 'pending' : 'completed');
            $transaction->setReference($order->getOrderNumber());
            $transaction->setDescription(sprintf(
                'Order #%s via %s',
                $order->getOrderNumber(),
                strtoupper($paymentMethod)
            ));
            $transaction->setCreatedAt(new \DateTimeImmutable());
            $transaction->setProcessedAt($isCashOnDelivery ? null : new \DateTimeImmutable());
            $transaction->setMetadata([
                'orderId' => $order->getId(),
                'paymentMethod' => $paymentMethod,
            ]);

            $this->entityManager->persist($transaction);

            // Update order status
            if ($isOnlinePayment) {
                $order->setStatus('paid');
            } else {
                $order->setStatus('pending');
            }
            $order->setUpdatedAt(new \DateTimeImmutable());

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $isCashOnDelivery
                    ? 'Order placed with Cash on Delivery. Please pay upon delivery.'
                    : 'Payment successful! Your order has been confirmed.'
            );
            return $this->redirectToRoute('app_order_success', ['id' => $order->getId()]);

        } elseif ($subscriptionId) {
            // Process subscription payment
            $subscription = $this->subscriptionRepository->find($subscriptionId);
            
            if (!$subscription || $subscription->getCustomer() !== $customer) {
                $this->addFlash('error', 'Subscription not found or access denied.');
                return $this->redirectToRoute('customer_subscriptions');
            }

            $plan = $subscription->getPlan();
            $amount = $plan ? (float)$plan->getPrice() : 0;

            // Create payment
            $payment = new Payment();
            $payment->setCustomer($customer);
            $payment->setSubscription($subscription);
            $payment->setAmount((string)$amount);
            $payment->setCurrency('PHP');
            $payment->setPaymentMethod($paymentMethod);
            $payment->setStatus($isCashOnDelivery ? 'pending' : 'completed');
            $payment->setDescription('Payment for Subscription: ' . ($plan ? $plan->getName() : 'N/A'));
            $payment->setCreatedAt(new \DateTimeImmutable());
            $payment->setPaidAt($isCashOnDelivery ? null : new \DateTimeImmutable());

            $this->entityManager->persist($payment);

            // Create transaction record
            $transaction = new Transaction();
            $transaction->setCustomer($customer);
            $transaction->setPayment($payment);
            $transaction->setSubscription($subscription);
            $transaction->setType('subscription');
            $transaction->setAmount((string)$amount);
            $transaction->setCurrency('PHP');
            $transaction->setStatus($isCashOnDelivery ? 'pending' : 'completed');
            $transaction->setReference($subscription->getId() ? 'SUB-' . $subscription->getId() : null);
            $transaction->setDescription(sprintf(
                'Subscription payment via %s',
                strtoupper($paymentMethod)
            ));
            $transaction->setCreatedAt(new \DateTimeImmutable());
            $transaction->setProcessedAt($isCashOnDelivery ? null : new \DateTimeImmutable());
            $transaction->setMetadata([
                'subscriptionId' => $subscription->getId(),
                'paymentMethod' => $paymentMethod,
            ]);

            $this->entityManager->persist($transaction);

            // Update subscription status
            if ($isOnlinePayment) {
                $subscription->setStatus('active');
            } else {
                $subscription->setStatus('pending');
            }

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $isCashOnDelivery
                    ? 'Subscription recorded. Please complete payment upon delivery/collection.'
                    : 'Payment successful! Your subscription has been activated.'
            );
            return $this->redirectToRoute('customer_subscriptions');
        }

        $this->addFlash('error', 'Invalid payment request.');
        return $this->redirectToRoute('app_cart');
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

