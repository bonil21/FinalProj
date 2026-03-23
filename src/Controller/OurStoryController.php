<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OurStoryController extends AbstractController
{
    #[Route('/our-story', name: 'app_our_story', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('our_story/index.html.twig');
    }
}
