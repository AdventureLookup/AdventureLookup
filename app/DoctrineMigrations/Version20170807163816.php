<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Creates a change request per NPC with an explanation why NPCs were removed.
 */
class Version20170807163816 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql(<<<SQL
DROP PROCEDURE IF EXISTS migrate_npcs;
CREATE PROCEDURE migrate_npcs()
  BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE adventure_id INT;
    DECLARE npc_name VARCHAR(255);
    DECLARE npc_cursor CURSOR FOR
      SELECT a.id, n.name
      FROM adventure a
        JOIN adventure_npc an ON an.adventure_id = a.id
        JOIN npc n ON n.id = an.npc_id
      ORDER BY a.id ASC, n.id ASC;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
    START TRANSACTION;
      OPEN npc_cursor;
      read_loop: LOOP
        FETCH npc_cursor INTO adventure_id, npc_name;
        IF done THEN
          LEAVE read_loop;
        END IF;
        INSERT INTO change_request (adventure_id, comment, resolved, created_by, updated_by, created_at, updated_at)
        VALUES (adventure_id, CONCAT('Hi! Thank you for contributing to Adventure Lookup.\nYou used the NPC field for this adventure. We and Matt decided to remove this field from all adventures. The discussion can be found at GitHub: https://github.com/AdventureLookup/AdventureLookup/issues/109\nWe therefore removed NPCs from this adventure. Notable and important NPCs should now either be added to the adventure''s description or as a monster with "unique" checked. Please note however, that it is discouraged to add NPCs which only appear in this particular adventure - who would ever be searching for them?\n\nHere is the name of one of the NPCs that were previously part of the adventure:\n\n', npc_name, '\n\nFeel free to click ''resolve'' on this change request once you have integrated this change.'), 0, 'cmfcmf', 'cmfcmf', NOW(), NOW());
      END LOOP;
      CLOSE npc_cursor;
    COMMIT;
  END;
CALL migrate_npcs();
SQL
        );

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
