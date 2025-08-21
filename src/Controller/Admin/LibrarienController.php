<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Librarien\SearchForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

#[Route(path: '/admin/librarien')]
#[IsGranted('ROLE_ADMIN')]
final class LibrarienController extends AbstractController
{
    #[Route('/', name: 'librarien')]
    public function all(
        UserRepository $userRepository,
        Request $request,
    ): Response {
        $currentTab = $request->query->get('tab', 'family');
        $role = 'ROLE_LIBRARIEN';
        $librarien = new User;
        $form = $this->createForm(SearchForm::class, $librarien);
        $form->handleRequest($request);
        
        if ($request->isMethod('POST')) {
            if ($currentTab === 'family') {
                if ($form->isSubmitted()) {
                    $query = $form->get('query')->getData();
                    $results = $userRepository->findAllWithFilterQuery($role, $query);
                    return $this->render('admin/librarien/index.html.twig', [
                        'librariens' => $results,
                        'tab' => 'family',
                        'searchForm' => $form
                    ]);
                }
            }
            if ($currentTab === 'new') {
                // TODO
            }
        }

        return $this->render('admin/librarien/index.html.twig', [
            'tab' => $currentTab,
            'searchForm' => $form
        ]);
    }

    #[Route('/{id}', name: 'show-librarien')]
    public function show(
        User $user,
        Request $request
    ): Response {
        $currentTab = $request->query->get('tab', 'family');
        $role = 'ROLE_LIBRARIEN';
        $librarien = new User;
        $form = $this->createForm(SearchForm::class, $librarien);
        $form->handleRequest($request);
        return $this->render('admin/librarien/index.html.twig', [
            'tab' => 'family',
            'librarien' => $user,
            'searchForm'=>$form
        ]);
    }



    #[Route('/edit/{id}', name: 'edit-librarien')]
    public function edit(
        User $user,
    ) : Response {
        return $this->render('Admin/librarien/index.html.twig',[
            'librarienToEdit'=>$user
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
