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

        $filterForm = $this->createForm(InventoryFilterForm::class, null);
        $filterForm->handleRequest($request);

        if ($request->isMethod('POST')) {

            if ($form->isSubmitted() && $form->isValid()) {
                $inventory->setDate(new \DateTime('now'));
                $inventory = $form->getData();
                $em->persist($inventory);
                $em->flush();
                return $this->render('Admin/inventory/index.html.twig', [
                    'addedInventory' => $inventory,
                    'successMessage' => 'L\'inventaire a été crée avec success ! ',
                    'tab' => 'new'
                ]);
            }
            if ($filterForm->isSubmitted() && $filterForm->isValid()) {
                $status = $filterForm->get('status')->getData();
                $location = $filterForm->get('location')->getData();
                $inventories = $inventoryRepository->findAllWithFilterQuery($status, $location);
                return $this->render('Admin/inventory/index.html.twig', [
                    'inventories' => $inventories,
                    'tab' => 'search',
                    'filterForm' => $filterForm
                ]);
            }
        }

        return $this->render('Admin/inventory/index.html.twig', [
            'tab' => $currentTab,
            'form' => $form->createView(),
            'filterForm' => $filterForm->createView()
        ]);
    }
    #[Route('/{id}', name: 'show-inventory')]
    public function show(
        Inventory $inventory,
        InventoryItemRepository $inventoryItemRepository,
    ): Response {
        $items = $inventory->getInventoryItems();
        $notOkItems = $inventoryItemRepository->findAllByInventoryAndNotOkStatus($inventory);

        return $this->render('Admin/inventory/index.html.twig', [
            'currentInventory' => $inventory,
            'items' => $items,
            'notOkItems' => $notOkItems,
            'tab' => 'search',
        ]);
    }
    
    #[Route('/items/{id}/{page}', 
    name:'admin-items',
    requirements:['page' => '^(checked|not-ok)$'])]
    public function items(
        Inventory $inventory,
        string $page,
        InventoryRepository $inventoryRepository,
        InventoryItemRepository $inventoryItemRepository
    ) : Response {
        $currentInventory = $inventoryRepository->findWithItems($inventory->getId()); // besoin de récupérer avec inventoryItems
        $items = null;
        $notOkItems = null;

        if($page ==='checked'){
            $items = $inventoryItemRepository->findAllByInventory($inventory);
        }
        if($page ==='not-ok'){
            $notOkItems = $inventoryItemRepository->findAllByInventoryAndNotOkStatus($inventory);
        }
        return $this->render('Admin/inventory/index.html.twig',[
            'items'=>$items,
            'notOkItems'=>$notOkItems,
            'currentInventory'=>$currentInventory,
            'tab'=>'search',
            'page'=>$page
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
        EntityManagerInterface $em,
        InventoryRepository $inventoryRepository
    ): Response {
        $currentInventory = $inventoryRepository->findWithItems($inventoryItem->getInventory()->getId()); // besoin de récupérer avec inventoryItems

        $itemForm = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $itemForm->handleRequest($request)->createView();

        $items = $currentInventory->getInventoryItems();

        $itemFilterForm = $this->createForm(InventoryItemFilterForm::class, $inventoryItem);
        $itemFilterForm->handleRequest($request);

        if ($itemForm->isSubmitted()) {
            $inventoryItem->addUser($this->getUser());
            $inventoryItem->setModifiedAt(new DateTimeImmutable());
            $em->flush();
            return $this->redirectToRoute('show-inventory', [
                'id' => $currentInventory->getId()
            ]);
        }
        return $this->render('Admin/inventory/index.html.twig', [
            'itemToEdit' => $inventoryItem,
            'itemForm' => $itemForm->createView(),
            'itemFilterForm' => $itemFilterForm->createView(),
            'currentInventory' => $currentInventory,
            'items' => $items,
            'notOkItems'=>null,
            'tab'=>'search'
        ]);
    }
}
