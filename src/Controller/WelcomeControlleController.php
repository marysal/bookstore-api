<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WelcomeControlleController extends AbstractController
{
    /**
     * @Route("/welcome", name="welcome")
     */
    public function index(): Response
    {
        return $this->render('welcome_controlle/index.html.twig', [
            'controller_name' => 'WelcomeControlleController',
        ]);
    }
}
