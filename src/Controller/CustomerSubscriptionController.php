<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Subscription;
use App\Entity\SubscriptionPlan;
use App\Repository\CustomerRepository;
use App\Repository\ProductsRepository;
use App\Repository\SubscriptionPlanRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/subscriptions')]
#[IsGranted('ROLE_USER')]
class CustomerSubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SubscriptionPlanRepository $planRepository,
        private SubscriptionRepository $subscriptionRepository,
        private CustomerRepository $customerRepository,
        private ProductsRepository $productsRepository,
    ) {
    }

    #[Route('', name: 'customer_subscriptions', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $customer = $this->getOrCreateCustomerForUser();
        $plans = $this->planRepository->findBy(['active' => true]);
        $subscriptions = $this->subscriptionRepository->findBy(['customer' => $customer]);
        $selectableProducts = $this->productsRepository->findAvailableForSubscription();

        return $this->render('subscriptions/index.html.twig', [
            'plans' => $plans,
            'subscriptions' => $subscriptions,
            'selectableProducts' => $selectableProducts,
        ]);
    }

    #[Route('/subscribe/{id}', name: 'customer_subscribe', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function subscribe(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $token = $request->request->getString('_token');
        $validToken = $this->isCsrfTokenValid('subscribe_'.$plan->getId(), $token)
            || $this->isCsrfTokenValid('subscribe'.$plan->getId(), $token);
        if (!$validToken) {
            $this->addFlash('error', 'Invalid security token. Please try again.');

            return $this->redirectToRoute('customer_subscriptions');
        }

        if (!$plan->isActive()) {
            $this->addFlash('error', 'This subscription plan is not available.');
            return $this->redirectToRoute('customer_subscriptions');
        }

        $customer = $this->getOrCreateCustomerForUser();

        $existing = $this->subscriptionRepository->findOneBy([
            'customer' => $customer,
            'plan' => $plan,
            'status' => 'active',
        ]);

        if ($existing) {
            $this->addFlash('info', 'You already have an active subscription for this plan. You can manage it below.');
            return $this->redirectToRoute('customer_subscriptions');
        }

        $requiredMeals = max(0, (int) ($plan->getMealsIncluded() ?? 0));
        $availableProducts = $this->productsRepository->findAvailableForSubscription();
        $availableById = [];
        foreach ($availableProducts as $availableProduct) {
            $productId = $availableProduct->getId();
            if ($productId !== null) {
                $availableById[$productId] = $availableProduct;
            }
        }

        if ($requiredMeals > 0 && empty($availableById)) {
            $this->addFlash('error', 'No meals are currently available for subscription selection. Please try again later.');

            return $this->redirectToRoute('customer_subscriptions');
        }

        $selectedMeals = [];
        $selectedMealQty = $request->request->all('meal_qty');
        $selectedTotal = 0;
        foreach ($selectedMealQty as $productIdRaw => $quantityRaw) {
            $productId = (int) $productIdRaw;
            $quantity = (int) $quantityRaw;
            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            if (!isset($availableById[$productId])) {
                $this->addFlash('error', 'One or more selected meals are no longer available. Please review your selection.');

                return $this->redirectToRoute('customer_subscriptions');
            }

            $product = $availableById[$productId];
            $selectedMeals[] = [
                'productId' => $productId,
                'name' => (string) $product->getName(),
                'quantity' => $quantity,
            ];
            $selectedTotal += $quantity;
        }

        if ($selectedTotal !== $requiredMeals) {
            $this->addFlash(
                'error',
                sprintf(
                    'Please select exactly %d meals for the %s plan. You selected %d.',
                    $requiredMeals,
                    (string) $plan->getName(),
                    $selectedTotal
                )
            );

            return $this->redirectToRoute('customer_subscriptions');
        }

        if ($requiredMeals > 0 && count($selectedMeals) === 0) {
            $this->addFlash('error', 'Please choose your meals before continuing.');

            return $this->redirectToRoute('customer_subscriptions');
        }

        $subscription = new Subscription();
        $subscription->setCustomer($customer);
        $subscription->setPlan($plan);
        $subscription->setStatus('pending'); // Set to pending until payment is completed
        $subscription->setStartDate(new \DateTime());
        $subscription->setCurrentPeriodStart(new \DateTime());
        $subscription->setCurrentPeriodEnd($this->calculatePeriodEnd($plan->getBillingInterval()));
        $subscription->setCancelAtPeriodEnd(false);
        $subscription->setSelectedMeals($selectedMeals);

        $this->em->persist($subscription);
        $this->em->flush();

        // Redirect to payment page instead of directly activating
        return $this->redirectToRoute('app_payment_subscription', ['subscriptionId' => $subscription->getId()]);
    }

    private function getOrCreateCustomerForUser(): Customer
    {
        $user = $this->getUser();
        $email = method_exists($user, 'getEmail') ? $user->getEmail() : $user->getUserIdentifier();
        
        // Ensure email is not null
        if (empty($email)) {
            $email = $user->getUserIdentifier();
        }
        
        // Get user's name if available, otherwise use email
        $name = null;
        if (method_exists($user, 'getName')) {
            $name = $user->getName();
        }
        // Fallback to email or default value
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
            // Set a default password (random string, since customer logs in via user account)
            $customer->setPassword(bin2hex(random_bytes(16)));
            $this->em->persist($customer);
            $this->em->flush();
        } else {
            // Update name if it's missing or null
            if (empty($customer->getName()) || $customer->getName() === null) {
                $customer->setName($name);
                $this->em->flush();
            }
        }

        return $customer;
    }

    private function calculatePeriodEnd(?string $billingInterval): \DateTime
    {
        $start = new \DateTime();
        return match ($billingInterval) {
            'weekly' => (clone $start)->modify('+7 days'),
            'monthly' => (clone $start)->modify('+1 month'),
            default => (clone $start)->modify('+1 month'),
        };
    }
}

