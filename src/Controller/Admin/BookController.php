<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Enum\LocationEnum;
use App\Form\Book\BookForm;
use App\Enum\BookStatusEnum;
use App\Form\Book\FindBookForm;
use App\Form\Book\BookFilterForm;
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

        $sharedData = [
            'bookForm' => $form->createView(),
            'filterForm' => $filterForm->createView(),
            'findBookForm' => $findBookForm->createView(),
            'all' => $all,
            'cameleon' => $cameleon,
            'f' => $f,
            'mba' => $mba,
            'badet' => $badet,
        ];

        if ($request->isMethod('POST')) {
            if ($currentTab === 'search') {
                if ($filterForm->isSubmitted()) {
                    $keyword = $filterForm->get('filter')->getData();
                    $results = $bookRepository->findAllWithFilterQuery($keyword);
                    return $this->render('Admin/book/index.html.twig', array_merge($sharedData, [
                        'books' => $results,
                        'tab' => 'search'
                    ]));
                }
                if ($findBookForm->isSubmitted()) {
                    $code = $findBookForm->get('code')->getData();
                    $currentBook = $bookRepository->findOneByCode($code);
                    return $this->redirectToRoute('admin-show-book', [
                        'id' => $currentBook->getId()
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
                    return $this->render('Admin/book/index.html.twig', array_merge($sharedData, [
                        'addedBook' => $book,
                        'tab' => 'new',
                        'successMessage' => 'Le livre a été ajouté avec succès'
                    ]));
                }
            }
        }
        return $this->render(
            'Admin/book/index.html.twig',
            array_merge($sharedData, [
                'tab' => $currentTab,
                'books' => $results,
                'currentBook' => $currentBook,
                'addedBook' => null
            ])
        );
    }

    #[Route('/{id}', name: 'admin-show-book')]
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
        Book $book,
        Request $request,
        EntityManagerInterface $em,
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


        $form = $this->createForm(BookForm::class, $book);
        $form->handleRequest($request);

        $sharedData = [
            'bookForm' => $form->createView(),
            'filterForm' => $filterForm->createView(),
            'findBookForm' => $findBookForm->createView(),
            'all' => $all,
            'cameleon' => $cameleon,
            'f' => $f,
            'mba' => $mba,
            'badet' => $badet,
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->render(
                'Admin/book/index.html.twig',
                array_merge($sharedData, [
                    'modifiedBook' => $book,
                    'tab' => 'search',
                    'successMessage'=>'Le livre a été modifié avec succès'
                ])
            );
        }
        return $this->render(
            'Admin/book/index.html.twig',
            array_merge($sharedData, [
                'bookToEdit' => $book,
                'tab' => 'search'
            ])
        );
    }

    #[Route('/delete/{id}', name: 'delete-book')]
    public function delete(
        Book $book,
        EntityManagerInterface $em,
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

        $sharedData = [
            'tab' => 'search',
            'filterForm' => $filterForm->createView(),
            'findBookForm' => $findBookForm->createView(),
            'all' => $all,
            'cameleon' => $cameleon,
            'f' => $f,
            'mba' => $mba,
            'badet' => $badet,
        ];

        if ($book->getStatus() !== BookStatusEnum::available) {
            return $this->render(
                'Admin/book/index.html.twig',
                array_merge(
                    $sharedData,
                    [
                        'loanBook' => $book,
                    ]
                )
            );
        }

        if ($request->isMethod('POST')) {
            $em->remove($book);
            $em->flush();

            return $this->render(
                'Admin/book/index.html.twig',
                array_merge(
                    $sharedData,
                    [
                        'deletedBook' => $book,
                        'successMessage'=>'Le livre a été supprimé avec succès'
                    ]
                )
            );
        }

        return $this->render(
            'Admin/book/index.html.twig',
            array_merge(
                $sharedData,
                [
                    'bookToDelete' => $book,
                ]
            )
        );
    }
}
