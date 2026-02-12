<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211130804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add author tracking to Matiere and Resource entities';
    }

    public function up(Schema $schema): void
    {
        // Clear existing resources to avoid constraint conflicts
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql('TRUNCATE TABLE resource');
        $this->addSql('DROP TABLE IF EXISTS chapter');
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
        
        // Add author to Matiere
        $this->addSql('ALTER TABLE matiere ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574AF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9014574AF675F31B ON matiere (author_id)');
        
        // Modify Resource table
        $this->addSql('ALTER TABLE resource ADD title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource ADD author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_BC91F416F675F31B ON resource (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chapter (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, cours_id INT DEFAULT NULL, INDEX IDX_F981B52E7ECF78B0 (cours_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574AF675F31B');
        $this->addSql('DROP INDEX IDX_9014574AF675F31B ON matiere');
        $this->addSql('ALTER TABLE matiere DROP author_id');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F416F675F31B');
        $this->addSql('DROP INDEX IDX_BC91F416F675F31B ON resource');
        $this->addSql('ALTER TABLE resource DROP title, DROP author_id');
    }
}
