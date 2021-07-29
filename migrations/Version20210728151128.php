<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210728151128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE meal_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meal_type_restaurant (meal_type_id INT NOT NULL, restaurant_id INT NOT NULL, INDEX IDX_4A1453F6BCFF3E8A (meal_type_id), INDEX IDX_4A1453F6B1E7706E (restaurant_id), PRIMARY KEY(meal_type_id, restaurant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_restaurant (tag_id INT NOT NULL, restaurant_id INT NOT NULL, INDEX IDX_4E8E906EBAD26311 (tag_id), INDEX IDX_4E8E906EB1E7706E (restaurant_id), PRIMARY KEY(tag_id, restaurant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meal_type_restaurant ADD CONSTRAINT FK_4A1453F6BCFF3E8A FOREIGN KEY (meal_type_id) REFERENCES meal_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meal_type_restaurant ADD CONSTRAINT FK_4A1453F6B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_restaurant ADD CONSTRAINT FK_4E8E906EBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_restaurant ADD CONSTRAINT FK_4E8E906EB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` ADD state INT NOT NULL');
        $this->addSql('ALTER TABLE restaurant ADD city VARCHAR(75) NOT NULL, ADD zip VARCHAR(6) NOT NULL, ADD street VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD wallet DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('INSERT INTO tag(name) VALUES ("Traditionel");');
        $this->addSql('INSERT INTO tag(name) VALUES ("Bio");');
        $this->addSql('INSERT INTO tag(name) VALUES ("Végétarien");');
        $this->addSql('INSERT INTO tag(name) VALUES ("Vegan");');
        $this->addSql('INSERT INTO tag(name) VALUES ("Local");');
        $this->addSql('INSERT INTO tag(name) VALUES ("Italien");');
        $this->addSql('INSERT INTO meal_type(name) VALUES ("Petit-déjeuner");');
        $this->addSql('INSERT INTO meal_type(name) VALUES ("Déjeuner");');
        $this->addSql('INSERT INTO meal_type(name) VALUES ("Brunch");');
        $this->addSql('INSERT INTO meal_type(name) VALUES ("Gouter");');
        $this->addSql('INSERT INTO meal_type(name) VALUES ("Diner");');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE meal_type_restaurant DROP FOREIGN KEY FK_4A1453F6BCFF3E8A');
        $this->addSql('ALTER TABLE tag_restaurant DROP FOREIGN KEY FK_4E8E906EBAD26311');
        $this->addSql('DROP TABLE meal_type');
        $this->addSql('DROP TABLE meal_type_restaurant');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_restaurant');
        $this->addSql('ALTER TABLE `order` DROP state');
        $this->addSql('ALTER TABLE restaurant DROP city, DROP zip, DROP street');
        $this->addSql('ALTER TABLE user DROP wallet');
    }
}
