<?php

namespace App\DataFixtures;

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


        # envoie à la base de donnée (commit)
        $manager->flush();
    }

    
}
