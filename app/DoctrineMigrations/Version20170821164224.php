<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170821164224 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE adventure_list (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_9E07A025A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adventure_list_adventure (adventure_list_id INT NOT NULL, adventure_id INT NOT NULL, INDEX IDX_2BFB611268A468EF (adventure_list_id), INDEX IDX_2BFB611255CF40F9 (adventure_id), PRIMARY KEY(adventure_list_id, adventure_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adventure_list ADD CONSTRAINT FK_9E07A025A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE adventure_list_adventure ADD CONSTRAINT FK_2BFB611268A468EF FOREIGN KEY (adventure_list_id) REFERENCES adventure_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_list_adventure ADD CONSTRAINT FK_2BFB611255CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure_list_adventure DROP FOREIGN KEY FK_2BFB611268A468EF');
        $this->addSql('DROP TABLE adventure_list');
        $this->addSql('DROP TABLE adventure_list_adventure');
    }
}
