<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Family;
use DateTime;
use App\Entity\Loan;
use App\Enum\BookStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// #[Route(
//     path: '/{_locale}/loan',
//     requirements: [
//         '_locale' => 'en|fr',
//     ],
// )]
#[Route(path: '/loan')]
#[IsGranted('ROLE_USER')]
final class LoanController extends AbstractController
{
    #[Route('/', name: 'loan')]
    public function index(
        Request $request,
        LoanRepository $loanRepository,
        FamilyRepository $familyRepository,
        BookRepository $bookRepository
    ): Response {

        $activeLoans = $loanRepository->findAllByStatus(LoanStatusEnum::inProgress);
        $overdueLoans = $loanRepository->findAllByStatus(LoanStatusEnum::overdue);
        $tab = $request->query->get('tab', 'family');
        $books = null;
        $results = null;

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
                } 
                else {
                    echo('aucune famille trouvé !');
                }
            }
            // chercher par livre avec code
            if ($request->request->has('book_code')) {
                $code = $request->request->get('book_code');
                $book = $bookRepository->findOneByCode($code);
                if ($book) {
                    return $this->redirectToRoute('show-book', [
                        'id' => $book->getId()
                    ]);
                } else {
                    echo('aucun livre trouvé !');
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
                    echo('aucun livre trouvé !');
                }
            }
        }

        return $this->render('loan/index.html.twig', [
            'tab' => $tab,
            'books' => $books,
            'families' => $results,
        ]);
    }

    #[Route(path: '/book/{id}', name: 'show-book')]
    public function book(
        Book $book,
        LoanRepository $loanRepository
    ): Response {
        $loan = $loanRepository->findWithBookAndStatus($book);

        return $this->render('loan/index.html.twig', [
            'loan' => $loan,
            'book' => $book,
            'tab' => 'book',
        ]);
    }

    #[Route(path: '/loan-book/{id}', name: 'loan-book')]
    public function loanBook(
        Book $book,
        Request $request,
        FamilyRepository $familyRepository
    ): Response {
        $results = null;
        if ($request->isMethod('POST')) {
            // chercher par famille
            if ($request->request->has('loan_family')) {
                $name = $request->request->get('loan_family');
                $results = $familyRepository->findAllByName($name);
                if ($results) {
                    return $this->render('loan/index.html.twig', [
                        'searchedFamilies' => $results,
                        'bookToLoan' => $book,
                        'tab' => 'book'
                    ]);
                } else {
                    echo('aucune famille trouvé !');
                }
            }
        }
        return $this->render('loan/index.html.twig', [
            'bookToLoan' => $book,
            'searchedFamilies' => $results,
            'tab' => 'book',
        ]);
    }

    #[Route(path: '/loan-book/{id}/{family}', name: 'loan-book-family')]
    public function loanBookFamily(
        Book $book,
        Family $family,
        EntityManagerInterface $em
    ): Response {
        $loan = new Loan();
        $loan->setFamily($family);
        $loan->setBook($book);
        $loan->setUser($this->getUser());
        $loan->setStatus(LoanStatusEnum::inProgress);
        $loan->setLoanDate(new \DateTime());
        $book->setStatus(BookStatusEnum::borrowed);
        $em->persist($loan);
        $em->persist($book);
        $em->flush();
        return $this->redirectToRoute('loan-by-family',[
            'id'=>$family->getId()
        ]);
    }

    #[Route(path: '/family/{id}', name: 'loan-by-family')]
    public function family(
        Family $family,
        Request $request,
        LoanRepository $loanRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $loans = $loanRepository->findAllWithFamilyAndStatus($family);

        if ($request->isMethod('POST')) {
            if ($request->request->has('book_code')) {
                $code = $request->request->get('book_code');
                $book = $bookRepository->findOneByCode($code);

                if ($family && $book && $book->getStatus() != BookStatusEnum::borrowed) {
                    $loan = new Loan();
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
                        'id' => $family->getId(),
                    ]);
                }
                if ($family && $book && $book->getStatus() === BookStatusEnum::borrowed) {
                    // TODO 
                    echo('ce livre est déjà emprunté !');
                } else {
                    echo('aucun livre trouvé !');
                }
            }
        }

        return $this->render('loan/index.html.twig', [
            'loans' => $loans,
            'family' => $family,
            'tab' => 'family'
        ]);
    }

    #[Route(path: '/return/{id}', name: 'return-book')]
    public function returnBook(
        Loan $loan,
        EntityManagerInterface $em
    ): Response {
        $book = $loan->getBook();
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
                    'id' => $loan->getFamily()->getId()
                ]);
            } else {
                echo('déjà rendu !');
            }
        } else {
            echo('non trouvé!');
        }
        return $this->render('loan/index.html.twig',[
            'loan'=>$loan,
            ''
        ]);
    }
}
