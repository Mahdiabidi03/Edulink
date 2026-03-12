<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303043427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_sentiment_log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, text VARCHAR(255) DEFAULT NULL, sentiment VARCHAR(50) DEFAULT NULL, confidence DOUBLE PRECISION DEFAULT NULL, INDEX IDX_CB928EFBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ai_sentiment_log ADD CONSTRAINT FK_CB928EFBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_sessions DROP FOREIGN KEY FK_7AED7913A76ED395');
        $this->addSql('DROP TABLE user_sessions');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_sessions (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, login_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', logout_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', session_duration_minutes INT DEFAULT NULL, INDEX IDX_7AED7913A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_sessions ADD CONSTRAINT FK_7AED7913A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ai_sentiment_log DROP FOREIGN KEY FK_CB928EFBA76ED395');
        $this->addSql('DROP TABLE ai_sentiment_log');
    }
}
