<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Populates the created_at field with data logged in the log entries table and makes it not nullable.
 */
class Version20170818080730 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE adventure a SET a.created_at = ( 
            SELECT MIN(l.logged_at)  
            FROM ext_log_entries l  
            WHERE 
            (l.object_id = a.id AND l.object_class = 'AppBundle\\\\Entity\\\\Adventure')
            OR
            (l.object_id IN (SELECT tc.id FROM tag_content tc WHERE tc.adventure_id = a.id) AND l.object_class = 'AppBundle\\\\Entity\\\\TagContent')
        )");
        $this->addSql('ALTER TABLE adventure MODIFY created_at DATETIME NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure MODIFY created_at DATETIME NULL');
    }
}
