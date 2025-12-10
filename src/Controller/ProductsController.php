<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
use App\Repository\ProductsRepository;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/products')]
final class ProductsController extends AbstractController
{
    public function __construct(
        private ActivityLogger $activityLogger
    ) {
    }
    #[Route(name: 'app_products_index', methods: ['GET'])]
    public function index(ProductsRepository $productsRepository): Response
    {
        // Staff can see all products (admin and other staff's), but can only edit/delete their own
        // Admins see all products
        if ($this->isGranted('ROLE_ADMIN')) {
            $products = $productsRepository->findAll();
            return $this->redirectToRoute('admin_dashboard');
        }

        // Staff see all products with staff template
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {
            $products = $productsRepository->findAll();
            return $this->render('products/staff_index.html.twig', [
                'products' => $products,
                'currentUser' => $this->getUser(),
            ]);
        }

        // Regular users see all products
        $products = $productsRepository->findAll();
        return $this->render('products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'app_products_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        $product = new Products();
        $product->setCreatedBy($this->getUser());
        $product->setCreatedAt(new \DateTimeImmutable());
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                try {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    
                    // Try to guess extension, fallback to client extension if fileinfo is not available
                    try {
                        $extension = $imageFile->guessExtension();
                    } catch (\Exception $e) {
                        // Fallback to client extension if guessExtension fails
                        $extension = $imageFile->getClientOriginalExtension() ?: 'jpg';
                    }
                    
                    // Ensure extension is valid (jpg, jpeg, png, webp)
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array(strtolower($extension), $allowedExtensions)) {
                        $extension = 'jpg'; // Default fallback
                    }
                    
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
                    $imagesDirectory = $this->getParameter('images_directory');
                    
                    // Ensure directory exists
                    if (!is_dir($imagesDirectory)) {
                        mkdir($imagesDirectory, 0755, true);
                    }
                    
                    $imageFile->move($imagesDirectory, $newFilename);
                    $product->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error uploading image: ' . $e->getMessage());
                }
            }

            // Ensure createdAt is set if not already set
            if (!$product->getCreatedAt()) {
                $product->setCreatedAt(new \DateTimeImmutable());
            }

            $entityManager->persist($product);
            $entityManager->flush();

            $this->activityLogger->log(
                $this->getUser(),
                'CREATE',
                'Products',
                (string)$product->getId(),
                ['name' => $product->getName()]
            );

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_products', [], Response::HTTP_SEE_OTHER);
            }
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        // Use staff template for staff, admin template for admin
        $template = ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) 
            ? 'products/staff_new.html.twig' 
            : 'products/new.html.twig';

        return $this->render($template, [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_products_show', methods: ['GET'])]
    public function show(Products $product): Response
    {
        // Staff can view all products, but edit/delete restrictions are handled in edit/delete methods
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->render('products/staff_show.html.twig', [
                'product' => $product,
                'currentUser' => $this->getUser(),
                'canEdit' => $product->getCreatedBy() === $this->getUser(),
            ]);
        }

        // Admin sees admin template
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->render('products/show.html.twig', [
                'product' => $product,
            ]);
        }

        // Regular customers see customer-facing template
        return $this->render('products/customer_show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function edit(Request $request, Products $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        // Staff can only edit their own records, admins can edit any
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {
            if ($product->getCreatedBy() !== $this->getUser()) {
                $this->addFlash('error', 'Access denied. You can only edit your own records.');
                return $this->redirectToRoute('staff_dashboard');
            }
        }

        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload if a new image is provided
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                try {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    
                    // Try to guess extension, fallback to client extension if fileinfo is not available
                    try {
                        $extension = $imageFile->guessExtension();
                    } catch (\Exception $e) {
                        // Fallback to client extension if guessExtension fails
                        $extension = $imageFile->getClientOriginalExtension() ?: 'jpg';
                    }
                    
                    // Ensure extension is valid (jpg, jpeg, png, webp)
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    if (!in_array(strtolower($extension), $allowedExtensions)) {
                        $extension = 'jpg'; // Default fallback
                    }
                    
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
                    $imagesDirectory = $this->getParameter('images_directory');
                    
                    // Ensure directory exists
                    if (!is_dir($imagesDirectory)) {
                        mkdir($imagesDirectory, 0755, true);
                    }

                    // Delete old image if it exists
                    $oldImage = $product->getImage();
                    if ($oldImage && file_exists($imagesDirectory . '/' . $oldImage)) {
                        unlink($imagesDirectory . '/' . $oldImage);
                    }

                    $imageFile->move($imagesDirectory, $newFilename);
                    $product->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error uploading image: ' . $e->getMessage());
                }
            }

            $entityManager->flush();

            $this->activityLogger->log(
                $this->getUser(),
                'UPDATE',
                'Products',
                (string)$product->getId(),
                ['name' => $product->getName()]
            );

            if ($this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_products', [], Response::HTTP_SEE_OTHER);
            }
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        // Use staff template for staff, admin template for admin
        $template = ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) 
            ? 'products/staff_edit.html.twig' 
            : 'products/edit.html.twig';

        return $this->render($template, [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_products_delete', methods: ['POST'])]
    #[IsGranted('ROLE_STAFF')]
    public function delete(Request $request, Products $product, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_STAFF');

        // Staff can only delete their own records, admins can delete any
        if ($this->isGranted('ROLE_STAFF') && !$this->isGranted('ROLE_ADMIN')) {
            if ($product->getCreatedBy() !== $this->getUser()) {
                $this->addFlash('error', 'Access denied. You can only delete your own records.');
                return $this->redirectToRoute('staff_dashboard');
            }
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $productId = $product->getId();
            $productName = $product->getName();

            $entityManager->remove($product);
            $entityManager->flush();

            $this->activityLogger->log(
                $this->getUser(),
                'DELETE',
                'Products',
                (string)$productId,
                ['name' => $productName]
            );
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_products', [], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }
}
