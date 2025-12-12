<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Entity\SubscriptionPlan;
use App\Form\SubscriptionType;
use App\Form\SubscriptionPlanType;
use App\Repository\SubscriptionRepository;
use App\Repository\SubscriptionPlanRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Staff Subscription Management Controller
 * 
 * NOTE: This controller uses the SAME database entities and repositories as the Admin controller.
 * Any changes made here (create, edit, delete) are immediately synced and visible in:
 * - Admin Dashboard (/admin/subscriptions)
 * - Landing Page (only active plans are displayed)
 * 
 * All subscription plans, subscriptions, bundles, and seasonal products are stored in
 * a shared database, ensuring real-time synchronization across all dashboards.
 */
#[Route('/staff/subscriptions')]
#[IsGranted('ROLE_STAFF')]
class StaffSubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubscriptionRepository $subscriptionRepository,
        private SubscriptionPlanRepository $subscriptionPlanRepository,
        private ActivityLogger $activityLogger
    ) {}

    #[Route('/', name: 'staff_subscription_index', methods: ['GET'])]
    public function index(): Response
    {
        // Staff can see all subscriptions
        $subscriptions = $this->subscriptionRepository->findAll();

        return $this->render('staff/subscriptions/index.html.twig', [
            'subscriptions' => $subscriptions,
            'active_menu' => 'subscriptions',
        ]);
    }

    #[Route('/new', name: 'staff_subscription_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $subscription = new Subscription();
        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the creator to the current staff user
            $subscription->setCreatedBy($this->getUser());
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'CREATE', 'Subscription', (string)$subscription->getId(), ['customer' => $subscription->getCustomer()?->getEmail()]);
            $this->addFlash('success', 'Subscription created successfully!');

            return $this->redirectToRoute('staff_subscription_index');
        }

        return $this->render('staff/subscriptions/new.html.twig', [
            'subscription' => $subscription,
            'form' => $form,
            'active_menu' => 'subscriptions',
        ]);
    }

    #[Route('/{id<\d+>}', name: 'staff_subscription_show', methods: ['GET'])]
    public function show(Subscription $subscription): Response
    {
        return $this->render('staff/subscriptions/show.html.twig', [
            'subscription' => $subscription,
            'active_menu' => 'subscriptions',
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'staff_subscription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subscription $subscription): Response
    {
        // Only allow staff to edit subscriptions they created
        // If createdBy is null, deny access (legacy subscriptions created by admin)
        $currentUser = $this->getUser();
        $createdBy = $subscription->getCreatedBy();
        if ($createdBy === null || $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only edit subscriptions that you created.');
            return $this->redirectToRoute('staff_subscription_index');
        }

        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'UPDATE', 'Subscription', (string)$subscription->getId(), ['customer' => $subscription->getCustomer()?->getEmail()]);
            $this->addFlash('success', 'Subscription updated successfully!');

            return $this->redirectToRoute('staff_subscription_index');
        }

        return $this->render('staff/subscriptions/edit.html.twig', [
            'subscription' => $subscription,
            'form' => $form,
            'active_menu' => 'subscriptions',
        ]);
    }

    #[Route('/{id<\d+>}/delete', name: 'staff_subscription_delete', methods: ['POST'])]
    public function delete(Request $request, Subscription $subscription): Response
    {
        // Only allow staff to delete subscriptions they created
        // If createdBy is null, deny access (legacy subscriptions created by admin)
        $currentUser = $this->getUser();
        $createdBy = $subscription->getCreatedBy();
        if ($createdBy === null || $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only delete subscriptions that you created.');
            return $this->redirectToRoute('staff_subscription_index');
        }

        if ($this->isCsrfTokenValid('delete'.$subscription->getId(), $request->request->get('_token'))) {
            $subscriptionId = $subscription->getId();
            $customerEmail = $subscription->getCustomer()?->getEmail();
            
            $this->entityManager->remove($subscription);
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'DELETE', 'Subscription', (string)$subscriptionId, ['customer' => $customerEmail]);
            $this->addFlash('success', 'Subscription deleted successfully!');
        }

        return $this->redirectToRoute('staff_subscription_index');
    }

    #[Route('/plans/', name: 'staff_subscription_plan_index', methods: ['GET'])]
    public function plansIndex(): Response
    {
        $plans = $this->subscriptionPlanRepository->findAll();

        return $this->render('staff/subscription_plans/index.html.twig', [
            'plans' => $plans,
            'active_menu' => 'subscriptions',
        ]);
    }

    #[Route('/plans/new', name: 'staff_subscription_plan_new', methods: ['GET', 'POST'])]
    public function planNew(Request $request): Response
    {
        $plan = new SubscriptionPlan();
        $form = $this->createForm(SubscriptionPlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set the creator to the current staff user
            $plan->setCreatedBy($this->getUser());
            $this->entityManager->persist($plan);
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'CREATE', 'SubscriptionPlan', (string)$plan->getId(), ['name' => $plan->getName()]);
            $this->addFlash('success', 'Subscription plan created successfully!');

            return $this->redirectToRoute('staff_subscription_plan_index');
        }

        return $this->render('staff/subscription_plans/new.html.twig', [
            'plan' => $plan,
            'form' => $form,
            'active_menu' => 'subscriptions',
        ]);
    }

    #[Route('/plans/{id<\d+>}/edit', name: 'staff_subscription_plan_edit', methods: ['GET', 'POST'])]
    public function planEdit(Request $request, SubscriptionPlan $plan): Response
    {
        // Only allow staff to edit subscription plans they created
        // If createdBy is null, deny access (legacy plans created by admin)
        $currentUser = $this->getUser();
        $createdBy = $plan->getCreatedBy();
        if ($createdBy === null || $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only edit subscription plans that you created.');
            return $this->redirectToRoute('staff_subscription_plan_index');
        }

        $form = $this->createForm(SubscriptionPlanType::class, $plan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'UPDATE', 'SubscriptionPlan', (string)$plan->getId(), ['name' => $plan->getName()]);
            $this->addFlash('success', 'Subscription plan updated successfully!');

            return $this->redirectToRoute('staff_subscription_plan_index');
        }

        return $this->render('staff/subscription_plans/edit.html.twig', [
            'plan' => $plan,
            'form' => $form,
            'active_menu' => 'subscriptions',
        ]);
    }

    #[Route('/plans/{id<\d+>}', name: 'staff_subscription_plan_delete', methods: ['POST'])]
    public function planDelete(Request $request, SubscriptionPlan $plan): Response
    {
        // Only allow staff to delete subscription plans they created
        // If createdBy is null, deny access (legacy plans created by admin)
        $currentUser = $this->getUser();
        $createdBy = $plan->getCreatedBy();
        if ($createdBy === null || $createdBy !== $currentUser) {
            $this->addFlash('error', 'You can only delete subscription plans that you created.');
            return $this->redirectToRoute('staff_subscription_plan_index');
        }

        if ($this->isCsrfTokenValid('delete'.$plan->getId(), $request->request->get('_token'))) {
            $planId = $plan->getId();
            $planName = $plan->getName();
            
            $this->entityManager->remove($plan);
            $this->entityManager->flush();

            $this->activityLogger->log($this->getUser(), 'DELETE', 'SubscriptionPlan', (string)$planId, ['name' => $planName]);
            $this->addFlash('success', 'Subscription plan deleted successfully!');
        }

        return $this->redirectToRoute('staff_subscription_plan_index');
    }
}

