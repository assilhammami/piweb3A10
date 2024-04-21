<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240408162555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` ADD cart_items_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398F52FE1EF FOREIGN KEY (cart_items_id) REFERENCES products (id)');
        $this->addSql('CREATE INDEX IDX_F5299398F52FE1EF ON `order` (cart_items_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398F52FE1EF');
        $this->addSql('DROP INDEX IDX_F5299398F52FE1EF ON `order`');
        $this->addSql('ALTER TABLE `order` DROP cart_items_id');
    }
}
