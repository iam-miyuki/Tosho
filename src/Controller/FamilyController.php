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


#[Route(path: '/family')]
#[IsGranted('ROLE_USER')]
final class FamilyController extends AbstractController
{
    #[Route('/', name: 'family')]
    public function index(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $familyName = $request->request->get('family_name');
        $searchedFamilies = null;
        if ($request->getMethod() === 'POST' && $request->request->has('family_name')) {
            $searchedFamilies = $em->getRepository(Family::class)->findAllByName($familyName);
            return $this->render('family/index.html.twig', [
                'searchedFamilies' => $searchedFamilies
            ]);
        }
        return $this->render('family/index.html.twig', [
            $familyName => null,
            $searchedFamilies => null
        ]);
    }

    #[Route('/new', name: 'new-family')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $family = new Family();
        $form = $this->createForm(FamilyTypeForm::class, $family);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $family->setCreatedAt(new \DateTimeImmutable('now'));
            $family = $form->getData();
            $em->persist($family);
            $em->flush();
            return $this->render('family/success.html.twig', [
                'family' => $family
            ]);
        }
        return $this->render('family/form.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'show-family')]
    public function read(int $id, EntityManagerInterface $em, Request $request)
    {

        $currentFamily = $em->getRepository(Family::class)->findOneById($id);
        $members = $em->getRepository(Member::class)->findByFamily([
            'family' => $currentFamily
        ]);
        if ($request->request->has('family_name')) {

            $familyName = $request->request->get('family_name');
            $searchedFamilies = $em->getRepository(Family::class)->findAllByName($familyName);
            return $this->render('family/index.html.twig', [
                'searchedFamilies' => $searchedFamilies
            ]);
        }
        return $this->render('family/index.html.twig', [
            'currentFamily' => $currentFamily,
            'members' => $members
        ]);
    }


    #[Route('/edit/{id}', name: 'edit-family')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $currentFamily = $em->getRepository(Family::class)->find($id);
        $members = $em->getRepository(Member::class)->findByFamily($currentFamily);
        $form = $this->createForm(FamilyTypeForm::class, $currentFamily);
        $form->handleRequest($request);

        if($form->isSubmitted()&&$form->isValid()){
            $em->flush();
            return $this->redirectToRoute('show-family',[
                'id'=>$currentFamily->getId()
            ]);
        }
        return $this->render('family/edit.html.twig',[
            'form'=>$form,
            'currentFamily'=>$currentFamily,
            'members'=>$members
        ]);
    }

    #[Route('/delete/{id}', name: 'delete-family')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $currentFamily = $em->getRepository(Family::class)->find($id);
        if ($currentFamily) {
            $em->remove($currentFamily);
            $em->flush();
        }
        return $this->render('family/index.html.twig');
    }
}
