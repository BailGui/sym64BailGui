# sym64BailGui 

### Créez un répertoire sur votre github 

Créez un répertoire sur github avec le nom `sym64{Votre-prénom}` et envoyer vos fichiers sur ce répertoire.

### Dans le `.env` de votre projet, modifiez la ligne suivante :

Ce code est pour la version locale, vous pouvez le modifier pour la version avec `Docker` :

```bash
DB_TYPE="mysql"
# DB_NAME="sym64{Votre-prénom}" # Remplacez {Votre-prénom} par votre 
DB_NAME="sym64bailgui"
# prénom dans majuscules et sans accent
DB_HOST="localhost"
DB_PORT=3306
DB_USER="root"
DB_PWD=""
DB_CHARSET="utf8mb4"

DATABASE_URL="${DB_TYPE}://${DB_USER}:${DB_PWD}@${DB_HOST}:${DB_PORT}/${DB_NAME}?charset=${DB_CHARSET}"
```

Créez la base de données avec la commande suivante :

```bash
php bin/console doctrine:database:create
```

Donnez le nom `homepage` à cet index et il doit pointer vers la racine de votre site (127.0.0.1:8000 généralement).

### Créez un User avec la commande suivante :

```bash

 php bin/console make:user

 The name of the security user class (e.g. User) [User]:
 >

 Do you want to store user data in the database (via Doctrine)? (yes/no) [yes]:
 >

 Enter a property name that will be the unique "display" name for the user (e.g. email, username, uuid) [email]:
 > username

 Will this app need to hash/check user passwords? Choose No if passwords are not needed or will be checked/hashed by some other system (e.g. a single sign-on server).   

 Does this app need to hash/check user passwords? (yes/no) [yes]:

```

Il faut ensuite faire un make:entity pour compléter l'entité `User` pour obtenir les champs suivants dans la table `user` :

```mysql
-- -----------------------------------------------------
-- Table `sym64michael`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sym64michael`.`user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(180) NOT NULL,
  `roles` JSON NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `fullname` VARCHAR(150) NULL,
  `uniqid` VARCHAR(60) NOT NULL,
  `email` VARCHAR(180) NOT NULL,
  `activate` TINYINT UNSIGNED NOT NULL DEFAULT 0-- boolean false
    ,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UNIQ_IDENTIFIER_USERNAME` (`username` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
```

### Créez une Entité `Article` avec la commande suivante :

```bash
php bin/console make:entity Article

```

Pour obtenir en base de données la table suivante :

```mysql
-- -----------------------------------------------------
-- Table `sym64michael`.`article`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sym64michael`.`article` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(160) NOT NULL,
    `title_slug` VARCHAR(162) NOT NULL,
    `text` LONGTEXT NOT NULL,
    `article_date_create` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `article_date_posted` DATETIME NULL DEFAULT NULL,
    `published` TINYINT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE INDEX `UNIQ_23A0E66D347411D` (`title_slug` ASC) VISIBLE,
    INDEX `IDX_23A0E66A76ED395` (`user_id` ASC) VISIBLE,
    CONSTRAINT `FK_23A0E66A76ED395`
      FOREIGN KEY (`user_id`)
        REFERENCES `sym64michael`.`user` (`id`))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
```

### Créez d'un `manytomany` de `Article` vers `Section` :

```bash
php bin/console make:entity Article
```

Puis migration la table :

```bash
php bin/console make:migration
# puis
php bin/console doctrine:migrations:migrate
```

### Mysql m2m

```mysql
-- -----------------------------------------------------
-- Table `sym64michael`.`article_section`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `sym64michael`.`article_section` (
  `article_id` INT UNSIGNED NOT NULL,
  `section_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`article_id`, `section_id`),
  INDEX `IDX_C0A13E587294869C` (`article_id` ASC) VISIBLE,
  INDEX `IDX_C0A13E58D823E37A` (`section_id` ASC) VISIBLE,
  CONSTRAINT `FK_C0A13E587294869C`
    FOREIGN KEY (`article_id`)
    REFERENCES `sym64michael`.`article` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `FK_C0A13E58D823E37A`
    FOREIGN KEY (`section_id`)
    REFERENCES `sym64michael`.`section` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
```

### Base de donnée minimale :

![base de donnée minimale](datas/sym64michael.png)

### Créez une Fixture pour toutes les entités :


#### Installez le package `orm-fixtures` :

```bash
composer require orm-fixtures --dev 
```

#### Importez Faker :

```bash
composer require fakerphp/faker
 ```

Documentation : https://fakerphp.org/

#### Importez Slugify :

```bash
composer require cocur/slugify
```

Documentation : https://github.com/cocur/slugify

#### Adaptez le fichier AppFixtures.php

#### Il nous faut 30 utilisateurs 
Mots de passe hachés ! Utilisation de `Slugify` pour le `username`, `Faker` pour le `fullname` et `email`, le `password` doit être haché avec `UserPasswordHasherInterface` et le `uniqid` doit être généré avec `uniqid()` :

- **1** `ROLE_ADMIN` avec comme login et mot de passe `admin` et `admin` actif, 
- **5** `ROLE_REDAC` avec comme login et mot de passe `redac{1 à 5}` et `redac{1 à 5}`  correspondants et actifs
- **24** `ROLE_USER` avec comme login et mot de passe `user{1 à 24}` et `user{1 à 24}` et **3 sur 4 actifs** ! Ne peuvent pas écrire d'articles !


#### Il nous faut 160 articles  
Utilisation de `Faker` pour le titre, puis `slugify` pour TitleSlug à partir du titre, `Faker` pour le texte, une date **entre 6 mois et maintenant** (voir `$faker->dateTimeBetween()`) pour la date de **création**, **une date après la date de création pour la date de publication si l'article est publié (3 chances sur 4)**, un auteur aléatoire (dans `ROLE_ADMIN` ou `ROLE_REDAC`).

#### Il nous faut 6 sections
Utilisation de `Faker` pour le titre, puis `slugify` pour SectionSlug à partir du titre, `Faker` pour le texte.
Il faut ajouter au **hasard entre 2 et 40 articles par section** !


```bash
<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Section;
use App\Entity\Article;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory as Faker;
use Cocur\Slugify\Slugify;

class AppFixtures extends Fixture
{
    # attribut contenant le hacher de mot de passe
    private UserPasswordHasherInterface $passwordHasher;


    # constructeur qui remplit les attributs
    public function __construct(
        UserPasswordHasherInterface $passwordHasher,

    )
    {
        # hache le mot de passe
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        // Création de Faker
        $faker = Faker::create('fr_FR');
        // Création du slugify
        $slugify = new Slugify();
        ###
        # Instanciation d'un User
        $user = new User();
        
        $user->setUniqid('1');
        $user->setUsername('admin');
        $user->setEmail('admin@gmail.com');
        $user->setRoles(['ROLE_ADMIN','ROLE_REDAC','ROLE_USER']);
        # hachage du mot de passe
        $pwdHash = $this->passwordHasher->hashPassword($user, 'admin');
        # insertion du mot de passe haché
        $user->setPassword($pwdHash);
        $user->setActivate(true);
        $user->setFullname('The Admin !');


        # Utilisation du $manager pour mettre le
        # User en mémoire
        $manager->persist($user);

         ###
        # Instanciation de 5 Rédacteurs
        #
      
        for($i = 1; $i <= 5; $i++){
            $user = new User();
            $user->setUniqid(uniqid('redac', true));
            $user->setUsername('redac'.$i);
            $user->setEMail('redac'.$i.'@gmail.com');
            $user->setRoles(['ROLE_REDAC','ROLE_USER']);
            $pwdHash = $this->passwordHasher->hashPassword($user, 'redac'.$i);
            $user->setPassword($pwdHash);
            $user->setActivate(true);
            $user->setFullname('The Redac '.$i.' !');

            // création/ update d'un tableau contenant
            // les User qui peuvent écrire un article
            $users[] = $user;

            # Utilisation du $manager pour mettre le
            # User en mémoire
            $manager->persist($user);
        }

         ###
        # Instanciation entre 24 et 30 User sans rôles
        # en utilisant Faker
        #
        $hasard = mt_rand(24,30);
        for($i = 1; $i <= $hasard; $i++){
            $user = new User();
            $user->setUniqid(uniqid('user', true));
            # nom d'utilisateur au hasard commençant par user-1234
            $username = $faker->numerify('user-####');
            $user->setUsername($username);
            # création d'un mail au hasard
            $mail = $faker->email();
            $user->setEMail($mail);
            $user->setRoles(['ROLE_USER']);
            # transformation du nom en mot de passe
            # (pour tester)
            $pwdHash = $this->passwordHasher->hashPassword($user, $username);
            $user->setPassword($pwdHash);
            $randActive = mt_rand(0,3);
            $user->setActivate($randActive);
            # Création d'un 'vrai' nom en français
            $realName = $faker->name();
            $user->setFullname($realName);
            // on garde les utilisateurs pour les commentaires
            $usersComment[] = $user;

            $manager->persist($user);

        }

           ###
    # GESTION de POST
    ###
    for($i = 1; $i <= 160; $i++){
        $post = new Article();
        // on prend un auteur au hasard
        $randomUserId = array_rand($users);

        $post->setUser($users[$randomUserId]);
        // titre entre 20 et 150 caractères
        $title = $faker->realTextBetween(20,150);
        $post->setTitle($title);
        $post->setTitleSlug($slugify->slugify($post->getTitle()));
        // texte entre 3 et 6 paragraphes
        $post->setText($faker->paragraphs(mt_rand(3,6), true));
        // on va remonter dans le passé entre 180 et 210 jours
        $day = mt_rand(180,210);
        $post->setArticleDateCreate(new DateTime("now -$day day"));
        // on va publier 3 articles sur 4 (+-) 1,2,3 => true 4 => false
        $published = mt_rand(1,4) < 4;
        $post->setPublished($published);
        if($published){
            // on va remonter dans le passé entre 5 et 15 jours
            $day = mt_rand(5,15);
            $post->setArticleDatePosted(new DateTime("now -$day day"));
        }
        // on garde les postes
        $posts[] = $post;

        $manager->persist($post);

    }
    ###
    # GESTION de Section
    ###

    // Section
    for ($i=1; $i<=6; $i++){
        $section = new Section();
        $section->setSectionTitle($faker->sentence(3, true));
        $section->setSectionSlug($slugify->slugify($section->getSectionTitle()));
        $section->setSectionDetail($faker->text(255));
        $postRandom = array_rand($posts, mt_rand(2,40));
        foreach ($postRandom as $post){
            $section->addArticle($posts[$post]);
        }
        $manager->persist($section);
    }


        # envoie à la base de donnée (commit)
        $manager->flush();
    }

    
}
```

### Choisissez un template et utiliser le sur votre projet

Vous pouvez utiliser un template gratuit de votre choix, responsive, et utiliser `Twig` pour l'intégrer dans votre projet. N'utilisez pas le même template que l'exemple donné !

### Mise en forme des formulaires et des pages avec `bootstrap`

Nous allons utiliser les assets qui se trouvent dans le dossier `assets`

Documentation :

Différence AssetMapper et Webpack Encore : https://symfony.com/doc/6.4/frontend.html#using-php-twig

### `AssetMapper`

Documentation : https://symfony.com/doc/6.4/frontend/asset_mapper.html

On va importer bootstrap

    php bin/console importmap:require bootstrap

    [OK] 3 new items (bootstrap, @popperjs/core, bootstrap/dist/css/bootstrap.min.css) added to the importmap.php!

La mise à jour a été effectuée uniquement dans `importmap.php`

Pour tester, on va d'abord trouver les templates `bootstrap` à cette adresse : https://symfony.com/doc/current/form/form_themes.html

Donc pour les formulaires `bootstrap`

```yaml
# config/packages/twig.yaml
twig:
form_themes: ['bootstrap_5_horizontal_layout.html.twig']
# ...
```

Le code `bootstrap` est généré, mais il manque le style !

Téléchargement d'une Template bootstrap à ajouter au dossier datas

On s'en servira pour créer les différents twig

## Ajout de `template.front.html.twig` 

séparation en block de la template bootstrap :

`_menu.html.twig`
`footer.html.twig`
`header.html.twig`
`main.html.twig`

## Modification de `base.html.twig`

ajout du head, des links et du script dans le fichier `base.html.twig`.

## Création d'une page de connexion

```bash
php bin/console make:security:form-login

 Choose a name for the controller class (e.g. SecurityController) [SecurityController]:
 >

 Do you want to generate a '/logout' URL? (yes/no) [yes]:
 >

 Do you want to generate PHPUnit tests? [Experimental] (yes/no) [no]:
 >

 created: src/Controller/SecurityController.php
 created: templates/security/login.html.twig
 updated: config/packages/security.yaml


  Success!
```

### Ajoutez login/logout au menu

```twig
{# templates/main/menu.html.twig #}
<nav>
    {# si nous sommes connectés #}
                {% if is_granted('IS_AUTHENTICATED') %}
               <li class="nav-item"><a class="nav-link" href="{{ path('app_logout') }}">Déconnexion</a></li>
                    {% if is_granted('ROLE_ADMIN') %}
                <li class="nav-item"><a class="nav-link" href="{{ path('app_admin') }}">Administration</a></li>
                    {% endif %}
                {% else %}
                <li class="nav-item"><a class="nav-link" href="{{ path('app_login') }}">Connexion</a></li>
                {% endif %}
</nav>
```

### Créer un contrôleur d'administration

  php bin/console make:controller AdminController
  
Une route vers un dossier `admin` a été créée, on va vérifier si un rôle lui est attribué dans le fichier `config/packages/security.yaml`

```yaml
    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        # - { path: ^/profile, roles: ROLE_USER }
```

Dorénavant, ce dossier (et sous-dossiers sont accessibles que par les `ROLE_ADMIN`)

https://symfony.com/doc/current/security.html#roles

On modifie le fichier pour passer certaines variables :

`src/Controller/AdminController.php`
```php
# ...
#[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'title' => 'Administration',
            'homepage_text' => "Bienvenue {$this->getUser()->getUsername()}",
        ]);
    }
# ...
```

On duplique `templates/template.front.html.twig` en `templates/template.back.html.twig`. On modifiera ce template suivant les besoins.

On modifie `templates/admin/index.html.twig` pour le faire correspondre aux variables du contrôleur

## Création du CRUD de Article

```bash
php bin/console make:crud

 The class name of the entity to create CRUD (e.g. OrangeGnome):
 > Article
Article

 Choose a name for your controller class (e.g. ArticleController) [ArticleContro
ller]:
 > AdminArticleController

 Do you want to generate PHPUnit tests? [Experimental] (yes/no) [no]:
 >

 created: src/Controller/AdminArticleController.php
 created: src/Form/ArticleType.php
 created: templates/admin_article/_delete_form.html.twig
 created: templates/admin_article/_form.html.twig
 created: templates/admin_article/edit.html.twig
 created: templates/admin_article/index.html.twig
 created: templates/admin_article/new.html.twig
 created: templates/admin_article/show.html.twig


  Success!


 Next: Check your new CRUD by going to /admin/article/



  Success!


 Next: Check your new CRUD by going to /admin/post/

```

on va modifier l'insertion de `src/Controller/AdminArticleController.php` pour avoir une date par défaut et éviter une erreur lors de l'insertion d'un nouveau Post

```php
#[Route('/new', name: 'app_admin_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $article->setArticleDateCreated(new \DateTime());
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_article/new.html.twig', [
            'article' => $article,
            'form' => $form,
            'title' => 'New Article',
            'homepage_text' => "Administration des Articles par {$this->getUser()->getUsername()}",
        ]);
    }

```

## Afficher les 10 derniers sur l'index. 

ajouter les routes pour les sections et les articles

```php
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
```

Appel et modification de template.front.html.twig

```php 
{% extends 'base.html.twig' %}

{% block nav %}  {% include 'main/_menu.html.twig'%} {% endblock %}
{% block body %}
<main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container">
        <h1>Blog</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.html">Home</a></li>
            <li class="current">Blog</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Blog Posts 2 Section -->
    {% for article in articles %}
    <section id="blog-posts-2" class="blog-posts-2 section">

      <div class="container">

        <div class="row gy-5">

          <div class="col-lg-12 col-md-12 text-center">
             
            <article class="align-items-center">
              <div class="post-img">
                <img src="{{ asset('img/blog/blog-1.jpg') }}" alt="" class="img-fluid">
              </div>

              <div class="meta-top">
                <ul class="justify-content-center">
                {% for section in article.sections %}
                  <li class="d-flex align-items-center"><a href="{{ path("section", {'slug': section.sectionSlug }) }}">{{ section.SectionTitle }}</a></li>
                {% endfor %}
                  <li class="d-flex align-items-center"><i class="bi bi-dot"></i>{{ article.ArticleDatePosted|date("d/m/Y \à H:i") }}<a href="blog-details.html"></a></li>
                </ul>
              </div>

              <h2 class="title">
                <a href="{{ path("article", {'slug': article.TitleSlug }) }}">{{ article.title }}</a>
              </h2>

            </article>
            
          
          </div><!-- End post list item -->

        </div><!-- End blog posts list -->

      </div>

    </section><!-- /Blog Posts 2 Section -->
    {% endfor %}

    <!-- Blog Pagination Section -->
    <section id="blog-pagination" class="blog-pagination section">

      <div class="container">
        <div class="d-flex justify-content-center">
          <ul>
            <li><a href="#"><i class="bi bi-chevron-left"></i></a></li>
            <li><a href="#">1</a></li>
            <li><a href="#" class="active">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">4</a></li>
            <li>...</li>
            <li><a href="#">10</a></li>
            <li><a href="#"><i class="bi bi-chevron-right"></i></a></li>
          </ul>
        </div>
      </div>

    </section><!-- /Blog Pagination Section -->

  </main>
  {% endblock %}
  {% block footer %}
  {% include 'main/footer.html.twig'%}
  {% endblock %}
  ``` 

# DOCKER VERSION

on va dockeriser notre projet symfony 

premièrement on va créer une branche docker par sécurité

```
git checkout -b DockerVersion
``` 

## Création du Dockerfile

création d'un dossier docker comprenant un dossier php avec un fichier Dockerfile

```
# Utiliser l'image de base PHP 8.1 avec fpm (FastCGI Process Manager) pour Alpine Linux 3.16
FROM php:8.1-fpm-alpine3.16
# installer les outils de base sans cache pour réduire l'image finale, avec des versions spécifiques :
RUN apk --no-cache add \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip


# Définicer le répertoire de travail par défaut sur /usr/src/app.
WORKDIR /usr/src/app

# Copier les fichiers de configuration de Composer pour gérer les dépendances PHP.
COPY composer.json composer.lock ./

# Ajoute les répertoires bin et vendor/bin au PATH, facilitant l'accès aux commandes installées par Composer.
RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

# Copier Composer depuis une image précédente pour éviter une réinstallation.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier tous les fichiers de l'application dans le répertoire de
COPY . .

# Installer les dépendances PHP spécifiées dans composer.json.
EXPOSE 9000
CMD ["php-fpm"]
```

création d'un dossier nginx dans le dossier docker comprenant le fichier default.conf

```
server {
   # Le nom de domaine utilisé pour le serveur. Le serveur répondra à domain.tld et www.domain.tld
   server_name domain.tld www.domain.tld;
   
   # Définit le dossier racine où Nginx va chercher les fichiers. 
   # Symfony utilise généralement un dossier 'public' comme dossier web accessible.
   root ./:/var/www/html/public;  # Symfony utilise habituellement un dossier 'public'

   # Configuration de la localisation de la racine du site
   location / {
       # Nginx essaie d'abord de servir le fichier correspondant à l'URI demandée. 
       # Si le fichier n'existe pas, il redirige vers index.php (Symfony gère toutes les requêtes via index.php)
       try_files $uri /index.php$is_args$args;
   }

   # Gestion des requêtes vers index.php
   location ~ ^/index\.php(/|$) {
       # Spécifie le service PHP utilisé par Nginx pour traiter les requêtes PHP, 
       # ici défini comme le service PHP sur le port 9000 (lié à docker-compose.yml).
       fastcgi_pass php:9000;  # Correspond au service PHP dans docker-compose.yml
       
       # Sépare le chemin de la requête pour les fichiers PHP et les paramètres supplémentaires après le fichier PHP
       fastcgi_split_path_info ^(.+\.php)(/.*)$;
       
       # Inclut les paramètres FastCGI par défaut (nécessaires pour exécuter des scripts PHP)
       include fastcgi_params;

       # Spécifie le fichier script à exécuter. Ici, Nginx combine la racine du document 
       # et le nom du script pour déterminer le chemin complet.
       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

       # Définit la racine du document (le répertoire public de Symfony)
       fastcgi_param DOCUMENT_ROOT $document_root;

       # Directive interne : cela signifie que cet emplacement ne peut pas être appelé directement par un client.
       internal;
   }

   # Gestion de tous les autres fichiers PHP
   location ~ \.php$ {
       # Spécifie à nouveau le service PHP, utilisé pour traiter toutes les requêtes .php
       fastcgi_pass php:9000;

       # Indique le fichier index à appeler si une requête PHP n'a pas de fichier spécifique
       fastcgi_index index.php;

       # Détermine le chemin complet du fichier PHP à exécuter
       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

       # Inclut les paramètres FastCGI nécessaires pour exécuter les scripts PHP
       include fastcgi_params;
   }

   # Fichier pour enregistrer les logs d'erreurs Nginx (liés à ce serveur)
   error_log /var/log/nginx/project_error.log;

   # Fichier pour enregistrer les logs des accès au serveur Nginx
   access_log /var/log/nginx/project_access.log;
}
``` 

création d'un fichier docker-compose.yaml 

```yaml

services:
  php:
    build:
      context: .
      dockerfile: ./docker/php/Dockerfile
    volumes:
      - ./:/var/www/html/

    networks:
      - symfony-network

  nginx:
    image: nginx:latest
    volumes:
      - ./:/var/www/html/
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    ports:
      - "8080:80"
    networks:
      - symfony-network
    depends_on:
      - php

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: sym64bailgui
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    ports:
      - "3308:3306"
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - symfony-network

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    environment:
      PMA_HOST: mysql
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    ports:
      - "8081:80"
    networks:
      - symfony-network

volumes:
  mysql-data:

networks:
  symfony-network:

```

## Lancement de Docker

    docker-compose down
    docker-compose build
    docker-compose up -d

## Pour utiliser PHP de l'intérieur du container

    docker-compose exec php bash

Par exemple pour installer les dépendances :

    composer install

Pour quitter le container :

    exit


# FIN DU TI 








  

