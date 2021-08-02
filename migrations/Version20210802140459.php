<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210802140459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction (uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', shop_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, currency VARCHAR(3) NOT NULL, payment_method_type SMALLINT NOT NULL, payment_method_token VARCHAR(255) DEFAULT NULL, status SMALLINT NOT NULL, detailed_status SMALLINT NOT NULL, operation_type SMALLINT NOT NULL, effective_strong_authentication TINYINT(1) NOT NULL, creation_date DATETIME NOT NULL, error_code SMALLINT DEFAULT NULL, error_message VARCHAR(255) DEFAULT NULL, detailed_error_code INT DEFAULT NULL, detailed_error_message VARCHAR(255) DEFAULT NULL, metadata LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', type SMALLINT NOT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE transaction');
    }
}
