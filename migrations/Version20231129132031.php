<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231129132031 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bon_produit DROP FOREIGN KEY FK_EFBD49ED9C810015');
        $this->addSql('DROP INDEX IDX_EFBD49ED9C810015 ON bon_produit');
        $this->addSql('ALTER TABLE bon_produit ADD numero VARCHAR(255) DEFAULT NULL, DROP ligne_facture_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bon_produit ADD ligne_facture_id INT DEFAULT NULL, DROP numero');
        $this->addSql('ALTER TABLE bon_produit ADD CONSTRAINT FK_EFBD49ED9C810015 FOREIGN KEY (ligne_facture_id) REFERENCES ligne_facture (id)');
        $this->addSql('CREATE INDEX IDX_EFBD49ED9C810015 ON bon_produit (ligne_facture_id)');
    }
}
