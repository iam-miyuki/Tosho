<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
    #[Route('/books', name: 'books')]
    public function bookList(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findAll(); // Récupère toutes les familles

        return $this->render('book/list.html.twig', [
            'books' => $books,
        ]);
    }
}
