<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $bookCode = $request->request->get('book_code');
        $currentBook = null;
        if($bookCode)
        {
            $currentBook = $em->getRepository(Book::class)->findOneByBookCode([
                'bookCode'=>$bookCode
            ]);
            // return $this->render('book/index.html.twig',[
            //     'currentBook'=>$currentBook
            // ]);
        }

        return $this->render('book/index.html.twig', [
            'currentBook' => $currentBook,
            'bookToModify'=>null,
            'bookToDelete'=>null
        ]);
    }
    
    #[Route('/book/modify/{id}', name:'modify-book')]
    public function modify(int $id, EntityManagerInterface $em) : Response
    {
        $bookToModify = $em->getRepository(Book::class)->find($id);
        dd($bookToModify);
        return $this->render('book/index.html.twig',[
            'currentBook'=>null,
            'bookToModify'=>$bookToModify,
            'bookToDelete'=>null
        ]);
    }

    #[Route('/book/delete/{id}', name:'delete-book')]
    public function delete(int $id, EntityManagerInterface $em) : Response
    {
        $bookToDelete = $em->getRepository(Book::class)->find($id);
        dd($bookToDelete);
        return $this->render('book/index.html.twig',[
            'currentBook'=>null,
            'bookToModify'=>null,
            'bookToDelete'=>$bookToDelete
        ]);
    }
}
