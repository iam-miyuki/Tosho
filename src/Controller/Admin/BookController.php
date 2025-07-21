<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Enum\BookStatusEnum;
use App\Form\BookFilterForm;
use App\Form\BookForm;
use App\Form\FindBookForm;
use Doctrine\ORM\QueryBuilder;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/book')]
#[IsGranted('ROLE_ADMIN')]
final class BookController extends AbstractController
{
    #[Route('/', name: 'book')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        BookRepository $bookRepository
    ): Response {
        $currentTab = $request->query->get('tab', 'search');
        $book = new Book;
        $form = $this->createForm(BookForm::class, $book);
        $form->handleRequest($request);
        $currentBook = null;

        $filterForm = $this->createForm(BookFilterForm::class, $book);
        $filterForm->handleRequest($request);

        $findBookForm = $this->createForm(FindBookForm::class, $book);
        $findBookForm->handleRequest($request);

        $results = null;

        if ($request->isMethod('POST')) {
            if ($currentTab === 'search') {
                if ($filterForm->isSubmitted()) {
                    $keyword = $filterForm->get('filter')->getData();
                    $results = $bookRepository->findAllWithFilterQuery($keyword);
                    dd($results);
                }
                if ($findBookForm->isSubmitted()){
                    $code = $findBookForm->get('code')->getData();
                    $currentBook = $bookRepository->findOneByCode($code);
                    dd($currentBook);
                }
            }
            if ($currentTab === 'new') {
                if ($form->isSubmitted() && $form->isValid()) {
                    $book = $form->getData();
                    $book->setAddedAt(new \DateTimeImmutable());
                    $book->setStatus(BookStatusEnum::available);
                    // TODO : $book->setBookCode();
                    $em->persist($book);
                    $em->flush();
                    dd('enregistré !');
                }
            }
        }
        return $this->render('Admin/book/index.html.twig', [
            'tab' => $currentTab,
            'results'=> $results,
            'currentBook'=>$currentBook,
            'bookForm' => $form->createView(),
            'filterForm' => $filterForm->createView(),
            'findBookForm'=>$findBookForm->createView()
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
        return $this->render('Admin/book/index.html.twig', [
            'currentBook' => null,
            'bookToEdit' => null,
            'bookToDelete' => $bookToDelete
        ]);
    }
}
