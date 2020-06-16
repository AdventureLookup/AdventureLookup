<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Error;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200607073734 extends AbstractMigration
{
    private $TABLES = ['adventure', 'ext_log_entries', 'ext_translations', 'migration_versions', 'user'];

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        foreach ($this->TABLES as $table) {
            $this->addSql('ALTER TABLE '.$table.' CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        foreach ($this->TABLES as $table) {
            $this->addSql('ALTER TABLE '.$table.' CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        }
    }
}
