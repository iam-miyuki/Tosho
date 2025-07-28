<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Enum\BookStatusEnum;
use App\Enum\LocationEnum;
use App\Form\BookFilterForm;
use App\Form\BookForm;
use App\Form\FindBookForm;
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
        $book = new Book();
        $form = $this->createForm(BookForm::class, $book);
        $form->handleRequest($request);
        $currentBook = null;

        $all = $bookRepository->findAll();
        $cameleon = $bookRepository->findAllByLocation(LocationEnum::cameleon);
        $f = $bookRepository->findAllByLocation(LocationEnum::f);
        $mba = $bookRepository->findAllByLocation(LocationEnum::mba);
        $badet = $bookRepository->findAllByLocation(LocationEnum::badet);

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
                    return $this->render('Admin/book/index.html.twig', [
                        'books' => $results,
                        'filterForm' => $filterForm->createView(),
                        'findBookForm' => $findBookForm->createView(),
                        'tab' => 'search',
                        'all' => $all,
                        'cameleon' => $cameleon,
                        'f' => $f,
                        'mba' => $mba,
                        'badet' => $badet
                    ]);
                }
                if ($findBookForm->isSubmitted()) {
                    $code = $findBookForm->get('code')->getData();
                    $currentBook = $bookRepository->findOneByCode($code);
                    return $this->render('Admin/book/index.html.twig', [
                        'currentBook' => $currentBook,
                        'filterForm' => $filterForm->createView(),
                        'findBookForm' => $findBookForm->createView(),
                        'tab' => 'search',
                        'all' => $all,
                        'cameleon' => $cameleon,
                        'f' => $f,
                        'mba' => $mba,
                        'badet' => $badet
                    ]);
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
                    return $this->redirectToRoute('book');
                }
            }
        }
        

        return $this->render('Admin/book/index.html.twig', [
            'tab' => $currentTab,
            'books' => $results,
            'currentBook' => $currentBook,
            'bookForm' => $form->createView(),
            'filterForm' => $filterForm->createView(),
            'findBookForm' => $findBookForm->createView(),
            'all' => $all,
            'cameleon' => $cameleon,
            'f' => $f,
            'mba' => $mba,
            'badet' => $badet
        ]);
    }

     #[Route('/{id}', name: 'show-book')]
    public function show(
        Book $book,
        Request $request,
        BookRepository $bookRepository
    ): Response {
        
        $filterForm = $this->createForm(BookFilterForm::class, $book);
        $filterForm->handleRequest($request);

        $findBookForm = $this->createForm(FindBookForm::class, $book);
        $findBookForm->handleRequest($request);

        $all = $bookRepository->findAll();
        $cameleon = $bookRepository->findAllByLocation(LocationEnum::cameleon);
        $f = $bookRepository->findAllByLocation(LocationEnum::f);
        $mba = $bookRepository->findAllByLocation(LocationEnum::mba);
        $badet = $bookRepository->findAllByLocation(LocationEnum::badet);

            return $this->render('Admin/book/index.html.twig', [
                'currentBook' => $book,
                'tab' => 'search',
                'filterForm' => $filterForm->createView(),
                'findBookForm' => $findBookForm->createView(),
                'all' => $all,
                'cameleon' => $cameleon,
                'f' => $f,
                'mba' => $mba,
                'badet' => $badet
            ]);
        }

        #[Route('/edit/{id}', name: 'edit-book')]
        public function edit(
            int $id,
            Request $request,
            EntityManagerInterface $em
        ): Response {
    
    
    
            $book = $em->getRepository(Book::class)->find($id);
            $form = $this->createForm(BookForm::class, $book);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();
                dd('modifié !');
                return $this->redirectToRoute('book', [
                    'id' => $id,
                    'tab' => 'search'
    
                ]);
            }
            return $this->render('Admin/book/edit.html.twig', [
                'book' => $book,
                'tab' => 'search',
                'bookForm' => $form->createView(),
    
            ]);
        }
    
        #[Route('/delete/{id}', name: 'delete-book')]
        public function delete(int $id, EntityManagerInterface $em): Response
        {
            $book = $em->getRepository(Book::class)->find($id);
            if ($book) {
                $em->remove($book);
                $em->flush();
                dd('supprimé !');
            }
            return $this->render('Admin/book/delete.html.twig', [
                'book' => $book,
                'tab' => 'search'
            ]);
        }
    }


