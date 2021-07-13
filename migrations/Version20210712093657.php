<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210712093657 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms DROP COLUMN class;');
        $this->addSql('ALTER TABLE cms ADD COLUMN css JSON NOT NULL;');

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cms DROP COLUMN css;');
        $this->addSql('ALTER TABLE cms ADD COLUMN class VARCHAR(255) DEFAULT NULL;');
    }
}
