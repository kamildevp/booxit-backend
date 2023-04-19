<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230415180905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE schedule_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE schedule (id INT NOT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A3811FB32C8A3DE ON schedule (organization_id)');
        $this->addSql('CREATE TABLE schedule_service (schedule_id INT NOT NULL, service_id INT NOT NULL, PRIMARY KEY(schedule_id, service_id))');
        $this->addSql('CREATE INDEX IDX_6CF7B663A40BC2D5 ON schedule_service (schedule_id)');
        $this->addSql('CREATE INDEX IDX_6CF7B663ED5CA9E6 ON schedule_service (service_id)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_service ADD CONSTRAINT FK_6CF7B663A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_service ADD CONSTRAINT FK_6CF7B663ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE schedule_id_seq CASCADE');
        $this->addSql('ALTER TABLE schedule DROP CONSTRAINT FK_5A3811FB32C8A3DE');
        $this->addSql('ALTER TABLE schedule_service DROP CONSTRAINT FK_6CF7B663A40BC2D5');
        $this->addSql('ALTER TABLE schedule_service DROP CONSTRAINT FK_6CF7B663ED5CA9E6');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE schedule_service');
    }
}
