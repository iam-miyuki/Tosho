<?php

namespace App\Controller\Admin;

use App\Entity\Family;
use App\Form\FamilyForm;
use App\Form\SearchFamilyForm;
use App\Repository\FamilyRepository;
use App\Repository\MemberRepository;
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
        FamilyRepository $familyRepository,
    ): Response {
        $family = new Family();
        $form = $this->createForm(FamilyForm::class, $family);
        $form->handleRequest($request);

        $searchForm = $this->createForm(SearchFamilyForm::class, $family);
        $searchForm->handleRequest($request);

        $currentTab = $request->query->get('tab', 'family');
        $results = null;

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

                return $this->render('Admin/family/success.html.twig', [
                    'family' => $family
                ]);
            }
        }

        return $this->render('Admin/family/index.html.twig', [
            'tab' => $currentTab,
            'searchedFamilies' => $results,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView()
        ]);
    }

    #[Route('/{id}', name: 'show-family')]
    public function show(
        Family $family,
        MemberRepository $memberRepository,
        Request $request
    ): Response {
        $form = $this->createForm(FamilyForm::class, $family);
        $form->handleRequest($request);

        $searchForm = $this->createForm(SearchFamilyForm::class, $family);
        $searchForm->handleRequest($request);

        if ($family) {
            $members = $memberRepository->findAllByFamily($family);
        }
        return $this->render('Admin/family/index.html.twig', [
            'tab' => 'family',
            'currentFamily' => $family,
            'members' => $members,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView()
        ]);
    }
    #[Route('/edit/{id}', name: 'edit-family')]
    public function edit(
        Family $family,
        Request $request,
        EntityManagerInterface $em,
        MemberRepository $memberRepository
    ): Response {
        $members = $memberRepository->findAllByFamily($family);

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
    public function delete(
        Family $family,
        EntityManagerInterface $em,
    ): Response {
        if ($family) {
            $em->remove($family);
            $em->flush();
        }

        return $this->redirectToRoute('family', ['tab' => 'family']);
    }
}
