<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\QueryBuilder;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/book')]
#[IsGranted('ROLE_USER')]
final class BookController extends AbstractController
{
    #[Route('/', name: 'book')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $bookCode = $request->request->get('book_code');
        $currentBook = null;
        if ($bookCode) {
            $currentBook = $em->getRepository(Book::class)->findOneByBookCode([
                'bookCode' => $bookCode
            ]);
            // return $this->render('book/index.html.twig',[
            //     'currentBook'=>$currentBook
            // ]);
        }

        return $this->render('book/index.html.twig', [
            'currentBook' => $currentBook,
            'bookToEdit' => null,
            'bookToDelete' => null
        ]);
    }

    #[Route('/edit/{id}', name: 'edit-book')]
    public function edit(int $id, EntityManagerInterface $em): Response
    {
        $bookToEdit = $em->getRepository(Book::class)->find($id);
        dd($bookToEdit);
        return $this->render('book/index.html.twig', [
            'currentBook' => null,
            'bookToEdit' => $bookToEdit,
            'bookToDelete' => null
        ]);
    }

    #[Route('/delete/{id}', name: 'delete-book')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $bookToDelete = $em->getRepository(Book::class)->find($id);
        dd($bookToDelete);
        return $this->render('book/index.html.twig', [
            'currentBook' => null,
            'bookToEdit' => null,
            'bookToDelete' => $bookToDelete
        ]);
    }
}
