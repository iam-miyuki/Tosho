<?php

namespace App\Controller\Admin;

use App\Entity\Family;
use App\Entity\Member;
use App\Form\FamilyForm;
use App\Form\SearchFamilyForm;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/family')]
#[IsGranted('ROLE_ADMIN')]
final class FamilyController extends AbstractController
{
    #[Route('/', name: 'family')]
public function index(
    Request $request, 
    EntityManagerInterface $em,
    FamilyRepository $familyRepository
    ): Response
{
    $family = new Family();
    $form = $this->createForm(FamilyForm::class, $family);
    $form->handleRequest($request);

    $searchForm = $this->createForm(SearchFamilyForm::class, $family);
    $searchForm->handleRequest($request);

    $currentTab = $request->query->get('tab','family');
    $results = null;
    $currentFamily = null;
    $members = null;
    $familyId = $request->query->get('id');

    if ($request->isMethod('POST')) {
        if ($searchForm->isSubmitted()) {
            $name = $searchForm->get('search')->getData();
            $results = $familyRepository->findAllByName($name);
           
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $family = $form->getData();
            $family->setCreatedAt(new \DateTimeImmutable());
            $em->persist($family);
            $em->flush();

            return $this->render('Admin/family/success.html.twig',[
                'family'=>$family
            ]);
        }
    }

    if ($familyId) {
        $currentFamily = $familyRepository->find($familyId);
        if ($currentFamily) {
            $members = $em->getRepository(Member::class)->findAllByFamily($currentFamily);
        }
    }

    return $this->render('Admin/family/index.html.twig', [
    'tab' => $currentTab,
    'searchedFamilies' => $results,
    'currentFamily' => $currentFamily,
    'members' => $members,
    'form' => $form->createView(),
    'searchForm'=>$searchForm->createView()
]);
}



    #[Route('/edit/{id}', name: 'edit-family')]
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

        return $this->render('Admin/family/edit.html.twig', [
            'form' => $form,
            'currentFamily' => $family,
            'members' => $members,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete-family')]
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
