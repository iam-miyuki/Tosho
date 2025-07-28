<?php

namespace App\Controller\Admin;

use App\Entity\Family;
use App\Entity\Member;
use App\Form\FamilyForm;
use App\Form\MemberForm;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/admin/member')]
#[IsGranted('ROLE_ADMIN')]
final class MemberController extends AbstractController
{
    #[Route('/', name: 'member')]
    public function index(): Response
    {
        return $this->render('Admin/member/success.html.twig', [
            'controller_name' => 'MemberController',
        ]);
    }

    #[Route('/new/{id}', name:'new-member')]
    public function new(
        Request $request, 
        EntityManagerInterface $em,
        Family $family
        ): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberForm::class, $member);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $member->setFamily($family);
            $em->persist($member);
            $em->flush();
            return $this->render('Admin/member/success.html.twig', [
                'member' => $member,
                'family' => $family
            ]);
        }
        return $this->render('Admin/member/form.html.twig', [
            'form' => $form,
            'id' => $family->getId()
        ]);
    }
    #[Route('/delete/{id}', name:'delete-member')]
    public function delete(
        EntityManagerInterface $em,
        Member $member
    ): Response {
        $family = $member->getFamily();
            if ($member) {
            $em->remove($member);
            $em->flush();
        }

        return $this->redirectToRoute('edit-family', [
            'id' => $family->getId()
        ]);
    }
}
