<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127105316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE centrale (id INT AUTO_INCREMENT NOT NULL, nom_centrale VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE fournisseur (id INT AUTO_INCREMENT NOT NULL, code_fournisseur VARCHAR(100) NOT NULL, nom_fournisseur VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_369ECA327FDF2382 (code_fournisseur), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE magasin (id INT AUTO_INCREMENT NOT NULL, code_magasin VARCHAR(100) NOT NULL, nom_magasin VARCHAR(255) NOT NULL, ville_id INT NOT NULL, centrale_id INT NOT NULL, statut_id INT NOT NULL, type_projet_id INT NOT NULL, UNIQUE INDEX UNIQ_54AF5F276C081BBB (code_magasin), INDEX IDX_54AF5F27A73F0036 (ville_id), INDEX IDX_54AF5F2758A1D71F (centrale_id), INDEX IDX_54AF5F27F6203804 (statut_id), INDEX IDX_54AF5F27B407C362 (type_projet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE materiel (id INT AUTO_INCREMENT NOT NULL, code_article VARCHAR(100) NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, valeur_unitaire_ht NUMERIC(10, 2) NOT NULL, fournisseur_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_18D2B0913A9D4AFA (code_article), INDEX IDX_18D2B091670C757F (fournisseur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE mouvement_stock (id INT AUTO_INCREMENT NOT NULL, date_mouvement DATETIME NOT NULL, type_mouvement VARCHAR(50) NOT NULL, quantite_mouvement INT NOT NULL, materiel_id INT NOT NULL, magasin_id INT DEFAULT NULL, INDEX IDX_61E2C8EB16880AAF (materiel_id), INDEX IDX_61E2C8EB20096AE3 (magasin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE statut (id INT AUTO_INCREMENT NOT NULL, nom_statut VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_central (id INT AUTO_INCREMENT NOT NULL, quantite_stock_central INT NOT NULL, materiel_id INT NOT NULL, UNIQUE INDEX UNIQ_CB65320016880AAF (materiel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_projet (id INT AUTO_INCREMENT NOT NULL, nom_type_projet VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ville (id INT AUTO_INCREMENT NOT NULL, nom_ville VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_43C3D9C3E93B4556 (nom_ville), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT FK_54AF5F27A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT FK_54AF5F2758A1D71F FOREIGN KEY (centrale_id) REFERENCES centrale (id)');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT FK_54AF5F27F6203804 FOREIGN KEY (statut_id) REFERENCES statut (id)');
        $this->addSql('ALTER TABLE magasin ADD CONSTRAINT FK_54AF5F27B407C362 FOREIGN KEY (type_projet_id) REFERENCES type_projet (id)');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B091670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB16880AAF FOREIGN KEY (materiel_id) REFERENCES materiel (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EB20096AE3 FOREIGN KEY (magasin_id) REFERENCES magasin (id)');
        $this->addSql('ALTER TABLE stock_central ADD CONSTRAINT FK_CB65320016880AAF FOREIGN KEY (materiel_id) REFERENCES materiel (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY FK_54AF5F27A73F0036');
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY FK_54AF5F2758A1D71F');
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY FK_54AF5F27F6203804');
        $this->addSql('ALTER TABLE magasin DROP FOREIGN KEY FK_54AF5F27B407C362');
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B091670C757F');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EB16880AAF');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EB20096AE3');
        $this->addSql('ALTER TABLE stock_central DROP FOREIGN KEY FK_CB65320016880AAF');
        $this->addSql('DROP TABLE centrale');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE magasin');
        $this->addSql('DROP TABLE materiel');
        $this->addSql('DROP TABLE mouvement_stock');
        $this->addSql('DROP TABLE statut');
        $this->addSql('DROP TABLE stock_central');
        $this->addSql('DROP TABLE type_projet');
        $this->addSql('DROP TABLE ville');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
