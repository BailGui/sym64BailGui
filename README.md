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