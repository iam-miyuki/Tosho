<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Librarien\SearchForm;
use App\Repository\UserRepository;
use App\Form\Librarien\RegisterForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route(path: '/admin/librarien')]
#[IsGranted('ROLE_ADMIN')]
final class LibrarienController extends AbstractController
{
    private function generatePassword(int $length = 8): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $maxIndex = strlen($chars) - 1; // strlen(): get string length

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)]; //ajoute ce caractère à la fin de la chaîne $password.
        }

        return $password;
    }

    #[Route('/', name: 'librarien')]
    public function all(
        UserRepository $userRepository,
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $currentTab = $request->query->get('tab', 'family');
        $user = new User();
        $registerForm = $this->createForm(RegisterForm::class, $user);
        $registerForm->handleRequest($request);

        $form = $this->createForm(SearchForm::class, null);
        $form->handleRequest($request);

        $role = 'ROLE_LIBRARIEN';

        if (!$request->isMethod('POST')) {
            return $this->render('admin/librarien/index.html.twig', [
                'tab' => $currentTab,
                'searchForm' => $form,
                'registerForm' => $registerForm
            ]);
        }
        if ($form->isSubmitted()  && $form->isValid()) {
            $query = $form->get('query')->getData();
            $results = $userRepository->findAllWithFilterQuery($role, $query);
            return $this->render('admin/librarien/index.html.twig', [
                'librariens' => $results,
                'tab' => 'family',
                'searchForm' => $form
            ]);
        }

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $password = $this->generatePassword(8); // randomPassword
            $user->setPassword($hasher->hashPassword($user, $password));
            $user->setRoles(['ROLE_LIBRARIEN']);
            $user->setIsActive(true);
            $em->persist($user);
            $em->flush();
            $email = new TemplatedEmail();
            $email
                ->from('tosho@mail.com')
                ->to($user->getEmail())
                ->subject('Votre compte bibliothécaire')
                ->htmlTemplate('admin/librarien/email.html.twig')
                ->context([
                    'pwd' => $password,
                    'user' => $user
                ]);
            $mailer->send($email);
            return $this->render('admin/librarien/index.html.twig', [
                'tab' => 'new',
                'addedUser' => $user,
                'successMessage' => 'Ajout avec succès !'
            ]);
        }

        return new Response('500 Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    #[Route('/{id}', name: 'show-librarien')]
    public function show(
        User $user,
        Request $request
    ): Response {
        $currentTab = $request->query->get('tab', 'family');
        $form = $this->createForm(SearchForm::class, null);
        $form->handleRequest($request);

        return $this->render('admin/librarien/index.html.twig', [
            'tab' => $currentTab,
            'librarien' => $user,
            'searchForm' => $form
        ]);
    }

    #[Route('/delete/{id}', name: 'delete-librarien')]
    public function delete(
        User $user,
        EntityManagerInterface $em,
        Request $request
    ): Response {

        if (!$request->isMethod('POST')) {
            return $this->render('admin/librarien/index.html.twig', [
                'librarienToDelete' => $user,
                'tab' => 'family'
            ]);
        }

        $em->remove($user);
        $em->flush();
        return $this->render(
            'admin/librarien/index.html.twig',
            [
                'tab' => 'family',
                'deletedUser' => $user,
                'successMessage' => 'Suppression avec succès !'
            ]
        );
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
