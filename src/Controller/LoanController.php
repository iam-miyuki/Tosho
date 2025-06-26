<?php

namespace App\Controller;

use App\Repository\LoanRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class LoanController extends AbstractController
{
    #[Route('/loan', name: 'loan')]
    public function index(Request $request, LoanRepository $loanRepository): Response
    {
        $loans=null;
        if ($request->getMethod() === 'POST') {
            if ($request->request->has('family_name')) {
                $loans = $loanRepository->findByFamily($request->request->get('family_name'));
                
            }
        }
        // dd($loans);
        return $this->render('loan/index.html.twig', [
            'loans' => $loans
        ]);
    }

    // #[Route('/loan/list', name: 'loan_list')]
    // public function list(): Response
    // {
    // }

    #[Route('/loan/book', name: 'loan_book')]
    public function showBook(Request $request, LoanRepository $loanRepository): Response
    {
        if ($request->getMethod() === 'POST') {
            $book = null;
            if ($request->request->has('book_code')) {
                $book = $loanRepository->findByBookCode($request->request->get('book_code'));
                return $this->render('loan/book.html.twig', [
                    'book' => $book
                ]);
            }
        }
        return $this->render('loan/index.html.twig');
    }
}
