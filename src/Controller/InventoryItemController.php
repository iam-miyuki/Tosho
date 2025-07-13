<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Form\InventoryItemTypeForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route(path: '/inventory/item')]
#[IsGranted('ROLE_USER')]
final class InventoryItemController extends AbstractController
{
    #[Route('/', name: 'inventory_item')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {   
        $id = $request->query->get('id_inventory');
        $inventory = $em->getRepository(Inventory::class)->find($id);
        dd($inventory);
        return $this->render('inventory_item/form.html.twig', [
            'form' => $form
        ]);
}
}
