<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Entity\InventoryItem;
use App\Enum\InventoryStatusEnum;
use App\Repository\BookRepository;
use App\Enum\InventoryItemStatusEnum;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InventoryItemRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\InventoryItem\InventoryItemForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/inventory')]
#[IsGranted('ROLE_USER')]
final class InventoryItemController extends AbstractController
{
    #[Route('/', name: 'inventory-home')]
    public function index(
        InventoryRepository $inventoryRepository,
    ): Response {
        $inventories = $inventoryRepository->findAllByStatus(InventoryStatusEnum::open);
        return $this->render('inventory_item/index.html.twig', [
            'inventories' => $inventories
        ]);
    }

    #[Route('/{id}', name: 'inventory-page')]
    public function search(
        Inventory $inventory,
        Request $request,
        BookRepository $bookRepository,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository
    ): Response {
        $currentTab = $request->query->get('tab', 'status');
        $currentInventory = $inventoryRepository->findWithItems($inventory->getId()); // besoin de récupérer avec inventoryItems
        $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);

        $location = $inventory->getLocation();
        $noCheckedBooks = $bookRepository->findNoInventory($inventory->getId(), $location);
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
                    'id' => $inventory->getId(),
                    'book' => $currentBook->getId(),
                    'tab'=>'check'
                ]);
            } else {
                return $this->redirectToRoute('edit-item', [
                    'id' => $item->getId(),
                    'tab'=>'check'
                ]);
            }
        }
        return $this->render('inventory_item/index.html.twig', [
            'currentInventory' => $currentInventory,
            'currentBook' => $currentBook,
            'checkedItems' => $checkedItems,
            'noCheckedBooks' => $noCheckedBooks,
            'allBooksByLocation' => $allBooksByLocation,
            'tab'=>$currentTab
        ]);
    }

    #[Route('/add/{id}/{book}', name: 'add-item')]
    public function add(
        Inventory $inventory,
        Book $book,
        Request $request,
        InventoryItemRepository $inventoryItemRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);
        $location = $inventory->getLocation();
        $noCheckedBooks = $bookRepository->findNoInventory($inventory->getId(), $location);
        $allBooksByLocation = $bookRepository->findAllByLocation($location);

        $inventoryItem = new InventoryItem();
        $addForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $addForm->handleRequest($request);

        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $inventoryItem = $addForm->getData();
            $inventoryItem->setBook($book);
            $inventoryItem->setInventory($inventory);
            $inventoryItem->setCreatedAt(new \DateTimeImmutable());
            $inventoryItem->addUser($this->getUser());
            $em->persist($inventoryItem);
            $em->flush();
            return $this->redirectToRoute('inventory-page', [
                'id' => $inventory->getId(),
                'tab'=>'check'
            ]);
        }
        return $this->render('inventory_item/index.html.twig', [
            'currentInventory' => $inventory,
            'currentBook' => $book,
            'addForm' => $addForm->createView(),
            'editForm' => null,
            'checkedItems' => $checkedItems,
            'noCheckedBooks' => $noCheckedBooks,
            'allBooksByLocation' => $allBooksByLocation,
            'tab'=>'check'
        ]);
    }

    #[Route('/edit/{id}', name: 'edit-item')]
    public function edit(
        Request $request,
        InventoryItem $inventoryItem,
        InventoryItemRepository $inventoryItemRepository,
        BookRepository $bookRepository,
        EntityManagerInterface $em
    ): Response {
        $inventory = $inventoryItem->getInventory();

        $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);
        $location = $inventory->getLocation();
        $noCheckedBooks = $bookRepository->findNoInventory($inventory->getId(), $location);
        $allBooksByLocation = $bookRepository->findAllByLocation($location);

        $editForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $inventoryItem->setModifiedAt(new \DateTimeImmutable());
            $inventoryItem->addUser($this->getUser());
            $em->flush();
            return $this->redirectToRoute('inventory-page', [
                'id'=>$inventory->getId(),
                'tab'=>'check'
            ]);
        }

        return $this->render('inventory_item/index.html.twig', [
            'editForm' => $editForm->createView(),
            'currentBook' => $inventoryItem->getBook(),
            'currentInventory' => $inventoryItem->getInventory(),
            'addForm' => null,
            'checkedItems' => $checkedItems,
            'noCheckedBooks' => $noCheckedBooks,
            'allBooksByLocation' => $allBooksByLocation,
            'tab'=>'check'
        ]);
    }

    #[Route(
        '/list/{id}/{page}',
        name: 'item-list',
        requirements: ['page' => '^(all|checked|no-checked)$']
    )]
    public function list(
        Inventory $inventory,
        string $page,
        PaginatorInterface $paginator,
        BookRepository $bookRepository,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository
    ): Response {
        $inventoryWithItems = $inventoryRepository->findWithItems($inventory->getId());
        $location = $inventoryWithItems->getLocation();

        if ($page==='all') {
            $allBooksByLocation = $bookRepository->findAllByLocation($location);
            return $this->render('inventory_item/index.html.twig',[
                'all'=>$allBooksByLocation,
                'tab'=>'status',
                'currentInventory'=>$inventoryWithItems,
            ]);
        }
        if ($page==='checked') {
            $checkedItems = $inventoryItemRepository->findAllByInventory($inventory);
            return $this->render('inventory_item/inventory.html.twig',[
                'checked'=>$checkedItems,
                'tab'=>'status',
                'currentInventory'=>$inventoryWithItems
            ]);
        }
        if ($page==='no-checked') {
            $noCheckedBooks = $bookRepository->findNoInventory($inventory->getId(), $location);
            return $this->render('inventory_item/inventory.html.twig',[
                'noChecked'=>$noCheckedBooks,
                'tab'=>'status',
                'currentInventory'=>$inventoryWithItems
            ]);
        }

        // $results = $paginator->paginate(
        //     $books,
        //     $request->query->getInt('page', 1)
        // );
        
        return $this->render('inventory_item/index.html.twig', [
            // 'pagination' => $results,
            'currentInventory' => $inventoryWithItems,
            'tab'=>'status'
        ]);
    }
}
