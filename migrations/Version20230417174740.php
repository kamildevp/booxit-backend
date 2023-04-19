<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230417174740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule DROP CONSTRAINT fk_5a3811fb487a6b90');
        $this->addSql('DROP INDEX uniq_5a3811fb487a6b90');
        $this->addSql('ALTER TABLE schedule DROP time_table_id');
        $this->addSql('ALTER TABLE working_hours DROP CONSTRAINT fk_d72cdc3d487a6b90');
        $this->addSql('DROP INDEX idx_d72cdc3d487a6b90');
        $this->addSql('ALTER TABLE working_hours RENAME COLUMN time_table_id TO schedule_id');
        $this->addSql('ALTER TABLE working_hours ADD CONSTRAINT FK_D72CDC3DA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D72CDC3DA40BC2D5 ON working_hours (schedule_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE schedule ADD time_table_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT fk_5a3811fb487a6b90 FOREIGN KEY (time_table_id) REFERENCES time_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_5a3811fb487a6b90 ON schedule (time_table_id)');
        $this->addSql('ALTER TABLE working_hours DROP CONSTRAINT FK_D72CDC3DA40BC2D5');
        $this->addSql('DROP INDEX IDX_D72CDC3DA40BC2D5');
        $this->addSql('ALTER TABLE working_hours RENAME COLUMN schedule_id TO time_table_id');
        $this->addSql('ALTER TABLE working_hours ADD CONSTRAINT fk_d72cdc3d487a6b90 FOREIGN KEY (time_table_id) REFERENCES time_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d72cdc3d487a6b90 ON working_hours (time_table_id)');
    }
}
