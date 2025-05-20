<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520171023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE advice (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, description LONGTEXT NOT NULL, month JSON NOT NULL COMMENT '(DC2Type:json)', INDEX IDX_64820E8DB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE advice ADD CONSTRAINT FK_64820E8DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conseil DROP FOREIGN KEY FK_3F3F0681B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conseil
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE ville city VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE conseil (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, description LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, month INT NOT NULL, INDEX IDX_3F3F0681B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conseil ADD CONSTRAINT FK_3F3F0681B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE advice DROP FOREIGN KEY FK_64820E8DB03A8386
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE advice
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE city ville VARCHAR(255) NOT NULL
        SQL);
    }
}
