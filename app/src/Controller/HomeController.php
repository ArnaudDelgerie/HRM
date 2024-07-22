<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct() {} 

    #[Route('/', name: 'app_home')]
    public function index(): Response 
    {
        return $this->render('/home/index.html.twig');
    }

}

