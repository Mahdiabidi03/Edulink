<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223113849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personal_tasks (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_completed TINYINT NOT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_8012381A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, points INT DEFAULT 0 NOT NULL, type VARCHAR(50) NOT NULL, order_index INT NOT NULL, is_required TINYINT NOT NULL, created_at DATETIME NOT NULL, challenge_id INT NOT NULL, INDEX IDX_527EDB2598A21AC6 (challenge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE personal_tasks ADD CONSTRAINT FK_8012381A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB2598A21AC6 FOREIGN KEY (challenge_id) REFERENCES challenge (id)');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY `FK_50586597A76ED395`');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('ALTER TABLE user CHANGE wallet_balance wallet_balance DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tasks (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_completed TINYINT NOT NULL, created_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_50586597A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT `FK_50586597A76ED395` FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE personal_tasks DROP FOREIGN KEY FK_8012381A76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB2598A21AC6');
        $this->addSql('DROP TABLE personal_tasks');
        $this->addSql('DROP TABLE task');
        $this->addSql('ALTER TABLE user CHANGE wallet_balance wallet_balance DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
