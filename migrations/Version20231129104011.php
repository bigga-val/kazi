<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231129104011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bon_produit (id INT AUTO_INCREMENT NOT NULL, facture_id INT DEFAULT NULL, ligne_facture_id INT DEFAULT NULL, created_by VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EFBD49ED7F2DEE08 (facture_id), INDEX IDX_EFBD49ED9C810015 (ligne_facture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bon_produit ADD CONSTRAINT FK_EFBD49ED7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('ALTER TABLE bon_produit ADD CONSTRAINT FK_EFBD49ED9C810015 FOREIGN KEY (ligne_facture_id) REFERENCES ligne_facture (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bon_produit DROP FOREIGN KEY FK_EFBD49ED7F2DEE08');
        $this->addSql('ALTER TABLE bon_produit DROP FOREIGN KEY FK_EFBD49ED9C810015');
        $this->addSql('DROP TABLE bon_produit');
    }
}
