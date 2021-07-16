<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716162437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cms DROP FOREIGN KEY FK_AC8F9907A1A51272');
        $this->addSql('ALTER TABLE cms DROP FOREIGN KEY FK_AC8F9907CC9893A7');
        $this->addSql('ALTER TABLE cms ADD CONSTRAINT FK_AC8F9907A1A51272 FOREIGN KEY (background_color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms ADD CONSTRAINT FK_AC8F9907CC9893A7 FOREIGN KEY (text_color_id) REFERENCES color (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cms DROP FOREIGN KEY FK_AC8F9907CC9893A7');
        $this->addSql('ALTER TABLE cms DROP FOREIGN KEY FK_AC8F9907A1A51272');
        $this->addSql('ALTER TABLE cms ADD CONSTRAINT FK_AC8F9907CC9893A7 FOREIGN KEY (text_color_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE cms ADD CONSTRAINT FK_AC8F9907A1A51272 FOREIGN KEY (background_color_id) REFERENCES color (id)');
    }
}
