<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RegisterForm;
use App\Form\Librarien\SearchForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route(path: '/admin/librarien')]
#[IsGranted('ROLE_ADMIN')]
final class LibrarienController extends AbstractController
{
    #[Route('/', name: 'librarien')]
    public function all(
        UserRepository $userRepository,
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $currentTab = $request->query->get('tab', 'family');
        $role = 'ROLE_LIBRARIEN';
        $user = new User();
        $registerForm = $this->createForm(RegisterForm::class, $user);
        $registerForm->handleRequest($request);

        $form = $this->createForm(SearchForm::class, null);
        $form->handleRequest($request);
       
        if ($request->isMethod('POST')) {
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
                    $password = random_int(1000,9999); // randomPassword
                    $user->setPassword($hasher->hashPassword($user, $password));
                    $user->setRoles(['ROLE_LIBRARIEN']);
                    $em->persist($user);
                    $em->flush();
                    $email = new TemplatedEmail();
                    $email
                        ->from('tosho@mail.com')
                        ->to($user->getEmail())
                        ->subject('Votre compte bibliothécaire')
                        ->htmlTemplate('admin/librarien/email.html.twig')
                        ->context([
                            'pwd'=>$password,
                            'user'=>$user
                        ]);
                    $mailer->send($email);
                    return $this->render('admin/librarien/index.html.twig',[
                        'tab'=>'new',
                        'addedUser'=>$user
                    ]);
                }
        }

        return $this->render('admin/librarien/index.html.twig', [
            'tab' => $currentTab,
            'searchForm' => $form,
            'registerForm'=>$registerForm
        ]);
    }

    #[Route('/{id}', name: 'show-librarien')]
    public function show(
        User $user,
        Request $request
    ): Response {
        $currentTab = $request->query->get('tab', 'family');
        $role = 'ROLE_LIBRARIEN';
        $form = $this->createForm(SearchForm::class, null);
        $form->handleRequest($request);
        return $this->render('admin/librarien/index.html.twig', [
            'tab' => 'family',
            'librarien' => $user,
            'searchForm' => $form
        ]);
    }

    #[Route('/edit/{id}', name: 'edit-librarien')]
    public function edit(
        User $user,
        Request $request
    ): Response {
        $form = $this->createForm(SearchForm::class, null);
        $form->handleRequest($request);
        return $this->render('Admin/librarien/index.html.twig', [
            'librarienToEdit' => $user,
            'tab' => 'family',
            'searchForm' => $form
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
