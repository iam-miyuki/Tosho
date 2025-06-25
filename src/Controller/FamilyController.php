<?php

namespace App\Controller;

use App\Repository\FamilyRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class FamilyController extends AbstractController
{
    #[Route('/family', name: 'app_family')]
    public function index(): Response
    {
        return $this->render('family/index.html.twig', [
            'controller_name' => 'FamilyController',
        ]);
    }
    #[Route('/families', name: 'families')]
    public function list(FamilyRepository $familyRepository): Response
    {
        $families = $familyRepository->findAll(); // Récupère toutes les familles

        return $this->render('family/list.html.twig', [
            'families' => $families,
        ]);
    }
}
