<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210808144501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE trade (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, restaurant_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_7E1A4366A76ED395 (user_id), INDEX IDX_7E1A4366B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trade_item (id INT AUTO_INCREMENT NOT NULL, container_id INT NOT NULL, trade_id INT NOT NULL, type SMALLINT NOT NULL, quantity INT NOT NULL, INDEX IDX_7DE052E5BC21F742 (container_id), INDEX IDX_7DE052E5C2D9760 (trade_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE trade_item ADD CONSTRAINT FK_7DE052E5BC21F742 FOREIGN KEY (container_id) REFERENCES container (id)');
        $this->addSql('ALTER TABLE trade_item ADD CONSTRAINT FK_7DE052E5C2D9760 FOREIGN KEY (trade_id) REFERENCES trade (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trade_item DROP FOREIGN KEY FK_7DE052E5C2D9760');
        $this->addSql('DROP TABLE trade');
        $this->addSql('DROP TABLE trade_item');
    }
}
