<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302224239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_sessions DROP FOREIGN KEY FK_7AED7913A76ED395');
        $this->addSql('DROP TABLE user_sessions');
        $this->addSql('ALTER TABLE event ADD predicted_score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE notes CHANGE tag tag VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_sessions (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, login_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', logout_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', session_duration_minutes INT DEFAULT NULL, INDEX IDX_7AED7913A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_sessions ADD CONSTRAINT FK_7AED7913A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event DROP predicted_score');
        $this->addSql('ALTER TABLE notes CHANGE tag tag VARCHAR(50) DEFAULT NULL');
    }
}
