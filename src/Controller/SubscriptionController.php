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

#[Route('/admin/subscriptions')]
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
    public function index(): Response
    {
        $subscriptions = $this->subscriptionRepository->findAll();

        return $this->render('admin/subscriptions/index.html.twig', [
            'subscriptions' => $subscriptions,
        ]);
    }

    #[Route('/new', name: 'app_subscription_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $subscription = new Subscription();
        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        if ($this->isCsrfTokenValid('delete'.$subscription->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_subscription_index');
    }

    #[Route('/plans/', name: 'app_subscription_plan_index', methods: ['GET'])]
    public function plansIndex(): Response
    {
        $plans = $this->subscriptionPlanRepository->findAll();

        return $this->render('admin/subscription_plans/index.html.twig', [
            'plans' => $plans,
        ]);
    }

    #[Route('/plans/new', name: 'app_subscription_plan_new', methods: ['GET', 'POST'])]
    public function planNew(Request $request): Response
    {
        $plan = new SubscriptionPlan();
        $form = $this->createForm(SubscriptionPlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        if ($this->isCsrfTokenValid('delete'.$plan->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($plan);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_subscription_plan_index');
    }

    #[Route('/payments/', name: 'app_payment_index', methods: ['GET'])]
    public function paymentsIndex(): Response
    {
        $payments = $this->paymentRepository->findAll();

        return $this->render('admin/payments/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route('/transactions/', name: 'app_transaction_index', methods: ['GET'])]
    public function transactionsIndex(): Response
    {
        $transactions = $this->transactionRepository->findAll();

        return $this->render('admin/transactions/index.html.twig', [
            'transactions' => $transactions,
        ]);
    }
}
