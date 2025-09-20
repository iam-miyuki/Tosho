<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Account\AccountForm;
use App\Form\Account\ChangePwdForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route(path: '/account')]
#[IsGranted('ROLE_LIBRARIEN')]
final class AccountController extends AbstractController
{
    #[Route('/', name: 'account')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('account/index.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/edit', name: 'edit-account')]
    public function edit(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(AccountForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->render('account/index.html.twig', [
                'editedUser' => $user,
                'successMessage' => 'Modifié avec success !'
            ]);
        }
        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
            'userToEdit' => $user
        ]);
    }
    #[Route('/change', name: 'change-pwd')]
    public function changePwd(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        MailerInterface $mailer
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $pwdForm = $this->createForm(ChangePwdForm::class, $user);
        $pwdForm->handleRequest($request);
        if ($pwdForm->isSubmitted() && $pwdForm->isValid()) {
            $pwd     = $pwdForm->get('password')->getData();
            $newPwd  = $pwdForm->get('newPwd')->getData();
            $confirm = $pwdForm->get('confirm')->getData();

            if ($hasher->isPasswordValid($user, $pwd)) {
                if ($hasher->isPasswordValid($newPwd, $confirm)) {
                    $user->setPassword($hasher->hashPassword($user, $newPwd));
                    $em->flush();
                    $email = new TemplatedEmail();
                    $email
                        ->from('tosho@mail.com')
                        ->to($user->getEmail())
                        ->subject('Votre mot de passe a été changé avec success !')
                        ->htmlTemplate('account/email.html.twig')
                        ->context([
                            'user' => $user,
                        ]);
                    $mailer->send($email);
                    return $this->render('account/index.html.twig', [
                        'successMessage' => 'Mot de passe a été changé avec success !',
                    ]);
                } else {
                    return $this->render('account/index.html.twig', [
                        'errorConfirm' => 'Les deux nouveaux mots de passe ne correspondent pas.',
                        'pwdForm' => $pwdForm->createView(),
                    ]);
                }
            } else {
                return $this->render('account/index.html.twig', [
                    'errorPwd' => 'Le mot de passe actuel incorrect',
                    'pwdForm' => $pwdForm->createView(),
                ]);
            }
        }
        return $this->render('account/index.html.twig', [
            'pwdForm' => $pwdForm->createView(),
        ]);
    }
}
