<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Enum\InventoryStatusEnum;
use App\Form\InventoryFilterForm;
use App\Form\InventoryForm;
use App\Repository\InventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route(path: '/inventory')]
#[IsGranted('ROLE_USER')]
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
        $form->handleRequest($request)->createView();
        
        $filterForm = $this->createForm(InventoryFilterForm::class, $inventory);
        $filterForm->handleRequest($request)->createView();
        
        $inventories = null;
        $currentInventory = null;
        
        $inventoryId = $request->query->get('id');
        if($inventoryId){
            $currentInventory = $inventoryRepository->find($inventoryId);
        }

        if ($request->isMethod('POST')) {
            if ($currentTab === 'new') {
                if ($form->isSubmitted()) {
                    $inventory->setDate(new \DateTime('now'));
                    $inventory = $form->getData();
                    $em->persist($inventory);
                    $em->flush();
                    return $this->render('inventory/success.html.twig');
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
        return $this->render('inventory/index.html.twig', [
            'tab' => $currentTab,
            'form' => $form,
            'filterForm' => $filterForm,
            'inventories' => $inventories,
            'currentInventory'=>$currentInventory
        ]);
    }

    #[Route('/edit', name: 'edit-inventory')]
    public function edit(
        Request $request, 
        InventoryRepository $inventoryRepository,
        EntityManagerInterface $em) : Response 
    {
        $inventoryId = $request->query->get('id');
        $inventory = $inventoryRepository->find($inventoryId);

        $form = $this->createForm(InventoryForm::class, $inventory);
        $form->handleRequest($request)->createView();

        if ($form->isSubmitted()) {
            $em->flush();
            dd('modifié !');
        }

        return $this->render('inventory/edit.html.twig', [
            'form' => $form,
            'inventory'=>$inventory
        ]);
    }



    #[Route('/inventory/edit', name: 'edit-inventory')]
    public function delete(
        Request $request, 
        InventoryRepository $inventoryRepository,
        EntityManagerInterface $em) : Response 
    {
        $inventoryId = $request->query->get('id');
        $inventory = $inventoryRepository->find($inventoryId);

        if ($inventory) {
            $em->remove($inventory);
            $em->flush();
        }
        return $this->render('inventory/delete.html.twig', [
            'inventory'=>$inventory
        ]);
    }


}
