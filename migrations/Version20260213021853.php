<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213021853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(20) NOT NULL, owner_id INT DEFAULT NULL, INDEX IDX_64C19C17E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE challenge (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(120) NOT NULL, goal VARCHAR(255) NOT NULL, reward_points INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE community_post (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, likes_count INT NOT NULL, tag VARCHAR(100) DEFAULT NULL, author_id INT NOT NULL, INDEX IDX_9BDB8647F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE help_request (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, bounty INT NOT NULL, is_ticket TINYINT NOT NULL, created_at DATETIME NOT NULL, close_reason VARCHAR(30) DEFAULT NULL, student_id INT NOT NULL, INDEX IDX_658D7043CB944F1A (student_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, timestamp DATETIME NOT NULL, is_toxic TINYINT NOT NULL, session_id INT NOT NULL, sender_id INT NOT NULL, INDEX IDX_B6BD307F613FECDF (session_id), INDEX IDX_B6BD307FF624B39D (sender_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notes (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, tag VARCHAR(50) DEFAULT NULL, attachment VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, category_id INT DEFAULT NULL, INDEX IDX_11BA68CA76ED395 (user_id), INDEX IDX_11BA68C12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post_comment (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, author_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_A99CE55FF675F31B (author_id), INDEX IDX_A99CE55F4B89032C (post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post_reaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_1B3A8E56A76ED395 (user_id), INDEX IDX_1B3A8E564B89032C (post_id), UNIQUE INDEX UNIQ_1B3A8E56A76ED3954B89032C (user_id, post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE post_report (id INT AUTO_INCREMENT NOT NULL, reason VARCHAR(50) NOT NULL, details LONGTEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, reporter_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_F40D93E1E1CFE6F5 (reporter_id), INDEX IDX_F40D93E14B89032C (post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE reminders (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, reminder_time DATETIME NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_6D92B9D4A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE session (id INT AUTO_INCREMENT NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME DEFAULT NULL, is_active TINYINT NOT NULL, help_request_id INT NOT NULL, tutor_id INT NOT NULL, UNIQUE INDEX UNIQ_D044D5D4A8AB70A7 (help_request_id), INDEX IDX_D044D5D4208F64F1 (tutor_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_completed TINYINT NOT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_50586597A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user_challenge (id INT AUTO_INCREMENT NOT NULL, progress VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, proof_file_name VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, challenge_id INT NOT NULL, INDEX IDX_D7E904B5A76ED395 (user_id), INDEX IDX_D7E904B598A21AC6 (challenge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C17E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE community_post ADD CONSTRAINT FK_9BDB8647F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE help_request ADD CONSTRAINT FK_658D7043CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F613FECDF FOREIGN KEY (session_id) REFERENCES session (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notes ADD CONSTRAINT FK_11BA68C12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE post_comment ADD CONSTRAINT FK_A99CE55FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_comment ADD CONSTRAINT FK_A99CE55F4B89032C FOREIGN KEY (post_id) REFERENCES community_post (id)');
        $this->addSql('ALTER TABLE post_reaction ADD CONSTRAINT FK_1B3A8E56A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_reaction ADD CONSTRAINT FK_1B3A8E564B89032C FOREIGN KEY (post_id) REFERENCES community_post (id)');
        $this->addSql('ALTER TABLE post_report ADD CONSTRAINT FK_F40D93E1E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post_report ADD CONSTRAINT FK_F40D93E14B89032C FOREIGN KEY (post_id) REFERENCES community_post (id)');
        $this->addSql('ALTER TABLE reminders ADD CONSTRAINT FK_6D92B9D4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4A8AB70A7 FOREIGN KEY (help_request_id) REFERENCES help_request (id)');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D4208F64F1 FOREIGN KEY (tutor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_challenge ADD CONSTRAINT FK_D7E904B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_challenge ADD CONSTRAINT FK_D7E904B598A21AC6 FOREIGN KEY (challenge_id) REFERENCES challenge (id)');
        $this->addSql('ALTER TABLE cours DROP image_url, CHANGE reward_points xp INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY `FK_DBDCD7E17ECF78B0`');
        $this->addSql('ALTER TABLE enrollment ADD completed_at DATETIME DEFAULT NULL, ADD completed_resources JSON DEFAULT \'[]\' NOT NULL, CHANGE cours_id cours_id INT NOT NULL');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E17ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE matiere ADD status VARCHAR(20) DEFAULT \'APPROVED\' NOT NULL, ADD image_url VARCHAR(255) DEFAULT NULL, ADD created_at DATETIME DEFAULT NULL, CHANGE author_id creator_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE matiere ADD CONSTRAINT FK_9014574A61220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_9014574A61220EA6 ON matiere (creator_id)');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY `FK_BC91F4167ECF78B0`');
        $this->addSql('ALTER TABLE resource CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F4167ECF78B0 FOREIGN KEY (cours_id) REFERENCES cours (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD xp INT DEFAULT 0 NOT NULL, CHANGE wallet_balance wallet_balance DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C17E3C61F9');
        $this->addSql('ALTER TABLE community_post DROP FOREIGN KEY FK_9BDB8647F675F31B');
        $this->addSql('ALTER TABLE help_request DROP FOREIGN KEY FK_658D7043CB944F1A');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F613FECDF');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68CA76ED395');
        $this->addSql('ALTER TABLE notes DROP FOREIGN KEY FK_11BA68C12469DE2');
        $this->addSql('ALTER TABLE post_comment DROP FOREIGN KEY FK_A99CE55FF675F31B');
        $this->addSql('ALTER TABLE post_comment DROP FOREIGN KEY FK_A99CE55F4B89032C');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E56A76ED395');
        $this->addSql('ALTER TABLE post_reaction DROP FOREIGN KEY FK_1B3A8E564B89032C');
        $this->addSql('ALTER TABLE post_report DROP FOREIGN KEY FK_F40D93E1E1CFE6F5');
        $this->addSql('ALTER TABLE post_report DROP FOREIGN KEY FK_F40D93E14B89032C');
        $this->addSql('ALTER TABLE reminders DROP FOREIGN KEY FK_6D92B9D4A76ED395');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4A8AB70A7');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4208F64F1');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597A76ED395');
        $this->addSql('ALTER TABLE user_challenge DROP FOREIGN KEY FK_D7E904B5A76ED395');
        $this->addSql('ALTER TABLE user_challenge DROP FOREIGN KEY FK_D7E904B598A21AC6');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE challenge');
        $this->addSql('DROP TABLE community_post');
        $this->addSql('DROP TABLE help_request');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notes');
        $this->addSql('DROP TABLE post_comment');
        $this->addSql('DROP TABLE post_reaction');
        $this->addSql('DROP TABLE post_report');
        $this->addSql('DROP TABLE reminders');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE user_challenge');
        $this->addSql('ALTER TABLE cours ADD image_url VARCHAR(255) DEFAULT NULL, CHANGE xp reward_points INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enrollment DROP FOREIGN KEY FK_DBDCD7E17ECF78B0');
        $this->addSql('ALTER TABLE enrollment DROP completed_at, DROP completed_resources, CHANGE cours_id cours_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT `FK_DBDCD7E17ECF78B0` FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE matiere DROP FOREIGN KEY FK_9014574A61220EA6');
        $this->addSql('DROP INDEX IDX_9014574A61220EA6 ON matiere');
        $this->addSql('ALTER TABLE matiere DROP status, DROP image_url, DROP created_at, CHANGE creator_id author_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F4167ECF78B0');
        $this->addSql('ALTER TABLE resource CHANGE url url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT `FK_BC91F4167ECF78B0` FOREIGN KEY (cours_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE user DROP xp, CHANGE wallet_balance wallet_balance INT NOT NULL');
    }
}
