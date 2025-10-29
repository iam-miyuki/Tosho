<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Enum\LocationEnum;
use App\Form\Book\BookForm;
use App\Enum\BookStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Form\Book\FindBookForm;
use App\Form\Book\BookFilterForm;
use App\Form\Book\EditBookForm;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
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
    private function generateUniqueCode(BookRepository $bookRepository): string
    {
        do {
            $code = (string) random_int(1000, 9999);
            $existingBook = $bookRepository->findOneByCode($code);
        } while ($existingBook !== null); //pour générer le code qui n'est pas encore attribué à un livre
        return $code;
    }

    #[Route('/', name: 'book')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        BookRepository $bookRepository,
        LoanRepository $loanRepository
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

        $filterForm = $this->createForm(BookFilterForm::class, null);
        $filterForm->handleRequest($request);

        $findBookForm = $this->createForm(FindBookForm::class, null);
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

        if (!$request->isMethod('POST')) {
            return $this->render(
                'admin/book/index.html.twig',
                array_merge($sharedData, [
                    'tab' => $currentTab,
                    'books' => $results,
                    'currentBook' => $currentBook,
                    'addedBook' => null,
                   ])
            );
        }

        if ($filterForm->isSubmitted()) {
            $keyword = $filterForm->get('filter')->getData();
            $results = $bookRepository->findAllWithFilterQuery($keyword);
            return $this->render('admin/book/index.html.twig', array_merge($sharedData, [
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

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $book->setAddedAt(new \DateTimeImmutable());
            $book->setStatus(BookStatusEnum::available);
            $code = $this->generateUniqueCode($bookRepository);
            $book->setCode($code);
            $em->persist($book);
            $em->flush();
            return $this->render('admin/book/index.html.twig', array_merge($sharedData, [
                'addedBook' => $book,
                'tab' => 'new',
                'successMessage' => 'Le livre a été ajouté avec succès'
            ]));
        }
        return new Response('500 Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
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

        return $this->render('admin/book/index.html.twig', [
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

        $editForm = $this->createForm(EditBookForm::class, $book);
        $editForm->handleRequest($request);

        $sharedData = [
            'bookForm' => $editForm->createView(),
            'filterForm' => $filterForm->createView(),
            'findBookForm' => $findBookForm->createView(),
            'all' => $all,
            'cameleon' => $cameleon,
            'f' => $f,
            'mba' => $mba,
            'badet' => $badet,
        ];

        if ($request->isMethod('POST')) {
            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $code = $editForm->get('code')->getData();
                $existingBook = $bookRepository->findOneByCode($code);

                if ($existingBook == null || $existingBook->getId() === $book->getId()) {
                    $em->flush();
                    return $this->render(
                        'admin/book/index.html.twig',
                        array_merge($sharedData, [
                            'modifiedBook' => $book,
                            'tab' => 'search',
                            'successMessage' => 'Le livre a été modifié avec succès'
                        ])
                    );
                } else {
                    return $this->render(
                        'admin/book/index.html.twig',
                        array_merge($sharedData, [
                            'tab' => 'search',
                            'erreurCodeBook' => $book
                        ])
                    );
                }
            }
        }
        return $this->render(
            'admin/book/index.html.twig',
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
                'admin/book/index.html.twig',
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
                'admin/book/index.html.twig',
                array_merge(
                    $sharedData,
                    [
                        'deletedBook' => $book,
                        'successMessage' => 'Le livre a été supprimé avec succès'
                    ]
                )
            );
        }

        return $this->render(
            'admin/book/index.html.twig',
            array_merge(
                $sharedData,
                [
                    'bookToDelete' => $book,
                ]
            )
        );
    }
}
