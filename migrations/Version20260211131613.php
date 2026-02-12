<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211131613 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing status column to resource table';
    }

    public function up(Schema $schema): void
    {
        // Add status column that was missing from previous migration
        $this->addSql('ALTER TABLE resource ADD status VARCHAR(20) DEFAULT \'APPROVED\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resource DROP status');
    }
}
