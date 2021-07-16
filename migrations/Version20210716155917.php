<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716155917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cms_zone (cms_id INT NOT NULL, zone_id INT NOT NULL, INDEX IDX_677D6D7ABE8A7CFB (cms_id), INDEX IDX_677D6D7A9F2C3FAB (zone_id), PRIMARY KEY(cms_id, zone_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cms_zone ADD CONSTRAINT FK_677D6D7ABE8A7CFB FOREIGN KEY (cms_id) REFERENCES cms (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cms_zone ADD CONSTRAINT FK_677D6D7A9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO color(name,code) VALUES ("primary","#E8743C");');
        $this->addSql('INSERT INTO color(name,code) VALUES ("accent","#E8743C");');
        $this->addSql('INSERT INTO color(name,code) VALUES ("dark","#FFFFFF");');
        $this->addSql('INSERT INTO color(name,code) VALUES ("background","#FDF9F4");');
        $this->addSql('INSERT INTO color(name,code) VALUES ("text","#4A4A4A");');
        $this->addSql('INSERT INTO color(name,code) VALUES ("border","#4A4A4A");');
        $this->addSql('INSERT INTO color(name,code) VALUES ("text-sub","#dddddd");');
        $this->addSql('INSERT INTO color(name,code) VALUES ("disabled","#aaaaaa");');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cms_zone');
    }
}
