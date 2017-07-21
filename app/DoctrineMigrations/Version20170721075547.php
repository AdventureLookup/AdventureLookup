<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170721075547 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE adventure_author (adventure_id INT NOT NULL, author_id INT NOT NULL, INDEX IDX_B2F7937F55CF40F9 (adventure_id), INDEX IDX_B2F7937FF675F31B (author_id), PRIMARY KEY(adventure_id, author_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adventure_environment (adventure_id INT NOT NULL, environment_id INT NOT NULL, INDEX IDX_4DE3B6DD55CF40F9 (adventure_id), INDEX IDX_4DE3B6DD903E3A94 (environment_id), PRIMARY KEY(adventure_id, environment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adventure_item (adventure_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_C5D47D2355CF40F9 (adventure_id), INDEX IDX_C5D47D23126F525E (item_id), PRIMARY KEY(adventure_id, item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adventure_npc (adventure_id INT NOT NULL, npc_id INT NOT NULL, INDEX IDX_E96C9B4755CF40F9 (adventure_id), INDEX IDX_E96C9B47CA7D6B89 (npc_id), PRIMARY KEY(adventure_id, npc_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE author (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_BDAFD8C85E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE edition (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_A891181F5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE environment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_4626DE225E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1F1B251E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE npc (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_468C762C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE publisher (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_9CE8D5465E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adventure_author ADD CONSTRAINT FK_B2F7937F55CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_author ADD CONSTRAINT FK_B2F7937FF675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_environment ADD CONSTRAINT FK_4DE3B6DD55CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_environment ADD CONSTRAINT FK_4DE3B6DD903E3A94 FOREIGN KEY (environment_id) REFERENCES environment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_item ADD CONSTRAINT FK_C5D47D2355CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_item ADD CONSTRAINT FK_C5D47D23126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_npc ADD CONSTRAINT FK_E96C9B4755CF40F9 FOREIGN KEY (adventure_id) REFERENCES adventure (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure_npc ADD CONSTRAINT FK_E96C9B47CA7D6B89 FOREIGN KEY (npc_id) REFERENCES npc (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE adventure ADD edition_id INT DEFAULT NULL, ADD publisher_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE adventure ADD CONSTRAINT FK_9E858E0F74281A5E FOREIGN KEY (edition_id) REFERENCES edition (id)');
        $this->addSql('ALTER TABLE adventure ADD CONSTRAINT FK_9E858E0F40C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id)');
        $this->addSql('CREATE INDEX IDX_9E858E0F74281A5E ON adventure (edition_id)');
        $this->addSql('CREATE INDEX IDX_9E858E0F40C86FCE ON adventure (publisher_id)');
        $this->addSql('ALTER TABLE setting ADD created_by VARCHAR(255) DEFAULT NULL, ADD updated_by VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_1483a5e9f85e0677 TO UNIQ_8D93D649F85E0677');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_1483a5e9e7927c74 TO UNIQ_8D93D649E7927C74');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE adventure_author DROP FOREIGN KEY FK_B2F7937FF675F31B');
        $this->addSql('ALTER TABLE adventure DROP FOREIGN KEY FK_9E858E0F74281A5E');
        $this->addSql('ALTER TABLE adventure_environment DROP FOREIGN KEY FK_4DE3B6DD903E3A94');
        $this->addSql('ALTER TABLE adventure_item DROP FOREIGN KEY FK_C5D47D23126F525E');
        $this->addSql('ALTER TABLE adventure_npc DROP FOREIGN KEY FK_E96C9B47CA7D6B89');
        $this->addSql('ALTER TABLE adventure DROP FOREIGN KEY FK_9E858E0F40C86FCE');
        $this->addSql('DROP TABLE adventure_author');
        $this->addSql('DROP TABLE adventure_environment');
        $this->addSql('DROP TABLE adventure_item');
        $this->addSql('DROP TABLE adventure_npc');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE edition');
        $this->addSql('DROP TABLE environment');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE npc');
        $this->addSql('DROP TABLE publisher');
        $this->addSql('DROP INDEX IDX_9E858E0F74281A5E ON adventure');
        $this->addSql('DROP INDEX IDX_9E858E0F40C86FCE ON adventure');
        $this->addSql('ALTER TABLE adventure DROP edition_id, DROP publisher_id');
        $this->addSql('ALTER TABLE setting DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649f85e0677 TO UNIQ_1483A5E9F85E0677');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649e7927c74 TO UNIQ_1483A5E9E7927C74');
    }
}
