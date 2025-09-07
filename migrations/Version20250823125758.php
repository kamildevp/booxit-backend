<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250823125758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create refresh_token table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE refresh_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE refresh_token (id INT NOT NULL, app_user_id INT NOT NULL, value TEXT DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C74F21954A3353D8 ON refresh_token (app_user_id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F21954A3353D8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE refresh_token DROP CONSTRAINT FK_C74F21954A3353D8');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP SEQUENCE refresh_token_id_seq CASCADE');
    }
}
