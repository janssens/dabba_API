<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210809102742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stock ADD user_id INT DEFAULT NULL, ADD restaurant_id INT DEFAULT NULL, ADD zone_id INT DEFAULT NULL, DROP owner');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656609F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B365660A76ED395 ON stock (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4B365660B1E7706E ON stock (restaurant_id)');
        $this->addSql('CREATE INDEX IDX_4B3656609F2C3FAB ON stock (zone_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_BA388B7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cart_item (id INT AUTO_INCREMENT NOT NULL, container_id INT NOT NULL, cart_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_F0FE2527BC21F742 (container_id), INDEX IDX_F0FE25271AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE2527BC21F742 FOREIGN KEY (container_id) REFERENCES container (id)');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660A76ED395');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660B1E7706E');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656609F2C3FAB');
        $this->addSql('DROP INDEX UNIQ_4B365660A76ED395 ON stock');
        $this->addSql('DROP INDEX UNIQ_4B365660B1E7706E ON stock');
        $this->addSql('DROP INDEX IDX_4B3656609F2C3FAB ON stock');
        $this->addSql('ALTER TABLE stock ADD owner INT NOT NULL, DROP user_id, DROP restaurant_id, DROP zone_id');
    }
}
