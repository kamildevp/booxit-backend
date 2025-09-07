<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250907132527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create organization table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE organization_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE organization (id INT NOT NULL, banner_file_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C1EE637C5E237E06 ON organization (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C1EE637CC79650AF ON organization (banner_file_id)');
        $this->addSql('CREATE INDEX IDX_C1EE637CB03A8386 ON organization (created_by_id)');
        $this->addSql('CREATE INDEX IDX_C1EE637C896DBBDE ON organization (updated_by_id)');
        $this->addSql('COMMENT ON COLUMN organization.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN organization.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637CC79650AF FOREIGN KEY (banner_file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637CB03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637C896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT FK_C1EE637CC79650AF');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT FK_C1EE637CB03A8386');
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT FK_C1EE637C896DBBDE');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP SEQUENCE organization_id_seq CASCADE');
    }
}
