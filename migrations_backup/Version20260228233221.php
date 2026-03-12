<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228233221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personal_tasks (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, is_completed TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, INDEX IDX_8012381A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, challenge_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, points INT DEFAULT 0 NOT NULL, is_required TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_527EDB2598A21AC6 (challenge_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_task (id INT AUTO_INCREMENT NOT NULL, task_id INT NOT NULL, user_challenge_id INT NOT NULL, is_completed TINYINT(1) NOT NULL, INDEX IDX_28FF97EC8DB60186 (task_id), INDEX IDX_28FF97EC186E66C4 (user_challenge_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personal_tasks ADD CONSTRAINT FK_8012381A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2598A21AC6 FOREIGN KEY (challenge_id) REFERENCES challenge (id)');
        $this->addSql('ALTER TABLE user_task ADD CONSTRAINT FK_28FF97EC8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE user_task ADD CONSTRAINT FK_28FF97EC186E66C4 FOREIGN KEY (user_challenge_id) REFERENCES user_challenge (id)');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597A76ED395');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('ALTER TABLE community_post CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE cours CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE enrollment CHANGE enrolled_at enrolled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE completed_at completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE help_request ADD category VARCHAR(50) DEFAULT NULL, ADD difficulty VARCHAR(20) DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE matiere CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE message ADD attachment_name VARCHAR(255) DEFAULT NULL, ADD attachment_size INT DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE content content LONGTEXT DEFAULT NULL, CHANGE timestamp timestamp DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE post_comment CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE post_report CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE session ADD summary LONGTEXT DEFAULT NULL, ADD jitsi_room_id VARCHAR(100) DEFAULT NULL, CHANGE started_at started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE ended_at ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD is_verified TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user_challenge ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_completed TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, INDEX IDX_50586597A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE personal_tasks DROP FOREIGN KEY FK_8012381A76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2598A21AC6');
        $this->addSql('ALTER TABLE user_task DROP FOREIGN KEY FK_28FF97EC8DB60186');
        $this->addSql('ALTER TABLE user_task DROP FOREIGN KEY FK_28FF97EC186E66C4');
        $this->addSql('DROP TABLE personal_tasks');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE user_task');
        $this->addSql('ALTER TABLE community_post CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE cours CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE enrollment CHANGE enrolled_at enrolled_at DATETIME NOT NULL, CHANGE completed_at completed_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE help_request DROP category, DROP difficulty, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE matiere CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE message DROP attachment_name, DROP attachment_size, DROP updated_at, CHANGE content content LONGTEXT NOT NULL, CHANGE timestamp timestamp DATETIME NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE notification CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post_comment CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post_report CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE session DROP summary, DROP jitsi_room_id, CHANGE started_at started_at DATETIME NOT NULL, CHANGE ended_at ended_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP is_verified');
        $this->addSql('ALTER TABLE user_challenge DROP updated_at');
    }
}
