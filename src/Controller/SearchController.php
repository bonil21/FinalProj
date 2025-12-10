<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/search')]
class SearchController extends AbstractController
{
    #[Route(name: 'app_search', methods: ['GET'])]
    public function search(Request $request, ProductsRepository $productsRepository): Response
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->redirectToRoute('app_products_index');
        }

        // Search products by name or description
        $products = $productsRepository->createQueryBuilder('p')
            ->where('p.name LIKE :query OR p.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getResult();

        // If staff, filter to only their products
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            $products = array_filter($products, function($product) use ($user) {
                return $product->getCreatedBy() === $user;
            });
        }

        return $this->render('products.html.twig', [
            'products' => $products,
            'search_query' => $query,
        ]);
    }
}

