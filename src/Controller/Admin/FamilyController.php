<?php

namespace App\Controller\Admin;

use App\Entity\Family;

use App\Form\Family\FamilyForm;
use App\Repository\FamilyRepository;
use App\Form\Family\SearchFamilyForm;
use App\Repository\LoanRepository;
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

        $searchFamilyForm = $this->createForm(SearchFamilyForm::class, $family);
        $searchFamilyForm->handleRequest($request);

        $currentTab = $request->query->get('tab', 'family');
        $results = null;

        if ($request->isMethod('POST')) {
            if ($searchFamilyForm->isSubmitted()) {
                $name = $searchFamilyForm->get('search')->getData();
                $results = $familyRepository->findAllByName($name);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $family = $form->getData();
                $family->setCreatedAt(new \DateTimeImmutable());
                $em->persist($family);
                $em->flush();
                return $this->render('admin/family/index.html.twig',[
                    'addedFamily'=>$family,
                    'tab'=>'new',
                    'newFamilyForm'=>$form->createView(),
                    'successMessage'=>'Ajouter des membres de la famille',
                ]);
            }
        }

        return $this->render('admin/family/index.html.twig', [
            'tab' => $currentTab,
            'searchedFamilies' => $results,
            'newFamilyForm' => $form->createView(),
            'searchFamilyForm' => $searchFamilyForm->createView()
        ]);
    }

    #[Route('/{id}', name: 'show-family')]
    public function show(
        Family $family,
        Request $request
    ): Response {
        $form = $this->createForm(FamilyForm::class, $family);
        $form->handleRequest($request);

        $searchFamilyForm = $this->createForm(SearchFamilyForm::class, $family);
        $searchFamilyForm->handleRequest($request);

        // TODO : if ($family) {
        // }
        return $this->render('admin/family/index.html.twig', [
            'tab' => 'family',
            'currentFamily' => $family,
            'form' => $form->createView(),
            'searchFamilyForm' => $searchFamilyForm->createView()
        ]);
    }
    #[Route('/edit/{id}', name: 'edit-family')]
    public function edit(
        Family $family,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(FamilyForm::class, $family);
        $form->handleRequest($request);

        $searchFamilyForm = $this->createForm(SearchFamilyForm::class, $family);
        $searchFamilyForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->render('admin/family/index.html.twig',[
                'edited'=>$family,
                'tab'=>'family',
                'successMessage'=>'Modifié avec success !',
                'searchFamilyForm'=>$searchFamilyForm->createView()
            ]);
        }

        return $this->render('admin/family/index.html.twig', [
            'form' => $form,
            'familyToEdit' => $family,
            'searchFamilyForm' => $searchFamilyForm->createView(),
            'tab' => 'family'
        ]);
    }

    #[Route('/delete/{id}', name: 'delete-family')]
    public function delete(
        Family $family,
        EntityManagerInterface $em,
        LoanRepository $loanRepository,
        Request $request
    ): Response {
        $searchFamilyForm = $this->createForm(SearchFamilyForm::class, $family);
        $searchFamilyForm->handleRequest($request);
        if ($loanRepository->findAllWithFamilyAndStatus($family)) {
            return $this->render('admin/family/index.html.twig', [
                'familyHasLoan' => $family,
                'tab' => 'family',
                'searchFamilyForm' => $searchFamilyForm
            ]);
        }
        if ($request->isMethod('POST')) {

            $em->remove($family);
            $em->flush();
            return $this->render('admin/family/index.html.twig', [
                'deleted' => $family,
                'tab' => 'family',
                'searchFamilyForm' => $searchFamilyForm,
                'successMessage' => 'Suppression de famille avec success !'
            ]);
        }

        return $this->render('admin/family/index.html.twig', [
            'familyToDelete' => $family,
            'tab'=>'family',
            'searchFamilyForm'=>$searchFamilyForm
        ]);
    }
}
