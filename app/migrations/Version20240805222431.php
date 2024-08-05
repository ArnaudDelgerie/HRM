<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240805222431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE day_leave_request (id INT AUTO_INCREMENT NOT NULL, leave_request_id INT NOT NULL, day_date DATE NOT NULL, period VARCHAR(255) NOT NULL, INDEX IDX_2D269E38F2E1C15D (leave_request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE leave_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, processed_by_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, nb_days DOUBLE PRECISION NOT NULL, state VARCHAR(255) NOT NULL, request_comment LONGTEXT DEFAULT NULL, response_comment LONGTEXT DEFAULT NULL, first_day DATE NOT NULL, last_day DATE NOT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_7DC8F778A76ED395 (user_id), INDEX IDX_7DC8F7782FFD4FD3 (processed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE day_leave_request ADD CONSTRAINT FK_2D269E38F2E1C15D FOREIGN KEY (leave_request_id) REFERENCES leave_request (id)');
        $this->addSql('ALTER TABLE leave_request ADD CONSTRAINT FK_7DC8F778A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE leave_request ADD CONSTRAINT FK_7DC8F7782FFD4FD3 FOREIGN KEY (processed_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE day_leave_request DROP FOREIGN KEY FK_2D269E38F2E1C15D');
        $this->addSql('ALTER TABLE leave_request DROP FOREIGN KEY FK_7DC8F778A76ED395');
        $this->addSql('ALTER TABLE leave_request DROP FOREIGN KEY FK_7DC8F7782FFD4FD3');
        $this->addSql('DROP TABLE day_leave_request');
        $this->addSql('DROP TABLE leave_request');
    }
}
