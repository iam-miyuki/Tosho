<?php

namespace App\Controller;

use App\Entity\Family;
use App\Entity\Member;
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
            return $this->render('family/index.html.twig',[
                    'searchedFamilies'=>$searchedFamilies
                ]);
        } 
        
        return $this->render('family/index.html.twig',[
            $familyName=>null,
            $searchedFamilies => null
        ]);
    }
    
    
    #[Route('/family/new', name:'new-family')]
    public function create(Request $request) : Response
    {
        dd('je suis la');
    }

    #[Route('/family/{id}', name:'show-family')]
    public function read(int $id, EntityManagerInterface $em, Request $request)
    {
        
        $currentFamily = $em->getRepository(Family::class)->findOneById($id);
        $members = $em->getRepository(Member::class)->findByFamily([
            'family'=>$currentFamily
        ]);
        if($request->request->has('family_name')){
            
            $familyName = $request->request->get('family_name');
            $searchedFamilies = $em->getRepository(Family::class)->findAllByName($familyName);
            return $this->render('family/index.html.twig',[
                'searchedFamilies'=>$searchedFamilies
            ]);
        }
        return $this->render('family/index.html.twig',[
            'currentFamily'=>$currentFamily,
            'members'=>$members
        ]);
    }


     #[Route('/family/modify/{id}', name:'modify-family')]
    public function update(int $id, EntityManagerInterface $em) : Response
    {
        $currentFamily = $em->getRepository(Family::class)->find($id);
        dd($currentFamily);
        return $this->render('family/index.html.twig',[
            'currentFamily'=>$currentFamily
        ]);
    }

    #[Route('/family/delete/{id}', name:'delete-family')]
    public function delete(int $id, EntityManagerInterface $em) : Response
    {
        $currentFamily = $em->getRepository(Family::class)->find($id);

        if($currentFamily)
        {
        // TODO:cascade avec membres
        }
        return $this->render('family/index.html.twig');
    }
    
}
