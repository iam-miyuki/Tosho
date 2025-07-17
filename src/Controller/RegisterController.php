<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegisterForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, $user->getPassword()));
            $em->persist($user);
            $em->flush();
            $email = new Email();
            $email
                ->to($user->getEmail())
                ->subject('test email')
                ->html('<p>Ici on test email</p>');
            $mailer->send($email);
            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig', [
            'registerForm' => $form->createView()
        ]);
    }
}
