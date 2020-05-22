<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200521181357 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tag_content DROP FOREIGN KEY FK_CCF41D03BAD26311');
        $this->addSql('DROP TABLE tag_content');
        $this->addSql('DROP TABLE tag_name');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tag_content (id INT AUTO_INCREMENT NOT NULL, adventure_id INT DEFAULT NULL, tag_id INT DEFAULT NULL, content LONGTEXT NOT NULL COLLATE utf8_unicode_ci, approved TINYINT(1) NOT NULL, version INT DEFAULT 1 NOT NULL, created_by VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, changed_by VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, INDEX IDX_CCF41D03BAD26311 (tag_id), INDEX IDX_CCF41D0355CF40F9 (adventure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_name (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, approved TINYINT(1) NOT NULL, type VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, description VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, example VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, use_as_filter TINYINT(1) NOT NULL, version INT DEFAULT 1 NOT NULL, show_in_search_results TINYINT(1) NOT NULL, created_by VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, updated_by VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_B02CC1B02B36786B (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag_content ADD CONSTRAINT FK_CCF41D0355CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id)');
        $this->addSql('ALTER TABLE tag_content ADD CONSTRAINT FK_CCF41D03BAD26311 FOREIGN KEY (tag_id) REFERENCES tag_name (id)');
    }
}
