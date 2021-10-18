<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018085114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE code_promo (id INT AUTO_INCREMENT NOT NULL, used_by_id INT DEFAULT NULL, code VARCHAR(50) NOT NULL, enabled TINYINT(1) NOT NULL, used_at DATETIME DEFAULT NULL, expired_at DATETIME DEFAULT NULL, amount INT NOT NULL, INDEX IDX_5C4683B74C2B72A8 (used_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE code_promo ADD CONSTRAINT FK_5C4683B74C2B72A8 FOREIGN KEY (used_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE code_promo');
    }
}
