<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SectionRepository;
use App\Entity\Article;
use App\Entity\Section;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(SectionRepository $sections, EntityManagerInterface $em): Response{
        $articles = $em->getRepository(Article::class)->findBy(['published'=>true], ['article_date_posted'=>'DESC'],10);
        return $this->render('admin/index.html.twig', [
            'title' => 'Administration',
            'homepage_text' => "Bienvenue {$this->getUser()->getUsername()}",
            # on met dans une variable pour twig toutes les sections récupérées
            'sections' => $sections->findAll(),
            # Liste des postes
            'articles' => $articles,
        ]);
    }
    
}
