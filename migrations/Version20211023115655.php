<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211023115655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE external_waste_save ADD container_id INT NOT NULL');
        $this->addSql('ALTER TABLE external_waste_save ADD CONSTRAINT FK_A59ADD8BC21F742 FOREIGN KEY (container_id) REFERENCES container (id)');
        $this->addSql('CREATE INDEX IDX_A59ADD8BC21F742 ON external_waste_save (container_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE external_waste_save DROP FOREIGN KEY FK_A59ADD8BC21F742');
        $this->addSql('DROP INDEX IDX_A59ADD8BC21F742 ON external_waste_save');
        $this->addSql('ALTER TABLE external_waste_save DROP container_id');
    }
}
