<?php

namespace App\Controller;

use App\Entity\InventoryItem;
use App\Enum\InventoryItemStatusEnum;
use App\Enum\InventoryStatusEnum;
use App\Enum\LocationEnum;
use App\Form\InventoryItemForm;
use App\Repository\BookRepository;
use App\Repository\InventoryItemRepository;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/inventory/item')]
#[IsGranted('ROLE_USER')]
final class InventoryItemController extends AbstractController
{
    #[Route('/', name: 'inventory-item')]
    public function index(
        Request $request,
        InventoryRepository $inventoryRepository,
    ): Response {
        $inventories = $inventoryRepository->findAllByStatus(InventoryStatusEnum::open);
        $inventoryId = $request->query->get('id');

        if ($inventoryId) {
            return $this->redirectToRoute('new-item', [
                'id' => $inventoryId
            ]);
        }

        return $this->render('inventory_item/index.html.twig', [
            'inventories' => $inventories
        ]);
    }

    #[Route('/search', name: 'search-item')]
    public function search(
        Request $request,
        BookRepository $bookRepository,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository
    ): Response {

        $tab = $request->query->get('tab', 'new');
        $inventoryId = $request->query->get('id');
        $inventory = $inventoryRepository->find($inventoryId);
        $location = $inventory->getLocation();
        $allBooks = $bookRepository->findAllByLocation($location);
        $checkedBooks = $inventoryItemRepository->findAllByInventory($inventory);
       
        $okItems = $inventoryItemRepository->findAllByInventoryAndStatus(
            $inventory,
            InventoryItemStatusEnum::ok
        );
        $badLocations = $inventoryItemRepository->findAllByInventoryAndStatus(
            $inventory,
            InventoryItemStatusEnum::badLocation
        );

        $notFounds = $inventoryItemRepository->findAllByInventoryAndStatus(
            $inventory,
            InventoryItemStatusEnum::notFound
        );
        $others = $inventoryItemRepository->findAllByInventoryAndStatus(
            $inventory,
            InventoryItemStatusEnum::other
        );

        $inventoryItem = new InventoryItem();
        $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $itemForm->handleRequest($request);

        $currentBook = null;
        $query = null;

        if ($request->isMethod('POST') && $request->request->has('book_code')) {
            $code = $request->request->get('book_code');
            $currentBook = $bookRepository->findOneByCode($code);
            // vérifier si $currentBook a déjà été ajouté dans cette session
            $query = $inventoryItemRepository->findOneByInventoryAndBook($inventory, $currentBook);
            if ($query === null) {

                return $this->redirectToRoute('add-item', [
                    'id' => $inventoryId,
                    'book' => $currentBook->getId(),
                ]);
            } else {

                return $this->redirectToRoute('edit-item', [
                    'id' => $query->getId(),
                ]);
            }
        }
        return $this->render('inventory_item/tabs.html.twig', [
            'currentInventory' => $inventory,
            'currentBook' => $currentBook,
            'query' => $query,
            'total' => $allBooks,
            'checked' => $checkedBooks,
            'okItems' => $okItems,
            'badLocations' => $badLocations,
            'notFounds' => $notFounds,
            'others' => $others,
            'tab' => $tab,
            'itemForm' => $itemForm->createView()
        ]);
    }

    #[Route('/add', name: 'add-item')]
    public function add(
        Request $request,
        InventoryRepository $inventoryRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $inventoryId = $request->query->get('id');
        $inventory = $inventoryRepository->find($inventoryId);
        $currentBook = $bookRepository->find($request->query->get('book'));

        $inventoryItem = new InventoryItem();
        $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            $inventoryItem = $itemForm->getData();
            $inventoryItem->setBook($currentBook);
            $inventoryItem->setInventory($inventory);
            // TODO : $inventoryItem->setUser($this->getUser());
            $em->persist($inventoryItem);
            $em->flush();
            return $this->redirectToRoute('search-item', [
                'id' => $inventoryId
            ]);
        }
        return $this->render('inventory_item/tabs.html.twig', [
            'currentInventory' => $inventory,
            'currentBook' => $currentBook,
            'itemForm' => $itemForm,
            'tab' => 'search'
        ]);
    }

    #[Route('/edit', name: 'edit-item')]
    public function edit(
        Request $request,
        InventoryItemRepository $inventoryItemRepository,
        EntityManagerInterface $em
    ): Response {
        $inventoryItem = $inventoryItemRepository->find($request->query->get('id'));

        $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'L’inventaire a été mis à jour.');
            return $this->redirectToRoute('search-item', [
                'id' => $inventoryItem->getInventory()->getId()
            ]);
        }

        return $this->render('inventory_item/tabs.html.twig', [
            'itemForm' => $itemForm,
            'currentBook' => $inventoryItem->getBook(),
            'currentInventory' => $inventoryItem->getInventory(),
            'tab' => 'search'
        ]);
    }

    #[Route('/list', name: 'item-list')]
    public function listt(
        Request $request,
        PaginatorInterface $paginator,
        BookRepository $bookRepository,
        InventoryRepository $inventoryRepository
    ): Response {
        $inventoryId = $request->query->get('id');
        $currentInventory = $inventoryRepository->find($inventoryId);
        $query = $bookRepository->findAllByLocationWithPagination(LocationEnum::cameleon);
        
        $results = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1)
        );
        
        return $this->render('inventory_item/books.html.twig', [
            'pagination' => $results,
            'currentInventory'=>$currentInventory,
        ]);
    }
}
