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
        $loans = [];
        $book = null;
        if (($request->getMethod() === 'POST')&& ($request->request->has('family_name'))) {
                $loans = $loanRepository->findByFamily($request->request->get("family_name"));
                return $this->render('loan/list.html.twig',[
                    'loans'=>$loans
                ]);
            }
        if (($request->getMethod() === 'POST')&&($request->request->has('book_code'))) {
                $book = $loanRepository->findByBookCode($request->request->get("book_code"));
                return $this->render('loan/book.html.twig', [
                    'book'=>$book
                ]);
            }
        return $this->render('loan/index.html.twig');
    }    
}



// final class LoanController extends AbstractController
// {
//     #[Route('/loan', name: 'loan')]
//     public function index(): Response
//     {
//         return $this->render('loan/index.html.twig');
//     }

//     #[Route('/loan/list', name: 'loan_list')]
//     public function list(Request $request, LoanRepository $loanRepository): Response
//     {
//         $loans = new ArrayCollection();
//         if (($request->getMethod() === 'POST')&& ($request->request->has('family_name'))) {
//                 $loans = $loanRepository->findByFamily($request->request->get("family_name"));
//             }
        
//         return $this->render('loan/list.html.twig',[
//             'loans'=>$loans
//         ]);
//     }

//     #[Route('/loan/book', name: 'loan_book')]
//     public function showBook(Request $request,LoanRepository $loanRepository): Response
//     {
//         $book = null;
//         if (($request->getMethod() === 'POST')&&($request->request->has('book_code'))) {
            
//                 $book = $loanRepository->findByBookCode($request->request->get("book_code"));
            
//             }
//             return $this->render('loan/book.html.twig', [
//                 'book' => $book
//             ]);
//     }

    
// }
