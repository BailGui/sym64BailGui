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
