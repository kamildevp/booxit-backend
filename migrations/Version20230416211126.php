<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230416211126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE time_table_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE week_day_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE time_table (id INT NOT NULL, schedule_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B35B6E3AA40BC2D5 ON time_table (schedule_id)');
        $this->addSql('CREATE TABLE week_day (id INT NOT NULL, time_table_id INT NOT NULL, name VARCHAR(255) NOT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_256D1361487A6B90 ON week_day (time_table_id)');
        $this->addSql('ALTER TABLE time_table ADD CONSTRAINT FK_B35B6E3AA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE week_day ADD CONSTRAINT FK_256D1361487A6B90 FOREIGN KEY (time_table_id) REFERENCES time_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule ADD time_table_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB487A6B90 FOREIGN KEY (time_table_id) REFERENCES time_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A3811FB487A6B90 ON schedule (time_table_id)');
        $this->addSql('ALTER TABLE working_hours DROP CONSTRAINT fk_d72cdc3da40bc2d5');
        $this->addSql('DROP INDEX idx_d72cdc3da40bc2d5');
        $this->addSql('ALTER TABLE working_hours RENAME COLUMN schedule_id TO time_table_id');
        $this->addSql('ALTER TABLE working_hours ADD CONSTRAINT FK_D72CDC3D487A6B90 FOREIGN KEY (time_table_id) REFERENCES time_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D72CDC3D487A6B90 ON working_hours (time_table_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE schedule DROP CONSTRAINT FK_5A3811FB487A6B90');
        $this->addSql('ALTER TABLE working_hours DROP CONSTRAINT FK_D72CDC3D487A6B90');
        $this->addSql('DROP SEQUENCE time_table_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE week_day_id_seq CASCADE');
        $this->addSql('ALTER TABLE time_table DROP CONSTRAINT FK_B35B6E3AA40BC2D5');
        $this->addSql('ALTER TABLE week_day DROP CONSTRAINT FK_256D1361487A6B90');
        $this->addSql('DROP TABLE time_table');
        $this->addSql('DROP TABLE week_day');
        $this->addSql('DROP INDEX UNIQ_5A3811FB487A6B90');
        $this->addSql('ALTER TABLE schedule DROP time_table_id');
        $this->addSql('DROP INDEX IDX_D72CDC3D487A6B90');
        $this->addSql('ALTER TABLE working_hours RENAME COLUMN time_table_id TO schedule_id');
        $this->addSql('ALTER TABLE working_hours ADD CONSTRAINT fk_d72cdc3da40bc2d5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d72cdc3da40bc2d5 ON working_hours (schedule_id)');
    }
}
