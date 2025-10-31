<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250907145318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create organization_member table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE organization_member_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE organization_member (id INT NOT NULL, organization_id INT NOT NULL, app_user_id INT NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_756A2A8D32C8A3DE ON organization_member (organization_id)');
        $this->addSql('CREATE INDEX IDX_756A2A8D4A3353D8 ON organization_member (app_user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_org_user ON organization_member (organization_id, app_user_id)');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D4A3353D8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8D32C8A3DE');
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8D4A3353D8');
        $this->addSql('DROP TABLE organization_member');
        $this->addSql('DROP SEQUENCE organization_member_id_seq CASCADE');
    }
}
