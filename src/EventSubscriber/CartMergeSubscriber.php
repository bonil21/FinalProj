<?php

namespace App\EventSubscriber;

use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Merges session cart into user's persisted cart when they log in.
 */
class CartMergeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private CartItemRepository $cartItemRepository,
        private ProductsRepository $productsRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof \App\Entity\User) {
            return;
        }

        // JWT/API firewalls are stateless: never touch session there.
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null || $event->getFirewallName() === 'api_platform' || str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        $sessionCart = $session->get('cart', []);

        if (empty($sessionCart)) {
            return;
        }

        foreach ($sessionCart as $item) {
            if (($item['type'] ?? '') !== 'product' || empty($item['id'])) {
                continue;
            }

            $product = $this->productsRepository->find($item['id']);
            if (!$product) {
                continue;
            }

            $quantity = (int) ($item['quantity'] ?? 1);
            if ($quantity < 1) {
                continue;
            }

            $cartItem = $this->cartItemRepository->findOneByUserAndProduct($user, $product);
            if ($cartItem) {
                $cartItem->setQuantity($cartItem->getQuantity() + $quantity);
            } else {
                $cartItem = new CartItem();
                $cartItem->setUser($user);
                $cartItem->setProduct($product);
                $cartItem->setQuantity($quantity);
                $this->entityManager->persist($cartItem);
            }
        }

        $this->entityManager->flush();
        $session->set('cart', []);
    }
}
