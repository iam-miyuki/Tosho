<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/account')]
#[IsGranted('ROLE_ADMIN')]
final class AccountController extends AbstractController
{
    #[Route('/', name: 'admin_account')]
    public function index(): Response
    {
        $admin = $this->getUser();
        return $this->render('admin/account/index.html.twig', [
            'admin' => $admin,
        ]);
    }
}
