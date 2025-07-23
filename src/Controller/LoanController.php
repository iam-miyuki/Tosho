<?php

namespace App\Controller;

use DateTime;
use App\Entity\Loan;
use App\Enum\BookStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
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
        BookRepository $bookRepository
    ): Response {

        $tab = $request->query->get('tab', 'family');
        $currentFamily = null;
        $results = null;
        $currentBook = null;
        $books = null;
        $currentLoan = null;

        if ($request->isMethod('POST')) {

            // chercher par famille
            if ($request->request->has('family_name')) {
                $name = $request->request->get('family_name');
                $results = $familyRepository->findAllByName($name);
                if ($results) {
                    return $this->render('loan/index.html.twig', [
                        'families' => $results,
                        'tab' => 'family'
                    ]);
                } else {
                    dd('aucune famille trouvé !');
                }
            }
            // chercher par livre avec code
            if ($request->request->has('book_code')) {
                $code = $request->request->get('book_code');
                $book = $bookRepository->findOneByCode($code);
                if ($book) {
                    return $this->redirectToRoute('loan-by-book', [
                        'id' => $book->getId()
                    ]);
                } else {
                    dd('aucun livre trouvé !');
                }
            }
            // chercher par livre avec mot-clé
            if ($request->request->has('keyword')) {
                $keyword = $request->request->get('keyword');
                $books = $bookRepository->findAllWithFilterQuery($keyword);
                if ($books) {
                    return $this->render('loan/index.html.twig', [
                        'books' => $books,
                        'tab' => 'book'
                    ]);
                } else {
                    dd('aucun livre trouvé !');
                }
            }
        }
        if ($request->query->has('book')) {
            $bookId = $request->query->get('book');
            $loanFamilies = null;
            if ($request->request->has('loan_family')) {
                $name = $request->request->get('loan_family');
                $loanFamilies = $familyRepository->findAllByName($name);
            }
            return $this->render('loan/index.html.twig', [
                'bookId' => $bookId,
                'tab' => 'family',
                'loanFamilies' => $loanFamilies
            ]);
        }

        return $this->render('loan/index.html.twig', [
            'tab' => $tab,
            'currentFamily' => $currentFamily,
            'families' => $results,
            'currentBook' => $currentBook,
            'currentLoan' => $currentLoan,
            'books' => $books
        ]);
    }

    #[Route(path: '/family', name: 'loan-by-family')]
    public function family(
        Request $request,
        FamilyRepository $familyRepository,
        LoanRepository $loanRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {

        $familyId = $request->query->get('id');
        $family = $familyRepository->find($familyId);
        $loans = $loanRepository->findAllWithFamilyAndStatus($family);

        if ($request->isMethod('POST')) {
            if ($request->request->has('book_code')) {
                $code = $request->request->get('book_code');
                $book = $bookRepository->findOneByCode($code);

                if ($family && $book && $book->getStatus() != BookStatusEnum::borrowed) {

                    $loan = new Loan;
                    $loan->setFamily($family);
                    $loan->setBook($book);
                    $loan->setStatus(LoanStatusEnum::inProgress);
                    $loan->setLoanDate(new \DateTime());
                    $loan->setUser($this->getUser());
                    $book->setStatus(BookStatusEnum::borrowed);
                    $em->persist($loan);
                    $em->persist($book);
                    $em->flush();
                    return $this->redirectToRoute('loan-by-family', [
                        'id' => $familyId,
                    ]);
                }
                if ($family && $book && $book->getStatus() === BookStatusEnum::borrowed) {
                    dd('ce livre est déjà emprunté !');
                } else {
                    dd('aucun livre trouvé !');
                }
            }
        }

        if ($request->query->has('id') && $request->query->has('book')) {
            $familyId = $request->query->get('id');
            $bookId = $request->query->get('book');
            $book = $bookRepository->find($bookId);
            $family = $familyRepository->find($family);
            if ($book && $family) {
                $loan = new Loan;
                $loan->setFamily($family);
                $loan->setBook($book);
                $loan->setStatus(LoanStatusEnum::inProgress);
                $loan->setLoanDate(new \DateTime());
                $loan->setUser($this->getUser());
                $book->setStatus(BookStatusEnum::borrowed);
                $em->persist($loan);
                $em->persist($book);
                $em->flush();
                return $this->redirectToRoute('loan-by-family', [
                    'id' => $familyId,
                ]);
            }
        }

        return $this->render('loan/index.html.twig', [
            'loans' => $loans,
            'currentFamily' => $family,
            'tab' => 'family'
        ]);
    }

    #[Route(path: '/book', name: 'loan-by-book')]
    public function book(
        Request $request,
        LoanRepository $loanRepository,
        BookRepository $bookRepository
    ): Response {
        $bookId = $request->query->get('id');
        $book = $bookRepository->find($bookId);
        $status = $book->getStatus();
        $loan = null;
        if ($status != BookStatusEnum::available) {
            $loan = $loanRepository->findWithBookAndStatus($book);
        }
        return $this->render('loan/index.html.twig', [
            'tab' => 'book',
            'currentBook' => $book,
            'currentLoan' => $loan
        ]);
    }

    #[Route(name: 'return-book', path: '/return')]
    public function returnBook(
        Request $request,
        LoanRepository $loanRepository,
        EntityManagerInterface $em
    ): Response {
        // changer LoanStatus et status
        $idLoan = $request->query->get('id');
        $loan = $loanRepository->find($idLoan);
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
                $loan->setUser($this->getUser());
                $em->persist($loan);
                $em->persist($book);
                $em->flush();
                return $this->redirectToRoute('loan-by-family', [
                    'id' => $familyId
                ]);
            } else {
                // TODO 
                dd('déjà rendu !');
            }
        } else {
            dd('non trouvé!');
        }
    }
}
