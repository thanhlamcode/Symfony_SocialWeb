<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function dashboard(): Response
    {
        return $this->render('dashboard.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}
