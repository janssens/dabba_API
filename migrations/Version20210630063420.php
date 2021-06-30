<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210630063420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access_token (id VARCHAR(255) NOT NULL, user_id VARCHAR(255) NOT NULL, client_id VARCHAR(255) NOT NULL, scopes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', revoked TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_BA388B7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart_item (id INT AUTO_INCREMENT NOT NULL, container_id INT NOT NULL, cart_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_F0FE2527BC21F742 (container_id), INDEX IDX_F0FE25271AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, secret VARCHAR(255) NOT NULL, redirect VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cms (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, position INT NOT NULL, class VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, button_label VARCHAR(255) DEFAULT NULL, from_date DATETIME DEFAULT NULL, to_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE container (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movement (id INT AUTO_INCREMENT NOT NULL, stock_from_id INT DEFAULT NULL, stock_to_id INT DEFAULT NULL, container_id INT NOT NULL, created_at DATETIME NOT NULL, reason INT NOT NULL, INDEX IDX_F4DD95F76168C9A8 (stock_from_id), INDEX IDX_F4DD95F7AC255694 (stock_to_id), INDEX IDX_F4DD95F7BC21F742 (container_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, amount INT NOT NULL, status INT NOT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, access_token_id VARCHAR(255) NOT NULL, revoked TINYINT(1) NOT NULL, expires_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant (id INT AUTO_INCREMENT NOT NULL, zone_id INT NOT NULL, name VARCHAR(255) NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, google_place_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_EB95123F5E237E06 (name), INDEX IDX_EB95123F9F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, type INT NOT NULL, owner INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, owned_zone_id INT DEFAULT NULL, zone_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, dob DATE DEFAULT NULL, is_verified TINYINT(1) NOT NULL, roles JSON NOT NULL, INDEX IDX_8D93D649CC80828F (owned_zone_id), INDEX IDX_8D93D6499F2C3FAB (zone_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE zone (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE2527BC21F742 FOREIGN KEY (container_id) REFERENCES container (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F76168C9A8 FOREIGN KEY (stock_from_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F7AC255694 FOREIGN KEY (stock_to_id) REFERENCES stock (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F7BC21F742 FOREIGN KEY (container_id) REFERENCES container (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE restaurant ADD CONSTRAINT FK_EB95123F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649CC80828F FOREIGN KEY (owned_zone_id) REFERENCES zone (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6499F2C3FAB FOREIGN KEY (zone_id) REFERENCES zone (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25271AD5CDBF');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE2527BC21F742');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F7BC21F742');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F76168C9A8');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F7AC255694');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE restaurant DROP FOREIGN KEY FK_EB95123F9F2C3FAB');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649CC80828F');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6499F2C3FAB');
        $this->addSql('DROP TABLE access_token');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE cms');
        $this->addSql('DROP TABLE container');
        $this->addSql('DROP TABLE movement');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE restaurant');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE zone');
    }
}
