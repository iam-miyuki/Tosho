<?php

namespace App\Controller\Admin;

use App\Entity\Inventory;
use App\Form\InventoryForm;
use App\Entity\InventoryItem;
use App\Form\InventoryItemForm;
use App\Form\InventoryFilterForm;
use App\Enum\InventoryItemStatusEnum;
use App\Form\InventoryItemFilterForm;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InventoryItemRepository;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(path: '/admin/inventory')]
#[IsGranted('ROLE_ADMIN')]
final class InventoryController extends AbstractController
{
    #[Route('/', name: 'inventory')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository
    ): Response {
        $currentTab = $request->query->get('tab', 'search');
        $inventory = new Inventory();

        $form = $this->createForm(InventoryForm::class, $inventory);
        $form->handleRequest($request);



        $filterForm = $this->createForm(InventoryFilterForm::class, $inventory);
        $filterForm->handleRequest($request);

        $inventories = null;
        $currentInventory = null;
        $items = null;
        $notOkItems = null;

        if ($request->isMethod('POST')) {
            if ($currentTab === 'new') {
                if ($form->isSubmitted()) {
                    $inventory->setDate(new \DateTime('now'));
                    $inventory = $form->getData();
                    $em->persist($inventory);
                    $em->flush();
                    return $this->render('Admin/inventory/success.html.twig');
                }
            }
            if ($currentTab === 'search') {
                if ($filterForm->isSubmitted()) {
                    $data = $filterForm->getData();
                    $status = $data->getStatus();
                    $date = $data->getDate();
                    $location = $data->getLocation();
                    $inventories = $inventoryRepository->findAllWithFilterQuery($status, $date, $location);
                }
            }
        }

        if ($request->query->has('id')) {
            $id = $request->query->get('id');
            $inventory = $inventoryRepository->findWithItems($id);
            $items = $inventory->getInventoryItems();
            $notOkItems = $inventoryItemRepository->findAllByInventoryAndNotOkStatus($inventory);
            return $this->redirectToRoute('inventory-search-items', [
                'id' => $id
            ]);
        }

        return $this->render('Admin/inventory/index.html.twig', [
            'tab' => $currentTab,
            'form' => $form->createView(),
            'filterForm' => $filterForm->createView(),
            'inventories' => $inventories,
            'currentInventory' => $currentInventory,
            'items' => $items,
            'notOkItems' => $notOkItems
        ]);
    }

    #[Route('/search-items', name: 'inventory-search-items')]
    public function searchItems(
        Request $request,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository
    ): Response {
        $inventoryItem = new InventoryItem();
        $itemFilterForm = $this->createForm(InventoryItemFilterForm::class, $inventoryItem);
        $itemFilterForm->handleRequest($request);
        $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $itemForm->handleRequest($request)->createView();

        $id = $request->query->get('id');
        $inventory = $inventoryRepository->findWithItems($id);
        $items = $inventory->getInventoryItems();
        $notOkItems = $inventoryItemRepository->findAllByInventoryAndNotOkStatus($inventory);

        if ($itemFilterForm->isSubmitted()) {
            $status = $itemFilterForm->get('status')->getData();
            $items = $inventoryItemRepository->findAllByInventoryAndStatus($inventory, $status);
        }

        return $this->render('Admin/inventory/inventory-page.html.twig', [
            'currentInventory' => $inventory,
            'items' => $items,
            'notOkItems' => $notOkItems,
            'tab' => 'search',
            'itemFilterForm' => $itemFilterForm->createView(),
            'itemForm' => $itemForm->createView()
        ]);
    }


    #[Route('/edit', name: 'edit-inventory')]
    public function edit(
        Request $request,
        InventoryRepository $inventoryRepository,
        EntityManagerInterface $em
    ): Response {
        $inventoryId = $request->query->get('id');
        $inventory = $inventoryRepository->findWithItems($inventoryId);

        $form = $this->createForm(InventoryForm::class, $inventory);
        $form->handleRequest($request)->createView();

        if ($form->isSubmitted()) {
            $em->flush();
            dd('modifié !');
        }

        return $this->render('Admin/inventory/edit.html.twig', [
            'form' => $form->createView(),
            'inventory' => $inventory
        ]);
    }

    #[Route('/delete', name: 'delete-inventory')]
    public function delete(
        Request $request,
        InventoryRepository $inventoryRepository,
        EntityManagerInterface $em
    ): Response {
        $inventoryId = $request->query->get('id');
        $inventory = $inventoryRepository->find($inventoryId);

        if ($inventory) {
            $em->remove($inventory);
            $em->flush();
        }
        return $this->redirectToRoute('inventory');
    }

    #[Route('/edit-item-admin', name: 'edit-item-admin')]
    public function editIA(
        Request $request,
        InventoryItemRepository $inventoryItemRepository,
        EntityManagerInterface $em
    ): Response {
        $id = $request->query->get('item');
        $inventoryItem = $inventoryItemRepository->find($id);
        $inventory = $inventoryItem->getInventory();
        $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $itemForm->handleRequest($request)->createView();
        $items = $inventory->getInventoryItems();
        $itemFilterForm = $this->createForm(InventoryItemFilterForm::class, $inventoryItem);
        $itemFilterForm->handleRequest($request);

        if ($itemForm->isSubmitted()) {
            $inventoryItem->addUser($this->getUser());
            $inventoryItem->setModifiedAt(new DateTimeImmutable());
            $em->flush();
            return $this->redirectToRoute('inventory-search-items', [
                'id' => $inventory->getId()
            ]);
        }
        return $this->render('Admin/inventory/edit-item.html.twig', [
            'item' => $inventoryItem,
            'itemForm' => $itemForm->createView(),
            'itemFilterForm' => $itemFilterForm->createView(),
            'currentInventory' => $inventory,
            'items' => $items,
        ]);
    }
}
