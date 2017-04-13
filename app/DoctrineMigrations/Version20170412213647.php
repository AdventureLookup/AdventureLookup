<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170412213647 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE adventure (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, edition VARCHAR(255) NOT NULL, year INT NOT NULL, publisher VARCHAR(255) DEFAULT NULL, num_pages INT DEFAULT NULL, format VARCHAR(255) DEFAULT NULL, author VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_9E858E0F2B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_content (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(255) NOT NULL, suggested TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_name (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, suggested TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_B02CC1B02B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE adventure');
        $this->addSql('DROP TABLE tag_content');
        $this->addSql('DROP TABLE tag_name');
    }
}
