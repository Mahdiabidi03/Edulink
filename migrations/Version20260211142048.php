<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211142048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY `FK_FDCA8C9CF46CD258`');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CF46CD258 FOREIGN KEY (matiere_id) REFERENCES matiere (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY `FK_BC91F416579F4768`');
        $this->addSql('DROP INDEX IDX_BC91F416579F4768 ON resource');
        $this->addSql('ALTER TABLE resource DROP chapter_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CF46CD258');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT `FK_FDCA8C9CF46CD258` FOREIGN KEY (matiere_id) REFERENCES matiere (id)');
        $this->addSql('ALTER TABLE resource ADD chapter_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT `FK_BC91F416579F4768` FOREIGN KEY (chapter_id) REFERENCES chapter (id)');
        $this->addSql('CREATE INDEX IDX_BC91F416579F4768 ON resource (chapter_id)');
    }
}
