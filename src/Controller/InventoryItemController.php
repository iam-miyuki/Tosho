<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Entity\InventoryItem;
use App\Enum\InventoryStatusEnum;
use App\Form\InventoryItemForm;
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
    #[Route('/', name: 'inventory_item')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $inventories = $em->getRepository(Inventory::class)->findAllByStatus(
            InventoryStatusEnum::open
        );
        $inventoryId = $request->query->get('id');
        $currentInventory = null;
        if ($inventoryId) {
            $currentInventory = $em->getRepository(Inventory::class)->find($inventoryId);
            return $this->render('inventory_item/index.html.twig', [
                'currentInventory' => $currentInventory
            ]);
        }
        return $this->render('inventory_item/index.html.twig', [
            'inventories' => $inventories,
            'currentInventory' => null
        ]);
    }

    #[Route('/new', name: 'new_inventory_item')]
    public function new(Request $request, 
    EntityManagerInterface $em
    ): Response
    {
        $currentBook = null;
        $inventoryId = $request->query->get('id');
        $currentInventory = $em->getRepository(Inventory::class)->find($inventoryId);
        $isExist = false;
        
        if ($request->isMethod('POST')) {
            if ($request->request->has('book_code')) {
                $bookCode = $request->request->get('book_code');
                $currentBook = $em->getRepository(Book::class)->findOneByBookCode($bookCode);
                $result = $em->getRepository(InventoryItem::class)->findOneByInventoryAndBook($currentInventory,$currentBook);
                if($result){
                    $isExist=true;
                }
                if ($currentBook && !$isExist) {
                    return $this->redirectToRoute('new_inventory_item_form', [
                        'id' => $inventoryId,
                        'book' => $currentBook->getId()
                    ]);
                } if($currentBook && $isExist){
                    return $this->redirectToRoute('edit_inventory_item_form',[
                        'id'=>$inventoryId,
                        'book'=>$currentBook->getId()
                    ]);
                }
            }
        }
        return $this->render('inventory_item/new.html.twig', [
            'currentBook' => null
        ]);
    }
    #[Route('/new/form', name: 'new_inventory_item_form')]
    public function form(Request $request, EntityManagerInterface $em): Response
    {
        $inventoryItem = new InventoryItem;
        $form = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $form->handleRequest($request); //mapper avec l'entité
        $inventoryId = $request->query->get('id');
        $bookId = $request->query->get('book');
        $currentBook = $em->getRepository(Book::class)->find($bookId);
        if ($form->isSubmitted() && $form->isValid()) {

            $currentInventory = $em->getRepository(Inventory::class)->find($inventoryId);
            
            $inventoryItem = $form->getData();
            $inventoryItem->setBook($currentBook);
            $inventoryItem->setInventory($currentInventory);
            // TODO : $inventoryItem->setUser($???);
            $em->persist($inventoryItem);
            $em->flush();
            dd('formule enregistre !');
        }
        return $this->render('inventory_item/form.html.twig', [
            'form' => $form,
            'currentBook'=>$currentBook
        ]);
    }

    #[Route('/edit/form', name: 'edit_inventory_item_form')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {

        $inventoryId = $request->query->get('id');
        $bookId = $request->query->get('book');
        $currentBook = $em->getRepository(Book::class)->find($bookId);
        $currentInventory = $em->getRepository(Inventory::class)->find($inventoryId);

        $inventoryItem = $em->getRepository(InventoryItem::class)->findOneByInventoryAndBook($currentInventory,$currentBook);
        $form = $this->createForm(InventoryItemForm::class, $inventoryItem);
        $form->handleRequest($request);
        
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            dd('formule modifié !');
        }
        return $this->render('inventory_item/edit_form.html.twig', [
            'form' => $form,
            'currentBook'=>$currentBook,
            'currentInventory'=>$currentInventory
        ]);
    }
}
