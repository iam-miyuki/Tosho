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

    #[Route('/new', name:'new-member')]
    public function new(Request $request, EntityManagerInterface $em) : Response
    {  
        $id = $request->query->get('id');
        $family = $em->getRepository(Family::class)->find($id);
        $member = new Member();
        $form = $this->createForm(MemberForm::class ,$member);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $member = $form->getData();
            $member->setFamily($family);
            $em->persist($member);
            $em->flush();
            return $this->render('Admin/member/success.html.twig',[
                'member'=>$member,
                'family'=>$family
            ]);
        }
        return $this->render('Admin/member/form.html.twig',[
            'form'=>$form,
            'id'=>$id
        ]);
    }
    #[Route('/delete', name:'delete-member')]
    public function delete(
        Request $request,
        MemberRepository $memberRepository, 
        EntityManagerInterface $em) : Response
    {  
        $id = $request->query->get('id');
        $familyId = $request->query->get('family');
        $member = $memberRepository->find($id);
        if ($member) {
            $em->remove($member);
            $em->flush();
        }

        return $this->redirectToRoute('edit-family',[
            'id'=>$familyId
        ]);
    }

}
