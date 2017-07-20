<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170719053245 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure ADD setting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adventure ADD CONSTRAINT FK_9E858E0FEE35BD72 FOREIGN KEY (setting_id) REFERENCES setting (id)');
        $this->addSql('CREATE INDEX IDX_9E858E0FEE35BD72 ON adventure (setting_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure DROP FOREIGN KEY FK_9E858E0FEE35BD72');
        $this->addSql('DROP INDEX IDX_9E858E0FEE35BD72 ON adventure');
        $this->addSql('ALTER TABLE adventure DROP setting_id');
    }
}
