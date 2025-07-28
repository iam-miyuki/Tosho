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

        $id = $request->query->get('inventory');
        $inventory = $inventoryRepository->findWithItems($id); // besoin de récupérer avec inventoryItems
        $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);

        $location = $inventory->getLocation();
        $noCheckedBooks = $bookRepository->findNoInventory($id, $location);
        $allBooksByLocation = $bookRepository->findAllByLocation($location);

        $currentBook = null;
        $query = null;

        if ($request->isMethod('POST') && $request->request->has('book_code')) {
            $code = $request->request->get('book_code');
            $currentBook = $bookRepository->findOneByCode($code);

            // vérifier si $currentBook a déjà été ajouté dans cette session
            $item = $inventoryItemRepository->findOneByInventoryAndBook($inventory, $currentBook);
            if ($item === null) {
                return $this->redirectToRoute('add-item', [
                    'inventory' => $id,
                    'book' => $currentBook->getId(),
                ]);
            } else {
                return $this->redirectToRoute('edit-item', [
                    'item' => $item->getId(),
                ]);
            }
        }
        return $this->render('inventory_item/search.html.twig', [
            'currentInventory' => $inventory,
            'currentBook' => $currentBook,
            'checkedItems' => $checkedItems,
            'noCheckedBooks' => $noCheckedBooks,
            'allBooksByLocation' => $allBooksByLocation
        ]);
    }

    #[Route('/add', name: 'add-item')]
    public function add(
        Request $request,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $id = $request->query->get('inventory');
        $inventory = $inventoryRepository->find($id);
        $currentBook = $bookRepository->find($request->query->get('book'));

        $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);
        $location = $inventory->getLocation();
        $noCheckedBooks = $bookRepository->findNoInventory($id, $location);
        $allBooksByLocation = $bookRepository->findAllByLocation($location);

        $inventoryItem = new InventoryItem();
        $addForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $addForm->handleRequest($request);

        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $inventoryItem = $addForm->getData();
            $inventoryItem->setBook($currentBook);
            $inventoryItem->setInventory($inventory);
            $inventoryItem->setCreatedAt(new \DateTimeImmutable());
            $inventoryItem->addUser($this->getUser());
            $em->persist($inventoryItem);
            $em->flush();
            return $this->redirectToRoute('search-item', [
                'inventory' => $id
            ]);
        }
        return $this->render('inventory_item/search.html.twig', [
            'currentInventory' => $inventory,
            'currentBook' => $currentBook,
            'addForm' => $addForm->createView(),
            'editForm' => null,
            'checkedItems' => $checkedItems,
            'noCheckedBooks' => $noCheckedBooks,
            'allBooksByLocation' => $allBooksByLocation
        ]);
    }

    #[Route('/edit', name: 'edit-item')]
    public function edit(
        Request $request,
        InventoryItemRepository $inventoryItemRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $inventoryItemId = $request->query->get('item');
        $inventoryItem = $inventoryItemRepository->find($inventoryItemId);
        $inventory = $inventoryItem->getInventory();
        $id = $inventory->getId();

        $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);
        $location = $inventory->getLocation();
        $noCheckedBooks = $bookRepository->findNoInventory($id, $location);
        $allBooksByLocation = $bookRepository->findAllByLocation($location);

        $editForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $inventoryItem->setModifiedAt(new \DateTimeImmutable());
            $inventoryItem->addUser($this->getUser());
            $em->flush();
            return $this->redirectToRoute('search-item', [
                'inventory' => $inventoryItem->getInventory()->getId()
            ]);
        }

        return $this->render('inventory_item/search.html.twig', [
            'editForm' => $editForm->createView(),
            'currentBook' => $inventoryItem->getBook(),
            'currentInventory' => $inventoryItem->getInventory(),
            'addForm' => null,
            'checkedItems' => $checkedItems,
            'noCheckedBooks' => $noCheckedBooks,
            'allBooksByLocation' => $allBooksByLocation
        ]);
    }

    #[Route('/list', name: 'item-list')]
    public function list(
        Request $request,
        PaginatorInterface $paginator,
        BookRepository $bookRepository,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository
    ): Response {
        $allBooksByLocation = null;
        $checkedItems = null;
        $noCheckedBooks = null;
        $inventory = null;

        if ($request->query->has('all')) {
            $id = $request->query->get('all');
            $inventory = $inventoryRepository->findWithItems($id);
            $location = $inventory->getLocation();
            $allBooksByLocation = $bookRepository->findAllByLocation($location);
        }
        if ($request->query->has('checked')) {
            $id = $request->query->get('checked');
            $inventory = $inventoryRepository->findWithItems($id);
            $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);
        }
        if ($request->query->has('no_checked')) {
            $id = $request->query->get('no_checked');
            $inventory = $inventoryRepository->findWithItems($id);
            $location = $inventory->getLocation();
            $noCheckedBooks = $bookRepository->findNoInventory($id, $location);
        }

        // $results = $paginator->paginate(
        //     $books,
        //     $request->query->getInt('page', 1)
        // );

        return $this->render('inventory_item/books.html.twig', [
            // 'pagination' => $results,
            'allBooks' => $allBooksByLocation,
            'checkedItems' => $checkedItems,
            'noCheckedBooks' => $noCheckedBooks,
            'inventory' => $inventory
        ]);
    }
}
