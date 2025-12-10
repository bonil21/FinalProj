<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    #[Route('', name: 'app_cart', methods: ['GET'])]
    public function index(
        ProductsRepository $productsRepository
    ): Response {
        $cart = $this->getSession()->get('cart', []);
        $items = [];
        $total = 0;

        foreach ($cart as $item) {
            if ($item['type'] === 'product') {
                $product = $productsRepository->find($item['id']);
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
        $cart = $this->getSession()->get('cart', []);
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

        $this->getSession()->set('cart', $cart);
        $this->addFlash('success', $product->getName() . ' added to cart!');

        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_cart'));
    }


    #[Route('/remove/{type}/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(string $type, int $id): Response
    {
        $cart = $this->getSession()->get('cart', []);
        $cart = array_filter($cart, function($item) use ($type, $id) {
            return !($item['type'] === $type && $item['id'] === $id);
        });
        $this->getSession()->set('cart', array_values($cart));
        $this->addFlash('success', 'Item removed from cart');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(): Response
    {
        $this->getSession()->set('cart', []);
        $this->addFlash('success', 'Cart cleared');

        return $this->redirectToRoute('app_cart');
    }
}

