<?php

namespace App\Controller;

use App\Entity\Family;
use App\Entity\Member;
use App\Form\FamilyForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/family')]
#[IsGranted('ROLE_USER')]
final class FamilyController extends AbstractController
{
    #[Route('/', name: 'family')]
public function index(Request $request, EntityManagerInterface $em): Response
{
    $family = new Family();
    $form = $this->createForm(FamilyForm::class, $family);
    $form->handleRequest($request);

    $currentTab = $request->query->get('tab','family');
    $searchedFamilies = null;
    $currentFamily = null;
    $members = null;
    $familyId = $request->query->get('id');

    if ($request->isMethod('POST')) {
        if ($request->request->has('family_name')) {
            $familyName = $request->request->get('family_name');
            $searchedFamilies = $em->getRepository(Family::class)->findAllByName($familyName);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $family = $form->getData();
            $family->setCreatedAt(new \DateTimeImmutable());
            $em->persist($family);
            $em->flush();

            return $this->render('family/success.html.twig');
        }
    }

    if ($familyId) {
        $currentFamily = $em->getRepository(Family::class)->find($familyId);
        if ($currentFamily) {
            $members = $em->getRepository(Member::class)->findAllByFamily($currentFamily);
        }
    }

    return $this->render('family/index.html.twig', [
    'tab' => $currentTab,
    'searchedFamilies' => $searchedFamilies,
    'currentFamily' => $currentFamily,
    'members' => $members,
    'form' => $form,
]);
}



    #[Route('/edit/{id}', name: 'edit-family', requirements: ['id' => '\d+'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $family = $em->getRepository(Family::class)->find($id);
        $members = $em->getRepository(Member::class)->findByFamily($family);

        $form = $this->createForm(FamilyForm::class, $family);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            dd('modifié !');
            return $this->redirectToRoute('show-family', ['id' => $family->getId()]);
        }

        return $this->render('family/edit.html.twig', [
            'form' => $form,
            'currentFamily' => $family,
            'members' => $members,

        ]);
    }

    #[Route('/delete/{id}', name: 'delete-family', requirements: ['id' => '\d+'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $family = $em->getRepository(Family::class)->find($id);

        if ($family) {
            $em->remove($family);
            $em->flush();
        }

        return $this->redirectToRoute('family', ['tab' => 'search']);
    }
}
