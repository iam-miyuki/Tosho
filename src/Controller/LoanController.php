<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoanController extends AbstractController
{
    #[Route(name: 'loan', path: '/loan')]
    public function loan() 
    {
        return $this->render('librarien/loan/loan.html.twig');
    }
}
