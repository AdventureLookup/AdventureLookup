<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adds the createdAt field and populates it with data logged in the log entries table
 */
class Version20170816131325 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure ADD created_at DATETIME NULL');
        $this->addSql("UPDATE adventure a
            LEFT JOIN ext_log_entries l ON l.object_id = a.id AND l.action = 'create' AND l.object_class = 'AppBundle\\\\Entity\\\\Adventure'
            SET a.created_at = l.logged_at");
        $this->addSql('ALTER TABLE adventure MODIFY created_at DATETIME NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure DROP created_at');
    }
}
