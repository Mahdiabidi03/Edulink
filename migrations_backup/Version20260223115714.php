<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223115714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_task (id INT AUTO_INCREMENT NOT NULL, is_completed TINYINT NOT NULL, task_id INT NOT NULL, user_challenge_id INT NOT NULL, INDEX IDX_28FF97EC8DB60186 (task_id), INDEX IDX_28FF97EC186E66C4 (user_challenge_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user_task ADD CONSTRAINT FK_28FF97EC8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE user_task ADD CONSTRAINT FK_28FF97EC186E66C4 FOREIGN KEY (user_challenge_id) REFERENCES user_challenge (id)');
        $this->addSql('ALTER TABLE user CHANGE wallet_balance wallet_balance DOUBLE PRECISION DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_task DROP FOREIGN KEY FK_28FF97EC8DB60186');
        $this->addSql('ALTER TABLE user_task DROP FOREIGN KEY FK_28FF97EC186E66C4');
        $this->addSql('DROP TABLE user_task');
        $this->addSql('ALTER TABLE user CHANGE wallet_balance wallet_balance DOUBLE PRECISION DEFAULT \'0\' NOT NULL');
    }
}
