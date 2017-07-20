<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170720173952 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure ADD description LONGTEXT NOT NULL, ADD min_starting_level INT DEFAULT NULL, ADD max_starting_level INT DEFAULT NULL, ADD starting_level_range VARCHAR(255) DEFAULT NULL, ADD num_pages INT DEFAULT NULL, ADD found_in VARCHAR(255) NOT NULL, ADD link VARCHAR(255) DEFAULT NULL, ADD thumbnail_url VARCHAR(255) DEFAULT NULL, ADD soloable TINYINT(1) DEFAULT NULL, ADD pregenerated_characters TINYINT(1) DEFAULT NULL, ADD tactical_maps TINYINT(1) DEFAULT NULL, ADD handouts TINYINT(1) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure DROP description, DROP min_starting_level, DROP max_starting_level, DROP starting_level_range, DROP num_pages, DROP found_in, DROP link, DROP thumbnail_url, DROP soloable, DROP pregenerated_characters, DROP tactical_maps, DROP handouts');
    }
}
