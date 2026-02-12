<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211132202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cours_id to resource table and make image_url nullable';
    }

    public function up(Schema $schema): void
    {
        // Make image_url nullable
        $this->addSql('ALTER TABLE cours CHANGE image_url image_url VARCHAR(255) DEFAULT NULL');
        
        // Add cours_id to resource table
        $this->addSql('ALTER TABLE resource ADD cours_id INT NOT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F4167ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('CREATE INDEX IDX_BC91F4167ECF78B0 ON resource (cours_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours CHANGE image_url image_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F4167ECF78B0');
        $this->addSql('DROP INDEX IDX_BC91F4167ECF78B0 ON resource');
        $this->addSql('ALTER TABLE resource DROP cours_id');
    }
}
