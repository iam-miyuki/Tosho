<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\LibrarienForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
        Request $request,
        UserRepository $userRepository
    ): Response {
        $roles = 'ROLE_LIBRARIEN';
        $librariens = $userRepository->findAllByRoles($roles);
        return $this->render('admin/librarien/index.html.twig', [
            'librariens' => $librariens
        ]);
    }
    #[Route('/delete', name: 'delete-librarien')]
    public function delete(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $id = $request->query->get('id');
        $librarien = $userRepository->find($id);
        if ($librarien) {
            $em->remove($librarien);
            $em->flush();
            dd('supprimé !');
        }
        return $this->render('admin/librarien/index.html.twig', [
            'currentLibrarien' => $librarien
        ]);
    }
    #[Route('/change-status/{id}', name: 'change-status')]
    public function change(
        User $user,
        EntityManagerInterface $em
    ): JsonResponse {
        $user->setIsActive(!$user->isActive());
        $em->flush();

        return $this->json([
            'isActive' =>$user->isActive()
        ]);
    }
}
