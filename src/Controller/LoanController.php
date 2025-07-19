<?php

namespace App\Controller;

use DateTime;
use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Family;
use App\Enum\BookStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\FamilyRepository;
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

        $tab = $request->query->get('tab', 'family');

        if ($request->query->get('book_id')) {
            $bookId = $request->query->get('book_id');
            $book = $bookRepository->find($bookId);
            $status = $book->getStatus();

                if ($status != BookStatusEnum::available) {
                    $loan = $loanRepository->findWithBookAndStatus($book);
                    return $this->render('loan/index.html.twig', [
                        'families' => null,
                        'currentFamily' => null,
                        'loans' => null,
                        'currentBook' => $book,
                        'currentLoan' => $loan,
                        'tab' => 'book',

                    ]);
                } else {
                    return $this->render('loan/index.html.twig', [
                        'families' => null,
                        'currentFamily' => null,
                        'loans' => null,
                        'currentBook' => $book,
                        'currentLoan' => null,
                        'tab' => 'book',
                    ]);
                }
        }
        if ($request->isMethod('POST')) {
            if ($request->request->has('family_name')) {
                $name = $request->request->get('family_name');
                $results = $familyRepository->findAllByName($name);

                return $this->render('loan/index.html.twig', [
                    'families' => $results,
                    'currentFamily' => null,
                    'loans' => null,
                    'currentBook' => null,
                    'currentLoan' => null,
                    'tab' => 'family'
                ]);
            }
            if ($request->request->has('book_code')) {
                $code = $request->request->get('book_code');
                $book = $bookRepository->findOneByCode($code);
                $status = $book->getStatus();

                if ($status != BookStatusEnum::available) {
                    $loan = $loanRepository->findWithBookAndStatus($book);
                    return $this->render('loan/index.html.twig', [
                        'families' => null,
                        'currentFamily' => null,
                        'loans' => null,
                        'currentBook' => $book,
                        'currentLoan' => $loan,
                        'tab' => 'book',

                    ]);
                } else {
                    return $this->render('loan/index.html.twig', [
                        'families' => null,
                        'currentFamily' => null,
                        'loans' => null,
                        'currentBook' => $book,
                        'currentLoan' => null,
                        'tab' => 'book',
                    ]);
                }
            }
            if ($request->request->has('keyword')) {
                $keyword = $request->request->get('keyword');
                $books = $bookRepository->findAllWithFilterQuery($keyword);
                return $this->render('loan/index.html.twig', [
                    'families' => null,
                    'currentFamily' => null,
                    'loans' => null,
                    'books' => $books,
                    'currentBook' => null,
                    'currentLoan' => null,
                    'tab' => 'book',
                ]);
            }
        }
        return $this->render('loan/index.html.twig', [
            'families' => null,
            'currentFamily' => null,
            'loans' => null,
            'currentBook' => null,
            'currentBookLoan' => null,

            'tab' => $tab
        ]);
    }

    #[Route(path: '/family', name: 'loan-by-family')]
    public function loanByFamily(
        Request $request,
        FamilyRepository $familyRepository,
        LoanRepository $loanRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {

        $familyId = $request->query->get('id');
        $family = $familyRepository->find($familyId);
        $loans = $loanRepository->findAllWithFamilyAndStatus($family);

        // prêter un livre
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

        return $this->render('loan/index.html.twig', [
            'loans' => $loans,
            'currentFamily' => $family,
            'tab' => 'family'
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
