<?php

namespace App\Controller\Admin;

use App\Entity\Family;
use App\Entity\Member;
use App\Form\Member\MemberForm;
use App\Form\Family\SearchFamilyForm;
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

    #[Route('/new/{id}', name: 'new-member')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        Family $family
    ): Response {
        $member = new Member();
        $form = $this->createForm(MemberForm::class, $member);
        $form->handleRequest($request);

        $searchFamilyForm = $this->createForm(SearchFamilyForm::class, $family);
        $searchFamilyForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $member->setFamily($family);
            $em->persist($member);
            $em->flush();
            return $this->render('Admin/family/index.html.twig',[
                'thisFamily'=>$member->getFamily(),
                'thisMember'=>$member,
                'successMessage'=>'Ajout d\'enfant avec success',
                'tab'=>'new'
            ]);
        }
        return $this->render('Admin/family/index.html.twig', [
            'newMemberForm' => $form->createView(),
            'searchFamilyForm' => $searchFamilyForm->createView(),
            'id' => $family->getId(),
            'tab' => 'new',
            'familyToSet' => $family
        ]);
    }
    #[Route('/delete/{id}', name: 'delete-member')]
    public function delete(
        EntityManagerInterface $em,
        Request $request,
        Member $member
    ): Response {
        $family = $member->getFamily();
        if ($request->isMethod('POST')) {
            $em->remove($member);
            $em->flush();
            return $this->render('Admin/family/index.html.twig', [
                'deletedMember' => $member,
                'tab' => 'family',
                'successMessage' => 'Suppression de membre avec success !'
            ]);
        }
        return $this->render('Admin/family/index.html.twig',[
            'memberToDelete'=>$member,
            'tab'=>'family',
        ]);
    }
}
