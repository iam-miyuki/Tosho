<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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
    #[Route('/edit/{id}', name:'edit-account')]
    public function edit(
        User $user, // param converter
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(AccountForm::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()&&$form->isValid()) {
            $em->flush();
            return $this->render('account/index.html.twig',[
                'editedUser'=>$user,
                'successMessage'=>'Modifié avec success !'
            ]);
        }
        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
            'userToEdit'=>$user
        ]);
    }
    // #[Route('/setting/{id}', name:'change-pwd')]
    // public function changePwd(
    //     User $user,
    // ): Response {

    // }
    // TODO : changer le mot de passe, mot de passe oublié
}
