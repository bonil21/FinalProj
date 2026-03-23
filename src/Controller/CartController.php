<?php

namespace App\Controller;

use App\Entity\CartItem;
use App\Entity\Products;
use App\Repository\CartItemRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private ProductsRepository $productsRepository,
        private CartItemRepository $cartItemRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    /**
     * Get cart items from session (guest) or database (logged-in user).
     */
    private function getCartItems(): array
    {
        $user = $this->getUser();
        if ($user instanceof \App\Entity\User) {
            $cartItems = $this->cartItemRepository->findByUser($user);
            return array_map(fn (CartItem $c) => [
                'type' => 'product',
                'id' => $c->getProduct()->getId(),
                'quantity' => $c->getQuantity(),
            ], $cartItems);
        }

        return $this->getSession()->get('cart', []);
    }

    /**
     * Persist cart for logged-in user; session for guest.
     */
    private function persistCart(array $cart): void
    {
        $user = $this->getUser();
        if ($user instanceof \App\Entity\User) {
            $this->cartItemRepository->deleteByUser($user);
            foreach ($cart as $item) {
                if (($item['type'] ?? '') !== 'product' || empty($item['id'])) {
                    continue;
                }
                $product = $this->productsRepository->find($item['id']);
                if (!$product) {
                    continue;
                }
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $cartItem = new CartItem();
                $cartItem->setUser($user);
                $cartItem->setProduct($product);
                $cartItem->setQuantity($quantity);
                $this->entityManager->persist($cartItem);
            }
            $this->entityManager->flush();
        } else {
            $this->getSession()->set('cart', $cart);
        }
    }

    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(): Response
    {
        $cart = $this->getCartItems();
        $items = [];
        $total = 0;

        foreach ($cart as $item) {
            if ($item['type'] === 'product') {
                $product = $this->productsRepository->find($item['id']);
                if ($product) {
                    $items[] = [
                        'type' => 'product',
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'price' => $product->getPrice(),
                        'image' => $product->getImage(),
                        'quantity' => $item['quantity'] ?? 1,
                    ];
                    $total += $product->getPrice() * ($item['quantity'] ?? 1);
                }
            }
        }

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/add/product/{id}', name: 'app_cart_add_product', methods: ['POST'])]
    public function addProduct(Products $product, Request $request): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Please log in or register first to add items to your cart.');

            return $this->redirectToRoute('app_login');
        }

        $tokenId = 'add_product_'.$product->getId();
        if (!$this->isCsrfTokenValid($tokenId, $request->request->getString('_token'))) {
            $this->addFlash('error', 'Could not add to cart. Please refresh the page and try again.');

            return $this->redirect($request->headers->get('referer') ?: $this->generateUrl('app_products_show', ['id' => $product->getId()]));
        }

        $cart = $this->getCartItems();
        $found = false;

        foreach ($cart as &$item) {
            if ($item['type'] === 'product' && $item['id'] === $product->getId()) {
                $item['quantity'] = ($item['quantity'] ?? 1) + 1;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'type' => 'product',
                'id' => $product->getId(),
                'quantity' => 1,
            ];
        }

        $this->persistCart($cart);
        $this->addFlash('success', $product->getName() . ' added to cart!');

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?: $this->generateUrl('app_cart'));
    }

    #[Route('/remove/{type}/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Request $request, string $type, int $id): Response
    {
        if (!$this->isCsrfTokenValid('cart_remove', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Could not remove item. Please try again.');

            return $this->redirectToRoute('app_cart');
        }

        $cart = $this->getCartItems();
        $cart = array_values(array_filter($cart, fn ($item) => !($item['type'] === $type && $item['id'] === $id)));
        $this->persistCart($cart);
        $this->addFlash('success', 'Item removed from cart');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cart_clear', $request->request->getString('_token'))) {
            $this->addFlash('error', 'Could not clear cart. Please try again.');

            return $this->redirectToRoute('app_cart');
        }

        $this->persistCart([]);
        $this->addFlash('success', 'Cart cleared');

        return $this->redirectToRoute('app_cart');
    }
}
