<?php

namespace App\Controller;

use DateTime;
use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Family;
use App\Form\FamilyForm;
use App\Enum\BookStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Form\BookFilterForm;
use App\Form\FindBookForm;
use App\Form\LoanForm;
use App\Form\LoanSearchForm;
use App\Form\SearchFamilyForm;
use App\Repository\BookRepository;
use App\Repository\FamilyRepository;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/loan')]
#[IsGranted('ROLE_USER')]
final class LoanController extends AbstractController
{
    #[Route('/', name: 'loan')]
    public function index(
        Request $request,
        FamilyRepository $familyRepository,
        BookRepository $bookRepository,
        LoanRepository $loanRepository
        ): Response {
            $currentTab = $request->query->get('tab', 'family'); // tab 'family' par défaut
            
            $family = new Family();
            $searchFamilyForm = $this->createForm(SearchFamilyForm::class, $family);
           
            $results = null;
            
            $book = new Book();
            $searchBookForm = $this->createForm(BookFilterForm::class, $book);
            
            
            $findBookForm = $this->createForm(FindBookForm::class, $book);
            
            // afficher loans de currentFamily
            $familyId = $request->query->get('id_family');
            if ($familyId) {
                $currentFamily = $familyRepository->find($familyId);
                $currentLoans = $loanRepository->findAllWithFamilyAndStatus($currentFamily);
                return $this->render('loan/index.html.twig', [
                    'currentFamily' => $currentFamily,
                    'loans' => $currentLoans,
                    'tab' => 'family',
                    'searchFamilyForm' => $searchFamilyForm->createView(),
                    'searchBookForm' => $searchBookForm->createView(),
                    'findBookForm' => $findBookForm->createView(),
                ]);
            }
            if ($request->getMethod() === 'POST') {
                 $searchFamilyForm->handleRequest($request);
                 $searchBookForm->handleRequest($request);
                 $findBookForm->handleRequest($request);

            // chercher loan par famille
            if ($searchFamilyForm->isSubmitted()) {
                $name = $searchFamilyForm->get('search')->getData();
                $results = $familyRepository->findAllByName($name);
                return $this->render('loan/index.html.twig', [
                    'tab' => 'family',
                    'searchFamilyForm' => $searchFamilyForm->createView(),
                    'searchBookForm' => $searchBookForm->createView(),
                    'findBookForm' => $findBookForm->createView(),
                    'families' => $results,
                    'currentFamily' => null,
                    'loans' => null
                ]);
            }
            // chercher loan par livre
            if ($searchBookForm->isSubmitted()) {
                $keyword = $searchBookForm->get('filter')->getData();
                $results = $bookRepository->findAllWithFilterQuery($keyword);
                return $this->render('loan/index.html.twig', [
                    'tab' => 'book',
                    'searchFamilyForm' => $searchFamilyForm->createView(),
                    'searchBookForm' => $searchBookForm->createView(),
                    'findBookForm' => $findBookForm->createView(),
                    'books' => $results
                ]);
            }
            // trouver un livre par code 
            if ($findBookForm->isSubmitted()) {
                $code = $findBookForm->get('code')->getData();
                $currentBook = $bookRepository->findOneByCode($code);
                $currentLoan = $loanRepository->findWithBookAndStatus($currentBook);
                return $this->render('loan/index.html.twig', [
                    'tab' => 'book',
                    'searchFamilyForm' => $searchFamilyForm->createView(),
                    'searchBookForm' => $searchBookForm->createView(),
                    'findBookForm' => $findBookForm->createView(),
                    'book' => $currentBook,
                    'loan' => $currentLoan
                ]);
            }
        }
        return $this->render('loan/index.html.twig', [
            'tab' => $currentTab,
            'currentFamily' => null,
            'loans'=>null,
            'searchFamilyForm' => $searchFamilyForm->createView(),
            'searchBookForm' => $searchBookForm->createView(),
            'findBookForm' => $findBookForm->createView()
        ]);
    }


    // prêter un livre 
    #[Route(path: '/new', name: 'new-loan')]
    public function newLoan(
        Request $request,
        EntityManagerInterface $em,
        FamilyRepository $familyRepository,
        BookRepository $bookRepository
    ): Response {

        $familyId = $request->request->get('family_id');
        $family = $familyRepository->find($familyId);

        $loan = new Loan;

        $book = new Book;
        $findBookForm = $this->createForm(FindBookForm::class, $book);
        $findBookForm->handleRequest($request);

        if ($request->getMethod() === 'POST') {

            if ($findBookForm->isSubmitted()) {

                $code = $findBookForm->get('code')->getData();
                $book = $bookRepository->findOneByCode($code);

                if ($family && $book && $book->getStatus() != BookStatusEnum::borrowed) {

                    $loan->setFamily($family);
                    $loan->setBook($book);
                    $loan->setStatus(LoanStatusEnum::inProgress);
                    $loan->setLoanDate(new \DateTime());
                    $book->setStatus(BookStatusEnum::borrowed);
                    $em->persist($loan);
                    $em->persist($book);
                    $em->flush();
                    return $this->redirectToRoute('family-loans', [
                        'id' => $familyId,
                    ]);
                }

                if ($family && $book && $book->getStatus() === BookStatusEnum::borrowed) {
                    // TODO 
                    dd('ce livre est déjà emprunté !');
                } else {
                    // TODO 
                    dd('aucun livre trouvé !');
                }
            }
        }
        return $this->render('loan/index.html.twig', [
            'tab' => 'family',
            'findBookForm' => $findBookForm->createView()
        ]);
    }

    #[Route(path: '/return', name: 'return-book')]
    public function returnBook(
        int $id,
        LoanRepository $loanRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // changer statut de Loan et Book
        $loan = $loanRepository->find($id);
        $book = $loan->getBook();
        $familyId = $loan->getFamily()->getId();
        if ($loan) {
            if (
                $loan->getStatus() != LoanStatusEnum::returned
                && $book->getStatus() != BookStatusEnum::available
            ) {
                $loan->setStatus(LoanStatusEnum::returned);
                $book->setStatus(BookStatusEnum::available);
                $loan->setReturnDate(new \DateTime());
                $entityManager->persist($loan); // mise à jours d'une entité
                $entityManager->persist($book);
                $entityManager->flush(); // executer
                return $this->redirectToRoute('loan-by-family', [
                    'familyId' => $familyId
                ]);
            } else {
                dd('déjà rendu!');
            }
        } else {
            dd('non trouvé!');
        }
    }
}
