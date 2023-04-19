<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230415182539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE working_hours_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE working_hours (id INT NOT NULL, schedule_id INT NOT NULL, date DATE NOT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D72CDC3DA40BC2D5 ON working_hours (schedule_id)');
        $this->addSql('ALTER TABLE working_hours ADD CONSTRAINT FK_D72CDC3DA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE working_hours_id_seq CASCADE');
        $this->addSql('ALTER TABLE working_hours DROP CONSTRAINT FK_D72CDC3DA40BC2D5');
        $this->addSql('DROP TABLE working_hours');
    }
}
