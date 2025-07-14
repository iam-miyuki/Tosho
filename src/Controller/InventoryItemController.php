<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\InventoryItem;
use App\Enum\InventoryStatusEnum;
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
        $inventories = $em->getRepository(Inventory::class)->findAllByStatus(
            InventoryStatusEnum::open);
dd($inventories);
}

}
