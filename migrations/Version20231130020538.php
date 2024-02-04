<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231130020538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ligne_bon_produit (id INT AUTO_INCREMENT NOT NULL, bon_produit_id INT DEFAULT NULL, INDEX IDX_8C5889DA5310BD51 (bon_produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ligne_bon_produit ADD CONSTRAINT FK_8C5889DA5310BD51 FOREIGN KEY (bon_produit_id) REFERENCES bon_produit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_bon_produit DROP FOREIGN KEY FK_8C5889DA5310BD51');
        $this->addSql('DROP TABLE ligne_bon_produit');
    }
}
