<?php

namespace App\Controller;

use App\Entity\Family;
use App\Entity\Member;
use App\Form\FamilyTypeForm;
use App\Form\MemberTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/member')]
#[IsGranted('ROLE_USER')]
final class MemberController extends AbstractController
{
    #[Route('/', name: 'member')]
    public function index(): Response
    {
        return $this->render('member/index.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }

    #[Route('/new', name:'new-member')]
    public function new(Request $request, EntityManagerInterface $em) : Response
    {  
        $member = new Member();
        $form = $this->createForm(MemberTypeForm::class ,$member);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $member = $form->getData();
            $em->persist($member);
            $em->flush();
        }
        return $this->render('member/form.html.twig',[
            'form'=>$form
        ]);
    }
}
