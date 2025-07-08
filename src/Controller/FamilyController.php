<?php

namespace App\Controller;

use App\Entity\Family;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class FamilyController extends AbstractController
{
    #[Route('/family', name: 'family')]
    public function index(Request $request, 
    EntityManagerInterface $em): Response
    {
        $familyName = $request->request->get('family_name');
        $searchedFamilies = null;
        if($request->getMethod()==='POST' && $request->request->has('family_name')){
            $searchedFamilies = $em->getRepository(Family::class)->findAllByName($familyName);
        }
        return $this->render('family/index.html.twig',[
                'searchedFamilies'=>$searchedFamilies
            ]);
    }
    
    #[Route('/family/{id}', name:'show-family')]
    public function showFamily(int $id, EntityManagerInterface $em)
    {
        $currentFamily = $em->getRepository(Family::class)->find($id);
        return $this->render('family/index.html.twig',[
            'currentFamily'=>$currentFamily
        ]);
    }    
}
