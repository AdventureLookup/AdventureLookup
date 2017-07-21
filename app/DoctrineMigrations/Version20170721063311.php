<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170721063311 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE monster (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_unique TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_245EC6F45E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monster_monstertype (monster_id INT NOT NULL, monster_type_id INT NOT NULL, INDEX IDX_20294CD2C5FF1223 (monster_id), INDEX IDX_20294CD2672D3DAC (monster_type_id), PRIMARY KEY(monster_id, monster_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monster_monstertype ADD CONSTRAINT FK_20294CD2C5FF1223 FOREIGN KEY (monster_id) REFERENCES monster (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE monster_monstertype ADD CONSTRAINT FK_20294CD2672D3DAC FOREIGN KEY (monster_type_id) REFERENCES monster_type (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE monster_monstertype DROP FOREIGN KEY FK_20294CD2C5FF1223');
        $this->addSql('DROP TABLE monster');
        $this->addSql('DROP TABLE monster_monstertype');
    }
}
