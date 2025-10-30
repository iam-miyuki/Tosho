<?php

namespace App\Controller;

use Doctrine\ORM\EntityManager;
use App\Form\Security\EmailForm;
use App\Repository\UserRepository;
use App\Form\Security\ResetPwdForm;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    #[Route(path: '/forget', name:'forget-pwd')]
    public function forgetPwd(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer
    ): Response {

        $emailForm = $this->createForm(EmailForm::class,null);
        $emailForm->handleRequest($request);
        if($emailForm->isSubmitted() && $emailForm->isValid()){
            $userMail = $emailForm->get('email')->getData();
            $existingEmail = $userRepository->findOneByEmail($userMail);
            if($existingEmail){
                $user = $this->getUser();
                //TODO générer un lien unique pour réinitialisation de mot de passe
                // $email = new TemplatedEmail();
                // $email
                //     ->from('tosho@mail.com')
                //     ->to($userMail)
                //     ->subject('Réinitialisation de votre mot de passe')
                //     ->htmlTemplate('security/email.html.twig')
                //     ->context([
                //         'link'=>??
                //         'user' => $user
                //     ]);
                // $mailer->send($email);
            } else{
                return $this->render('security/login.html.twig',[
                    'errorMessage'=>'Adress mail non valide'
                ]);
            }
        }
        return $this->render('security/login.html.twig',[

        ]);
    }
    #[Route(path: '/reset', name:'reset-pwd')]
    public function setPwd(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManager $em
    ) : Response {
        $resetPwdForm = $this->createForm(ResetPwdForm::class,null);
        $resetPwdForm->handleRequest($request);
        $user = $this->getUser();
        if($resetPwdForm->isSubmitted() && $resetPwdForm->isValid()){
            $newPwd = $resetPwdForm->get('newPwd')->getData();
            $confirm = $resetPwdForm->get('confirm')->getData();
            if($hasher->isPasswordValid($newPwd,$confirm)){
                $user->setPassword($newPwd);
                $em->flush();
                return $this->render('security/login.html.twig',[
                    'successMessage'=>'Mot de passe a été réinitialisé avec succès !'
                ]);
            }
        }
        return $this->render('security/login.html.twig',[
            
        ]);
    }
}
