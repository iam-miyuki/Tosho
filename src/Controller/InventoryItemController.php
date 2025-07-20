<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Enum\InventoryStatusEnum;
use App\Form\InventoryItemForm;
use App\Repository\BookRepository;
use App\Repository\InventoryItemRepository;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $inventoryId = $request->query->get('id');
        $inventory = $inventoryRepository->find($inventoryId);

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
                    'book'=>$currentBook->getId()
                ]);
            } else {
                return $this->redirectToRoute('edit-item', [
                    'id' => $query->getId()
                ]);
            }
        }
        return $this->render('inventory_item/search.html.twig', [
            'currentInventory' => $inventory,
            'currentBook' => $currentBook,
            'query' => $query
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
            return $this->redirectToRoute('search-item',[
                'id' => $inventoryId
            ]);
        }
        return $this->render('inventory_item/add.html.twig', [
            'currentInventory'=>$inventory,
            'currentBook'=>$currentBook,
            'itemForm'=>$itemForm
        ]);
    }

    #[Route('/edit', name: 'edit-item')]
    public function edit(
        Request $request, 
        InventoryItemRepository $inventoryItemRepository, 
        EntityManagerInterface $em): Response
    {
        $inventoryItem = $inventoryItemRepository->find($request->query->get('id'));

        $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'L’inventaire a été mis à jour.');
            return $this->redirectToRoute('search-item',[
                'id' => $inventoryItem->getInventory()->getId()
            ]);
        }

        return $this->render('inventory_item/edit.html.twig', [
            'itemForm' => $itemForm,
            'currentBook' => $inventoryItem->getBook(),
            'currentInventory' => $inventoryItem->getInventory()
        ]);
    }
}
