<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170807171717 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure_npc DROP FOREIGN KEY FK_E96C9B47CA7D6B89');
        $this->addSql('DROP TABLE adventure_npc');
        $this->addSql('DROP TABLE npc');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE adventure_npc (adventure_id INT NOT NULL, npc_id INT NOT NULL, INDEX IDX_E96C9B4755CF40F9 (adventure_id), INDEX IDX_E96C9B47CA7D6B89 (npc_id), PRIMARY KEY(adventure_id, npc_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE npc (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, created_by VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, updated_by VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, UNIQUE INDEX UNIQ_468C762C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adventure_npc ADD CONSTRAINT FK_E96C9B4755CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_npc ADD CONSTRAINT FK_E96C9B47CA7D6B89 FOREIGN KEY (npc_id) REFERENCES npc (id) ON DELETE CASCADE');
    }
}
