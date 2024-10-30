<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
# Appel de l'Entity Article
use App\Entity\Article;
use App\Entity\Section;


class MainController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    # appel du gestionnaire de Section
    public function index(SectionRepository $sections, EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findBy(['published'=>true], ['article_date_posted'=>'DESC'],10);

        return $this->render(
            'main/index.html.twig', [
                'title' => 'Homepage',
                'homepage_text'=> "Nous somme le ".date('d/m/Y \à H:i'
                ),
                # on met dans une variable pour twig toutes les sections récupérées
                'sections' => $sections->findAll(),
                # Liste des postes
                'articles' => $articles,

            ]
        );
    }

     // création de l'url pour le détail d'une section
     #[Route(
        # chemin vers la section avec son id
        path: '/section/{id}',
        # nom du chemin
        name: 'section',
        # accepte l'id au format int positif uniquement
        requirements: ['id' => '\d+'],
        # si absent, donne 1 comme valeur par défaut
        defaults: ['id'=>1])]

    public function section(SectionRepository $sections, int $id): Response
    {
        // récupération de la section
        $section = $sections->find($id);
        return $this->render('main/section.html.twig', [
            'title' => 'Section '.$section->getSectionTitle(),
            'homepage_text'=> $section->getSectionDetail(),
            'section' => $section,
            'sections' => $sections->findAll(),
        ]);
    }

    #[Route('/article/{slug}', name: 'article', methods: ['GET', 'POST'])]
    public function article($slug, EntityManagerInterface $em, Request $request): Response
    {

        $sections = $em->getRepository(Section::class)->findAll();
        $articles = $em->getRepository(Article::class)->findAll();
        $article = $em->getRepository(Article::class)->findOneBy(['title_slug' => $slug]);

        return $this->render('main/article.html.twig', [
            'sections' => $sections,
            'article' => $article,
            'articles' => $articles,
        ]);
    }
}



