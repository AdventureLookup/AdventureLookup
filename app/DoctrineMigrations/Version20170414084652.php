<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170414084652 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure ADD approved TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE tag_content CHANGE suggested approved TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE tag_name CHANGE suggested approved TINYINT(1) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure DROP approved');
        $this->addSql('ALTER TABLE tag_content CHANGE approved suggested TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE tag_name CHANGE approved suggested TINYINT(1) NOT NULL');
    }
}
