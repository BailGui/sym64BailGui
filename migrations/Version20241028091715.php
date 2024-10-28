<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241028091715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title_slug VARCHAR(160) NOT NULL, text LONGTEXT NOT NULL, article_date_create DATETIME NOT NULL, article_date_posted DATETIME DEFAULT NULL, published SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_section (article_id INT NOT NULL, section_id INT NOT NULL, INDEX IDX_C0A13E587294869C (article_id), INDEX IDX_C0A13E58D823E37A (section_id), PRIMARY KEY(article_id, section_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE section (id INT AUTO_INCREMENT NOT NULL, section_title VARCHAR(100) NOT NULL, section_slug VARCHAR(105) NOT NULL, section_detail VARCHAR(500) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_section ADD CONSTRAINT FK_C0A13E587294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_section ADD CONSTRAINT FK_C0A13E58D823E37A FOREIGN KEY (section_id) REFERENCES section (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD fullname VARCHAR(150) DEFAULT NULL, ADD uniqid VARCHAR(60) NOT NULL, ADD email VARCHAR(180) NOT NULL, ADD activate SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_section DROP FOREIGN KEY FK_C0A13E587294869C');
        $this->addSql('ALTER TABLE article_section DROP FOREIGN KEY FK_C0A13E58D823E37A');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_section');
        $this->addSql('DROP TABLE section');
        $this->addSql('ALTER TABLE user DROP fullname, DROP uniqid, DROP email, DROP activate');
    }
}
