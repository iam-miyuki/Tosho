<?php

namespace App\Controller;

use App\Entity\Inventory;
use App\Form\InventoryTypeForm;
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
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $currentTab = $request->query->get('tab' , 'report');
        $inventory = new Inventory();
        $form = $this->createForm(InventoryTypeForm::class, $inventory);
        $form->handleRequest($request);
        if($request->isMethod('POST')){

            if ($form->isSubmitted() && $form->isValid()) {
                $inventory->setDate(new \DateTime('now'));
                $inventory = $form->getData();
                $em->persist($inventory);
                $em->flush();
                return $this->render('inventory/success.html.twig');
            }
        }
        return $this->render('inventory/index.html.twig',[
           'tab'=>$currentTab,
           'form' =>$form
        ]);
    }
}
