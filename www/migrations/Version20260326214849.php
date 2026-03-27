<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260326214849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pdf_queue (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, parameters JSON NOT NULL, status VARCHAR(20) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, processed_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_EE6A32B9A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE pdf_queue ADD CONSTRAINT FK_EE6A32B9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pdf_queue DROP FOREIGN KEY FK_EE6A32B9A76ED395');
        $this->addSql('DROP TABLE pdf_queue');
    }
}
