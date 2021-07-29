<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210729130456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE restaurant_tag (restaurant_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_C2E6743FB1E7706E (restaurant_id), INDEX IDX_C2E6743FBAD26311 (tag_id), PRIMARY KEY(restaurant_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE restaurant_meal_type (restaurant_id INT NOT NULL, meal_type_id INT NOT NULL, INDEX IDX_A695DEFAB1E7706E (restaurant_id), INDEX IDX_A695DEFABCFF3E8A (meal_type_id), PRIMARY KEY(restaurant_id, meal_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE restaurant_tag ADD CONSTRAINT FK_C2E6743FB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_tag ADD CONSTRAINT FK_C2E6743FBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_meal_type ADD CONSTRAINT FK_A695DEFAB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE restaurant_meal_type ADD CONSTRAINT FK_A695DEFABCFF3E8A FOREIGN KEY (meal_type_id) REFERENCES meal_type (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE meal_type_restaurant');
        $this->addSql('DROP TABLE tag_restaurant');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE meal_type_restaurant (meal_type_id INT NOT NULL, restaurant_id INT NOT NULL, INDEX IDX_4A1453F6B1E7706E (restaurant_id), INDEX IDX_4A1453F6BCFF3E8A (meal_type_id), PRIMARY KEY(meal_type_id, restaurant_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tag_restaurant (tag_id INT NOT NULL, restaurant_id INT NOT NULL, INDEX IDX_4E8E906EB1E7706E (restaurant_id), INDEX IDX_4E8E906EBAD26311 (tag_id), PRIMARY KEY(tag_id, restaurant_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE meal_type_restaurant ADD CONSTRAINT FK_4A1453F6B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE meal_type_restaurant ADD CONSTRAINT FK_4A1453F6BCFF3E8A FOREIGN KEY (meal_type_id) REFERENCES meal_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_restaurant ADD CONSTRAINT FK_4E8E906EB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_restaurant ADD CONSTRAINT FK_4E8E906EBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE restaurant_tag');
        $this->addSql('DROP TABLE restaurant_meal_type');
    }
}
