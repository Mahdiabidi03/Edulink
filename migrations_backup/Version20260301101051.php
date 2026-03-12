<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301101051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE community_post CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE cours CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE enrollment ADD login_frequency_per_week DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD avg_session_minutes DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD assignments_completed DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD quiz_average_score DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD forum_interactions DOUBLE PRECISION DEFAULT \'0\' NOT NULL, ADD video_watch_percent DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE enrolled_at enrolled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE completed_at completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE help_request CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE matiere CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE message CHANGE timestamp timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE post_comment CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE post_report CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE session CHANGE started_at started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE ended_at ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE task CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user_challenge CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE community_post CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE cours CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE enrollment DROP login_frequency_per_week, DROP avg_session_minutes, DROP assignments_completed, DROP quiz_average_score, DROP forum_interactions, DROP video_watch_percent, CHANGE enrolled_at enrolled_at DATETIME NOT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE help_request CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE matiere CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message CHANGE timestamp timestamp DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post_comment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post_report CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE session CHANGE started_at started_at DATETIME NOT NULL, CHANGE ended_at ended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE task CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user_challenge CHANGE updated_at updated_at DATETIME DEFAULT NULL');
    }
}
