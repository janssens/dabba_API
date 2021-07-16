<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716142054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE color (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms ADD text_color_id INT DEFAULT NULL, ADD background_color_id INT DEFAULT NULL, ADD format SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE cms ADD CONSTRAINT FK_AC8F9907CC9893A7 FOREIGN KEY (text_color_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE cms ADD CONSTRAINT FK_AC8F9907A1A51272 FOREIGN KEY (background_color_id) REFERENCES color (id)');
        $this->addSql('CREATE INDEX IDX_AC8F9907CC9893A7 ON cms (text_color_id)');
        $this->addSql('CREATE INDEX IDX_AC8F9907A1A51272 ON cms (background_color_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cms DROP FOREIGN KEY FK_AC8F9907CC9893A7');
        $this->addSql('ALTER TABLE cms DROP FOREIGN KEY FK_AC8F9907A1A51272');
        $this->addSql('DROP TABLE color');
        $this->addSql('DROP INDEX IDX_AC8F9907CC9893A7 ON cms');
        $this->addSql('DROP INDEX IDX_AC8F9907A1A51272 ON cms');
        $this->addSql('ALTER TABLE cms DROP text_color_id, DROP background_color_id, DROP format');
    }
}
