<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\SubscriptionPlan;
use App\Entity\Customer;
use App\Entity\Payment;
use App\Entity\Transaction;
use App\Form\SubscriptionType;
use App\Form\SubscriptionPlanType;
use App\Repository\SubscriptionRepository;
use App\Repository\SubscriptionPlanRepository;
use App\Repository\CustomerRepository;
use App\Repository\PaymentRepository;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/subscriptions')]
#[IsGranted('ROLE_ADMIN')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubscriptionRepository $subscriptionRepository,
        private SubscriptionPlanRepository $subscriptionPlanRepository,
        private CustomerRepository $customerRepository,
        private PaymentRepository $paymentRepository,
        private TransactionRepository $transactionRepository
    ) {}

    #[Route('/', name: 'app_subscription_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(5, min(100, (int) $request->query->get('limit', 20)));
        $status = trim((string) $request->query->get('status', ''));
        $search = trim((string) $request->query->get('q', ''));

        $subscriptions = $this->subscriptionRepository->findPaginatedWithFilters($page, $limit, $status ?: null, $search ?: null);
        $totalSubscriptions = $this->subscriptionRepository->countWithFilters($status ?: null, $search ?: null);
        $totalPages = max(1, (int) ceil($totalSubscriptions / $limit));
        $statusCounts = $this->subscriptionRepository->getStatusCounts($search ?: null);

        return $this->render('admin/subscriptions/index.html.twig', [
            'subscriptions' => $subscriptions,
            'page' => $page,
            'limit' => $limit,
            'statusFilter' => $status,
            'search' => $search,
            'totalSubscriptions' => $totalSubscriptions,
            'totalPages' => $totalPages,
            'statusCounts' => $statusCounts,
        ]);
    }

    #[Route('/new', name: 'app_subscription_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $subscription = new Subscription();
        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the creator to the current admin user
            $subscription->setCreatedBy($this->getUser());
            // createdAt is already set in the entity constructor
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_subscription_index');
        }

        return $this->render('admin/subscriptions/new.html.twig', [
            'subscription' => $subscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_subscription_show', methods: ['GET'])]
    public function show(Subscription $subscription): Response
    {
        return $this->render('admin/subscriptions/show.html.twig', [
            'subscription' => $subscription,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_subscription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subscription $subscription): Response
    {
        // Only allow admin to edit subscriptions they created
        // If createdBy is null (legacy subscription), allow admin to edit
        $currentUser = $this->getUser();
        $createdBy = $subscription->getCreatedBy();
        if ($createdBy !== null && $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only edit subscriptions that you created.');
            return $this->redirectToRoute('app_subscription_index');
        }

        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_subscription_index');
        }

        return $this->render('admin/subscriptions/edit.html.twig', [
            'subscription' => $subscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}/delete', name: 'app_subscription_delete', methods: ['POST'])]
    public function delete(Request $request, Subscription $subscription): Response
    {
        // Only allow admin to delete subscriptions they created
        // If createdBy is null (legacy subscription), allow admin to delete
        $currentUser = $this->getUser();
        $createdBy = $subscription->getCreatedBy();
        if ($createdBy !== null && $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only delete subscriptions that you created.');
            return $this->redirectToRoute('app_subscription_index');
        }

        if ($this->isCsrfTokenValid('delete'.$subscription->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_subscription_index');
    }

    #[Route('/plans/', name: 'app_subscription_plan_index', methods: ['GET'])]
    public function plansIndex(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(5, min(100, (int) $request->query->get('limit', 12)));
        $active = trim((string) $request->query->get('active', ''));
        $search = trim((string) $request->query->get('q', ''));

        $plans = $this->subscriptionPlanRepository->findPaginatedWithFilters($page, $limit, $active, $search ?: null);
        $totalPlans = $this->subscriptionPlanRepository->countWithFilters($active, $search ?: null);
        $totalPages = max(1, (int) ceil($totalPlans / $limit));
        $activeCounts = $this->subscriptionPlanRepository->getActiveCounts($search ?: null);

        return $this->render('admin/subscription_plans/index.html.twig', [
            'plans' => $plans,
            'page' => $page,
            'limit' => $limit,
            'activeFilter' => $active,
            'search' => $search,
            'totalPlans' => $totalPlans,
            'totalPages' => $totalPages,
            'activeCounts' => $activeCounts,
        ]);
    }

    #[Route('/plans/new', name: 'app_subscription_plan_new', methods: ['GET', 'POST'])]
    public function planNew(Request $request): Response
    {
        $plan = new SubscriptionPlan();
        $form = $this->createForm(SubscriptionPlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the creator to the current admin user
            $plan->setCreatedBy($this->getUser());
            $this->entityManager->persist($plan);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_subscription_plan_index');
        }

        return $this->render('admin/subscription_plans/new.html.twig', [
            'plan' => $plan,
            'form' => $form,
        ]);
    }

    #[Route('/plans/{id<\d+>}/edit', name: 'app_subscription_plan_edit', methods: ['GET', 'POST'])]
    public function planEdit(Request $request, SubscriptionPlan $plan): Response
    {
        // Only allow admin to edit subscription plans they created
        // If createdBy is null (legacy plan), allow admin to edit
        $currentUser = $this->getUser();
        $createdBy = $plan->getCreatedBy();
        if ($createdBy !== null && $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only edit subscription plans that you created.');
            return $this->redirectToRoute('app_subscription_plan_index');
        }

        $form = $this->createForm(SubscriptionPlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_subscription_plan_index');
        }

        return $this->render('admin/subscription_plans/edit.html.twig', [
            'plan' => $plan,
            'form' => $form,
        ]);
    }

    #[Route('/plans/{id<\d+>}', name: 'app_subscription_plan_delete', methods: ['POST'])]
    public function planDelete(Request $request, SubscriptionPlan $plan): Response
    {
        // Only allow admin to delete subscription plans they created
        // If createdBy is null (legacy plan), allow admin to delete
        $currentUser = $this->getUser();
        $createdBy = $plan->getCreatedBy();
        if ($createdBy !== null && $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only delete subscription plans that you created.');
            return $this->redirectToRoute('app_subscription_plan_index');
        }

        if ($this->isCsrfTokenValid('delete'.$plan->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($plan);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_subscription_plan_index');
    }

    #[Route('/payments/', name: 'app_payment_index', methods: ['GET'])]
    public function paymentsIndex(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(5, min(100, (int) $request->query->get('limit', 20)));
        $status = trim((string) $request->query->get('status', ''));
        $search = trim((string) $request->query->get('q', ''));

        $payments = $this->paymentRepository->findPaginatedWithFilters($page, $limit, $status ?: null, $search ?: null);
        $totalPayments = $this->paymentRepository->countWithFilters($status ?: null, $search ?: null);
        $totalPages = max(1, (int) ceil($totalPayments / $limit));
        $statusCounts = $this->paymentRepository->getStatusCounts($search ?: null);

        return $this->render('admin/payments/index.html.twig', [
            'payments' => $payments,
            'page' => $page,
            'limit' => $limit,
            'statusFilter' => $status,
            'search' => $search,
            'totalPayments' => $totalPayments,
            'totalPages' => $totalPages,
            'statusCounts' => $statusCounts,
        ]);
    }

    #[Route('/transactions/', name: 'app_transaction_index', methods: ['GET'])]
    public function transactionsIndex(Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(5, min(100, (int) $request->query->get('limit', 20)));
        $status = trim((string) $request->query->get('status', ''));
        $search = trim((string) $request->query->get('q', ''));

        $transactions = $this->transactionRepository->findPaginatedWithFilters($page, $limit, $status ?: null, $search ?: null);
        $totalTransactions = $this->transactionRepository->countWithFilters($status ?: null, $search ?: null);
        $totalPages = max(1, (int) ceil($totalTransactions / $limit));
        $statusCounts = $this->transactionRepository->getStatusCounts($search ?: null);

        return $this->render('admin/transactions/index.html.twig', [
            'transactions' => $transactions,
            'page' => $page,
            'limit' => $limit,
            'statusFilter' => $status,
            'search' => $search,
            'totalTransactions' => $totalTransactions,
            'totalPages' => $totalPages,
            'statusCounts' => $statusCounts,
        ]);
    }

    #[Route('/payments/{id<\d+>}', name: 'app_payment_show', methods: ['GET'])]
    public function paymentShow(Payment $payment): Response
    {
        return $this->render('admin/payments/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/transactions/{id<\d+>}', name: 'app_transaction_show', methods: ['GET'])]
    public function transactionShow(Transaction $transaction): Response
    {
        return $this->render('admin/transactions/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }
}
