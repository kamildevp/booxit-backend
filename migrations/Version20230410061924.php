<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230410061924 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE organization_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE organization_member_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE organization (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organization_member (id INT NOT NULL, organization_id INT NOT NULL, app_user_id INT NOT NULL, roles TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_756A2A8D32C8A3DE ON organization_member (organization_id)');
        $this->addSql('CREATE INDEX IDX_756A2A8D4A3353D8 ON organization_member (app_user_id)');
        $this->addSql('COMMENT ON COLUMN organization_member.roles IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D4A3353D8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE organization_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE organization_member_id_seq CASCADE');
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8D32C8A3DE');
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8D4A3353D8');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE organization_member');
    }
}
