<?php

namespace App\Controller\Admin;

use DateTimeImmutable;
use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Form\InventoryItem\InventoryItemForm;
use App\Form\Inventory\InventoryForm;
use App\Form\InventoryItem\InventoryItemFilterForm;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Inventory\InventoryFilterForm;
use App\Repository\InventoryItemRepository;
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
        InventoryRepository $inventoryRepository
    ): Response {
        $currentTab = $request->query->get('tab', 'search');
        $inventory = new Inventory();

        $form = $this->createForm(InventoryForm::class, $inventory);
        $form->handleRequest($request);

        $dates = $em->getRepository(Inventory::class)->findDates();
        $filterForm = $this->createForm(InventoryFilterForm::class, null,[
            'dates'=>$dates,
        ]);
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
     #[Route('/{id}', name: 'show-inventory')]
    public function show(
        Inventory $inventory,
        InventoryItemRepository $inventoryItemRepository,
        Request $request
    ): Response {
            $items = $inventory->getInventoryItems();
            $notOkItems = $inventoryItemRepository->findAllByInventoryAndNotOkStatus($inventory);

            $inventoryItem = new InventoryItem();
            $itemFilterForm = $this->createForm(InventoryItemFilterForm::class, $inventoryItem);
            $itemFilterForm->handleRequest($request);

            $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
            $itemForm->handleRequest($request)->createView();
    
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
    
    
        #[Route('/edit/{id}', name: 'edit-inventory')]
        public function edit(
            Inventory $inventory,
            Request $request,
            EntityManagerInterface $em
        ): Response {
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
    
        #[Route('/delete/{id}', name: 'delete-inventory')]
        public function delete(
            Inventory $inventory,
            EntityManagerInterface $em
        ): Response {
        
            if ($inventory) {
                $em->remove($inventory);
                $em->flush();
            }
            return $this->redirectToRoute('inventory');
        }
    
        #[Route('/edit-item/{id}', name: 'admin-edit-item')]
        public function editItem(
            Request $request,
            InventoryItem $inventoryItem,
            EntityManagerInterface $em
        ): Response {
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
                return $this->redirectToRoute('show-inventory', [
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


