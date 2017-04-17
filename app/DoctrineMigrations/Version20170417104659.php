<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170417104659 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure ADD created_by VARCHAR(255) NOT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tag_content ADD created_by VARCHAR(255) NOT NULL, ADD changed_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tag_name ADD created_by VARCHAR(255) NOT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE tag_content DROP created_by, DROP changed_by');
        $this->addSql('ALTER TABLE tag_name DROP created_by, DROP updated_by');
    }
}
