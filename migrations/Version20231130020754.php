<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231130020754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_bon_produit ADD ligne_facture_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ligne_bon_produit ADD CONSTRAINT FK_8C5889DA9C810015 FOREIGN KEY (ligne_facture_id) REFERENCES ligne_facture (id)');
        $this->addSql('CREATE INDEX IDX_8C5889DA9C810015 ON ligne_bon_produit (ligne_facture_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_bon_produit DROP FOREIGN KEY FK_8C5889DA9C810015');
        $this->addSql('DROP INDEX IDX_8C5889DA9C810015 ON ligne_bon_produit');
        $this->addSql('ALTER TABLE ligne_bon_produit DROP ligne_facture_id');
    }
}
