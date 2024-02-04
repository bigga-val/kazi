<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('index.html.twig');
    }

    public function analytics(): Response
    {
        return $this->render('analytics.html.twig');
    }

    public function finance(): Response
    {
        return $this->render('finance.html.twig');
    }

    public function crypto(): Response
    {
        return $this->render('crypto.html.twig');
    }

}
