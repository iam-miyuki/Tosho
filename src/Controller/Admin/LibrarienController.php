<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/admin/librarien')]
#[IsGranted('ROLE_ADMIN')]
final class LibrarienController extends AbstractController
{
    #[Route('/', name: 'librarien')]
    public function all(
        UserRepository $userRepository
    ): Response
    {   
        $roles = 'ROLE_LIBRARIEN';
        $librariens = $userRepository->findAllByRoles($roles);
        return $this->render('admin/librarien/index.html.twig', [
            'librariens' => $librariens
        ]);
    }
}
