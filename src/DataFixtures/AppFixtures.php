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
