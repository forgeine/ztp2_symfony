<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260605125109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, title VARCHAR(64) NOT NULL, slug VARCHAR(64) NOT NULL, UNIQUE INDEX uq_categories_title (title), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, content LONGTEXT NOT NULL, newspaper_id INT DEFAULT NULL, author_id INT DEFAULT NULL, INDEX IDX_5F9E962AC5D975FA (newspaper_id), INDEX IDX_5F9E962AF675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE newspapers (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, content LONGTEXT NOT NULL, slug VARCHAR(255) NOT NULL, average_rating DOUBLE PRECISION DEFAULT NULL, category_id INT NOT NULL, author_id INT NOT NULL, INDEX IDX_1F130BF812469DE2 (category_id), INDEX IDX_1F130BF8F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE newspapers_tags (newspaper_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_4EE66B17C5D975FA (newspaper_id), INDEX IDX_4EE66B17BAD26311 (tag_id), PRIMARY KEY (newspaper_id, tag_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ratings (id INT AUTO_INCREMENT NOT NULL, value INT NOT NULL, newspaper_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_CEB607C9C5D975FA (newspaper_id), INDEX IDX_CEB607C9A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, slug VARCHAR(64) NOT NULL, title VARCHAR(64) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX email_idx (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AC5D975FA FOREIGN KEY (newspaper_id) REFERENCES newspapers (id)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE newspapers ADD CONSTRAINT FK_1F130BF812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE newspapers ADD CONSTRAINT FK_1F130BF8F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE newspapers_tags ADD CONSTRAINT FK_4EE66B17C5D975FA FOREIGN KEY (newspaper_id) REFERENCES newspapers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE newspapers_tags ADD CONSTRAINT FK_4EE66B17BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C9C5D975FA FOREIGN KEY (newspaper_id) REFERENCES newspapers (id)');
        $this->addSql('ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AC5D975FA');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AF675F31B');
        $this->addSql('ALTER TABLE newspapers DROP FOREIGN KEY FK_1F130BF812469DE2');
        $this->addSql('ALTER TABLE newspapers DROP FOREIGN KEY FK_1F130BF8F675F31B');
        $this->addSql('ALTER TABLE newspapers_tags DROP FOREIGN KEY FK_4EE66B17C5D975FA');
        $this->addSql('ALTER TABLE newspapers_tags DROP FOREIGN KEY FK_4EE66B17BAD26311');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C9C5D975FA');
        $this->addSql('ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C9A76ED395');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE newspapers');
        $this->addSql('DROP TABLE newspapers_tags');
        $this->addSql('DROP TABLE ratings');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
