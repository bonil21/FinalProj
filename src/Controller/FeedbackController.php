<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/feedback')]
class FeedbackController extends AbstractController
{
    #[Route('/new', name: 'feedback_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feedback = new Feedback();
        $feedback->setAuthor($this->getUser());

        $form = $this->createForm(FeedbackType::class, $feedback, ['include_product' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feedback);
            $entityManager->flush();

            $this->addFlash('success', 'Thank you for your feedback!');
            return $this->redirectToRoute('feedback_new');
        }

        return $this->render('feedback/new.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('', name: 'feedback_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(FeedbackRepository $feedbackRepository): Response
    {
        $feedbacks = $feedbackRepository->findBy(
            ['author' => $this->getUser()],
            ['createdAt' => 'DESC']
        );

        return $this->render('feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }
}
