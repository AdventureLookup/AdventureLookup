<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170413070633 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tag_content ADD tag_id INT DEFAULT NULL, ADD adventure_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag_content ADD CONSTRAINT FK_CCF41D03BAD26311 FOREIGN KEY (tag_id) REFERENCES tag_name (id)');
        $this->addSql('ALTER TABLE tag_content ADD CONSTRAINT FK_CCF41D0355CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id)');
        $this->addSql('CREATE INDEX IDX_CCF41D03BAD26311 ON tag_content (tag_id)');
        $this->addSql('CREATE INDEX IDX_CCF41D0355CF40F9 ON tag_content (adventure_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tag_content DROP FOREIGN KEY FK_CCF41D03BAD26311');
        $this->addSql('ALTER TABLE tag_content DROP FOREIGN KEY FK_CCF41D0355CF40F9');
        $this->addSql('DROP INDEX IDX_CCF41D03BAD26311 ON tag_content');
        $this->addSql('DROP INDEX IDX_CCF41D0355CF40F9 ON tag_content');
        $this->addSql('ALTER TABLE tag_content DROP tag_id, DROP adventure_id');
    }
}
