<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route(path: '/admin/librarien')]
#[IsGranted('ROLE_ADMIN')]
final class LibrarienController extends AbstractController
{
    #[Route('/', name: 'librarien')]
    public function all(
        UserRepository $userRepository
    ): Response {
        $roles = 'ROLE_LIBRARIEN';
        $librariens = $userRepository->findAllByRoles($roles);
        return $this->render('admin/librarien/index.html.twig', [
            'librariens' => $librariens
        ]);
    }
    #[Route('/delete/{id}', name: 'delete-librarien')]
    public function delete(
        User $user,
        EntityManagerInterface $em
    ): Response {
        if ($user) {
            $em->remove($user);
            $em->flush();
            dd('supprimé !');
        }
        return $this->render('admin/librarien/index.html.twig', [
            'currentLibrarien' => $user
        ]);
    }
    #[Route('/change-status/{id}', name: 'change-status')]
    public function change(
        User $user, //ParamConverter : faire le lien entre le paramètre dans l'url(id) et l'entité sans faire de requette
        EntityManagerInterface $em
    ): JsonResponse {
        $user->setIsActive(!$user->isActive());
        $em->flush();

        return $this->json([
            'isActive' => $user->isActive()
        ]);
    }
}
