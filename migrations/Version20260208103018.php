<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208103018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE generation (id INT AUTO_INCREMENT NOT NULL, file VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_D3266C3BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE generation_user_contact (generation_id INT NOT NULL, user_contact_id INT NOT NULL, INDEX IDX_59D39840553A6EC4 (generation_id), INDEX IDX_59D3984040C6E3A6 (user_contact_id), PRIMARY KEY (generation_id, user_contact_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plan (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, role VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, special_price DOUBLE PRECISION DEFAULT NULL, special_price_from DATETIME DEFAULT NULL, special_price_to DATETIME DEFAULT NULL, active TINYINT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_contact (id INT AUTO_INCREMENT NOT NULL, lastname VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, user_id INT NOT NULL, INDEX IDX_146FF832A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE generation ADD CONSTRAINT FK_D3266C3BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE generation_user_contact ADD CONSTRAINT FK_59D39840553A6EC4 FOREIGN KEY (generation_id) REFERENCES generation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE generation_user_contact ADD CONSTRAINT FK_59D3984040C6E3A6 FOREIGN KEY (user_contact_id) REFERENCES user_contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_contact ADD CONSTRAINT FK_146FF832A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE actor DROP FOREIGN KEY `FK_447556F97E9E4C8C`');
        $this->addSql('ALTER TABLE actor_movie DROP FOREIGN KEY `FK_39DA19FB10DAF24A`');
        $this->addSql('ALTER TABLE actor_movie DROP FOREIGN KEY `FK_39DA19FB8F93B6FC`');
        $this->addSql('ALTER TABLE category_movie DROP FOREIGN KEY `FK_F56DBD2612469DE2`');
        $this->addSql('ALTER TABLE category_movie DROP FOREIGN KEY `FK_F56DBD268F93B6FC`');
        $this->addSql('ALTER TABLE director DROP FOREIGN KEY `FK_1E90D3F07E9E4C8C`');
        $this->addSql('ALTER TABLE movie DROP FOREIGN KEY `FK_1D5EF26F5BB66C05`');
        $this->addSql('ALTER TABLE movie DROP FOREIGN KEY `FK_1D5EF26F899FB366`');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE actor_movie');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_movie');
        $this->addSql('DROP TABLE director');
        $this->addSql('DROP TABLE media_object');
        $this->addSql('DROP TABLE movie');
        $this->addSql('DROP INDEX UNIQ_8D93D649848473FE ON user');
        $this->addSql('ALTER TABLE user ADD dob DATETIME DEFAULT NULL, ADD favorite_color VARCHAR(50) DEFAULT NULL, ADD phone VARCHAR(20) DEFAULT NULL, ADD plan_id INT DEFAULT NULL, DROP created_at, DROP api_key_hash, DROP api_key_prefix, DROP api_key_enabled, DROP api_key_created_at, DROP api_key_last_used_at, DROP api_tokens, DROP api_token_limit, DROP api_token_reset_at, DROP two_factor_enabled, DROP two_factor_backup_codes, CHANGE roles roles JSON NOT NULL, CHANGE two_factor_secret photo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649E899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649E899029B ON user (plan_id)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actor (id INT AUTO_INCREMENT NOT NULL, photo_id INT DEFAULT NULL, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, firstname VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, dob DATE DEFAULT NULL, dod DATE DEFAULT NULL, bio LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_447556F97E9E4C8C (photo_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE actor_movie (actor_id INT NOT NULL, movie_id INT NOT NULL, INDEX IDX_39DA19FB8F93B6FC (movie_id), INDEX IDX_39DA19FB10DAF24A (actor_id), PRIMARY KEY (actor_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE category_movie (category_id INT NOT NULL, movie_id INT NOT NULL, INDEX IDX_F56DBD2612469DE2 (category_id), INDEX IDX_F56DBD268F93B6FC (movie_id), PRIMARY KEY (category_id, movie_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE director (id INT AUTO_INCREMENT NOT NULL, photo_id INT DEFAULT NULL, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, firstname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, dob DATETIME NOT NULL, dod DATETIME DEFAULT NULL, bio LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1E90D3F07E9E4C8C (photo_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE media_object (id INT AUTO_INCREMENT NOT NULL, file_path VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE movie (id INT AUTO_INCREMENT NOT NULL, director_id INT DEFAULT NULL, poster_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, release_date DATE DEFAULT NULL, budget DOUBLE PRECISION DEFAULT NULL, duration INT DEFAULT NULL, nb_entries INT DEFAULT NULL, url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1D5EF26F899FB366 (director_id), INDEX IDX_1D5EF26F5BB66C05 (poster_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT `FK_447556F97E9E4C8C` FOREIGN KEY (photo_id) REFERENCES media_object (id)');
        $this->addSql('ALTER TABLE actor_movie ADD CONSTRAINT `FK_39DA19FB10DAF24A` FOREIGN KEY (actor_id) REFERENCES actor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actor_movie ADD CONSTRAINT `FK_39DA19FB8F93B6FC` FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_movie ADD CONSTRAINT `FK_F56DBD2612469DE2` FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_movie ADD CONSTRAINT `FK_F56DBD268F93B6FC` FOREIGN KEY (movie_id) REFERENCES movie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE director ADD CONSTRAINT `FK_1E90D3F07E9E4C8C` FOREIGN KEY (photo_id) REFERENCES media_object (id)');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT `FK_1D5EF26F5BB66C05` FOREIGN KEY (poster_id) REFERENCES media_object (id)');
        $this->addSql('ALTER TABLE movie ADD CONSTRAINT `FK_1D5EF26F899FB366` FOREIGN KEY (director_id) REFERENCES director (id)');
        $this->addSql('ALTER TABLE generation DROP FOREIGN KEY FK_D3266C3BA76ED395');
        $this->addSql('ALTER TABLE generation_user_contact DROP FOREIGN KEY FK_59D39840553A6EC4');
        $this->addSql('ALTER TABLE generation_user_contact DROP FOREIGN KEY FK_59D3984040C6E3A6');
        $this->addSql('ALTER TABLE user_contact DROP FOREIGN KEY FK_146FF832A76ED395');
        $this->addSql('DROP TABLE generation');
        $this->addSql('DROP TABLE generation_user_contact');
        $this->addSql('DROP TABLE plan');
        $this->addSql('DROP TABLE user_contact');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E899029B');
        $this->addSql('DROP INDEX IDX_8D93D649E899029B ON user');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD api_key_hash VARCHAR(64) DEFAULT NULL, ADD api_key_prefix VARCHAR(16) DEFAULT NULL, ADD api_key_enabled TINYINT DEFAULT NULL, ADD api_key_created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD api_key_last_used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD api_tokens INT NOT NULL, ADD api_token_limit INT NOT NULL, ADD api_token_reset_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD two_factor_enabled TINYINT NOT NULL, ADD two_factor_backup_codes JSON DEFAULT NULL COMMENT \'(DC2Type:json)\', DROP dob, DROP favorite_color, DROP phone, DROP plan_id, CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE photo two_factor_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649848473FE ON user (api_key_hash)');
    }
}
