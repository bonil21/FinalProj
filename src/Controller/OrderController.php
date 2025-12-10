<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\CustomerRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/order')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CustomerRepository $customerRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/success/{id}', name: 'app_order_success', methods: ['GET'])]
    public function success(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $order = $this->orderRepository->find($id);
        
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

        return $this->render('order/success.html.twig', [
            'order' => $order,
        ]);
    }

    private function getOrCreateCustomer()
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
            $customer = new \App\Entity\Customer();
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

