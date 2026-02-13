<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213024422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, comment LONGTEXT DEFAULT NULL, session_id INT NOT NULL, UNIQUE INDEX UNIQ_794381C6613FECDF (session_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE user CHANGE wallet_balance wallet_balance DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6613FECDF');
        $this->addSql('DROP TABLE review');
        $this->addSql('ALTER TABLE user CHANGE wallet_balance wallet_balance DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
