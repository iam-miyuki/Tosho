<?php

namespace App\Controller;

use DateTime;
use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Family;
use App\Enum\BookStatusEnum;
use App\Enum\LoanStatusEnum;
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
        EntityManagerInterface $entityManager
    ): Response {
        $familyName = $request->request->get('family_name');
        $searchedFamilies = null;
        $bookCode = $request->request->get('book_code');
        $currentBook = null;
        $currentBookLoan = null;

        if ($request->getMethod() === 'POST') {
            if ($request->request->has('family_name')) {
                $searchedFamilies = $entityManager->getRepository(Family::class)->findAllByName($familyName);

                return $this->render('loan/index.html.twig', [
                    'searchedFamilies' => $searchedFamilies,
                    'currentFamily' => null,
                    'loans' => null,
                    'currentBook' => null,
                    'currentBookLoan' => null,
                    'tab' => 'family'
                ]);
            }
            if ($request->request->has('book_code')) {
                $familyName = null;
                $searchedFamilies = null;
                $currentBook = $entityManager->getRepository(Book::class)->findOneBy([
                    'bookCode' => $bookCode
                ]);
                $currentBookLoan = $entityManager->getRepository(Loan::class)->findOneBy([
                    'loanStatus' => LoanStatusEnum::inProgress,
                    'book' => $currentBook
                ]);
                return $this->render('loan/index.html.twig', [
                    'searchedFamilies' => null,
                    'currentFamily' => null,
                    'loans' => null,
                    'currentBook' => $currentBook,
                    'currentBookLoan' => $currentBookLoan,
                    'tab' => 'book'
                ]);
            }
        } // else (get...)
        $defaultTab = $request->query->get('tab');
        if (!$defaultTab) {
            $defaultTab = 'family';
        }
        return $this->render('loan/index.html.twig', [
            'searchedFamilies' => null,
            'currentFamily' => null,
            'loans' => null,
            'currentBook' => null,
            'currentBookLoan' => null,

            'tab' => $defaultTab
        ]);
    }

    #[Route('/new', name: 'new-loan')]
    public function newLoan(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->getMethod() === 'POST') {
            if ($request->request->has('family_id') && $request->request->has('book_code')) {
                $familyId = $request->request->get('family_id');
                $bookCode = $request->request->get('book_code');


                $family = $entityManager->getRepository(Family::class)->find($familyId);
                $book = $entityManager->getRepository(Book::class)->findOneByBookCode($bookCode);

                if ($family && $book && $book->getBookStatus() != BookStatusEnum::borrowed) {

                    $loan = new Loan;
                    $loan->setFamily($family);
                    $loan->setBook($book);
                    $loan->setLoanStatus(LoanStatusEnum::inProgress);
                    $loan->setLoanDate(new \DateTime());
                    $book->setBookStatus(BookStatusEnum::borrowed);
                    $entityManager->persist($loan);
                    $entityManager->persist($book);
                    $entityManager->flush();
                    return $this->redirectToRoute('loan-by-family', [
                        'familyId' => $familyId,
                    ]);
                }
                if ($family && $book && $book->getBookStatus() === BookStatusEnum::borrowed) {
                    dd('ce livre est déjà emprunté !');
                } else {
                    dd('aucun livre trouvé !');
                }
            }
            if ($request->request->has('book_code') && !$request->request->has('family_name')) {
                $bookCode = $request->request->get('book_code');
                $currentBook = $entityManager->getRepository(Book::class)->findOneByBookCode($bookCode);
                if ($currentBook && $currentBook != null) {

                    return $this->render('loan/index.html.twig', [
                        'currentBook' => $currentBook,
                        'currentBookLoan' => null,
                        'currentFamily' => null,
                        'searchedFamilies' => null,

                        'loans' => null,
                        'tab' => 'family'
                    ]);
                } 
                else {
                    dd('aucun livre trouvé !');
                }
            }
        }
        return $this->redirectToRoute('loan');
    }

    #[Route(path: '/{familyId}/list', name: 'loan-by-family')]
    public function loanByFamily(int $familyId, EntityManagerInterface $entityManager, Request $request): Response
    {
        //$familyName = null;


        $familyName = $request->request->get('family_name');

        if ($request->request->has('family_name')) {
            $searchedFamilies = $entityManager->getRepository(Family::class)->findAllByName($familyName);
            return $this->render('loan/index.html.twig', [
                'loans' => null,
                'currentFamily' => null,
                'searchedFamilies' => $searchedFamilies,
                'currentBook' => null,
                'currentBookLoan' => null,
                'tab' => 'family'
            ]);
            return $this->redirectToRoute('loan');
        }


        $currentFamily = $entityManager->getRepository(Family::class)->findOneById($familyId);
        $loans = $entityManager->getRepository(Loan::class)->findByFamilyId($familyId);
        return $this->render('loan/index.html.twig', [
            'loans' => $loans,
            'currentFamily' => $currentFamily,
            'searchedFamilies' => null,
            'currentBook' => null,
            'currentBookLoan' => null,
            'tab' => 'family'
        ]);
    }

    #[Route(name: 'return-book', path: '/{id}/return')]
    public function returnBook(
        int $id,
        LoanRepository $loanRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // changer LoanStatus et BookStatus
        $loan = $loanRepository->find($id);
        $book = $loan->getBook();
        $familyId = $loan->getFamily()->getId();
        if ($loan) {
            if (
                $loan->getLoanStatus() != LoanStatusEnum::returned
                && $book->getBookStatus() != BookStatusEnum::available
            ) {
                $loan->setLoanStatus(LoanStatusEnum::returned);
                $book->setBookStatus(BookStatusEnum::available);
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
