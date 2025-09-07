<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250825180847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create file table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE file (id INT NOT NULL, uploaded_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, size NUMERIC(10, 0) NOT NULL, type VARCHAR(255) NOT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8C9F3610A2B28FE8 ON file (uploaded_by_id)');
        $this->addSql('COMMENT ON COLUMN file.uploaded_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F3610A2B28FE8');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
    }
}
