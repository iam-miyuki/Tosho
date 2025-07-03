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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class LoanController extends AbstractController
{
    #[Route('/loan', name: 'loan')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $familyName = $request->request->get('family_name');
        $searchedFamily = null;
        $bookCode = $request->request->get('book_code');
        $currentBook = null;
        $currentBookLoan = null;
        if ($request->getMethod() === 'POST') {
            if ($request->request->has('family_name')) {
                $searchedFamily = $entityManager->getRepository(Family::class)->findAllByName($familyName);
                return $this->render('loan/index.html.twig', [
                    'searchedFamily' => $searchedFamily,
                    'currentFamily' => null,
                    'loans' => null,
                    'tab' => 'family'
                ]);
            }
            if ($request->request->has('book_code')) {
                $currentBook = $entityManager->getRepository(Book::class)->findOneBy([
                    'bookCode' => $bookCode
                ]);
                $currentBookLoan = $entityManager->getRepository(Loan::class)->findOneBy([
                    'loanStatus' => LoanStatusEnum::inProgress,
                    'book' => $currentBook
                ]);
                return $this->render('loan/index.html.twig', [
                    'searchedFamily' => [],
                    'currentFamily' => null,
                    'loans' => null,
                    'currentBook' => $currentBook,
                    'currentBookLoan' => $currentBookLoan,
                    'tab' => 'book'
        
                ]);
            }
        }
        return $this->render('loan/index.html.twig', [
            'searchedFamily' => $searchedFamily,
            'currentFamily' => null,
            'loans' => null,
            'currentBook' => $currentBook,
            'currentBookLoan' => $currentBookLoan,
            'tab' => 'family'

        ]);
    }

    #[Route('/loan/new', name: 'new-loan')]
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
                } else {
                    dd('ce livre est déjà emprunté !');
                }
            } else {
                dd('champs de l\'id livre est obligatoire !');
            }
        }
        return $this->redirectToRoute('loan');
    }


    #[Route(name: 'return-book', path: '/loan/{id}/return')]
    public function returnBook(
        int $id,
        LoanRepository $loanRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // changer LoanStatus et BookStatus
        $loan = $loanRepository->find($id);
        $book = $loan->getBook();
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
                dd('ok');
            } else {
                dd('déjà rendu!');
            }
        } else {
            dd('non trouvé!');
        }
    }

    #[Route(path: '/loan/{familyId}/list', name: 'loan-by-family')]
    public function loanByFamily(int $familyId, EntityManagerInterface $entityManager): Response
    {
        $currentFamily = $entityManager->getRepository(Family::class)->findOneById($familyId);
        $loans = $entityManager->getRepository(Loan::class)->findByFamilyId($familyId);
        return $this->render('loan/index.html.twig', [
            'loans' => $loans,
            'currentFamily' => $currentFamily,
            'searchedFamily' => null,
            'currentBook' => null,
            'currentBookLoan' => null
        ]);
    }
}
