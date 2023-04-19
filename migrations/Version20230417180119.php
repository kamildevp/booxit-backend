<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230417180119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE time_table_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE week_day_id_seq CASCADE');
        $this->addSql('ALTER TABLE week_day DROP CONSTRAINT fk_256d1361487a6b90');
        $this->addSql('ALTER TABLE time_table DROP CONSTRAINT fk_b35b6e3aa40bc2d5');
        $this->addSql('DROP TABLE week_day');
        $this->addSql('DROP TABLE time_table');
        $this->addSql('ALTER TABLE working_hours ADD day VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE working_hours DROP date');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE time_table_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE week_day_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE week_day (id INT NOT NULL, time_table_id INT NOT NULL, name VARCHAR(255) NOT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_256d1361487a6b90 ON week_day (time_table_id)');
        $this->addSql('CREATE TABLE time_table (id INT NOT NULL, schedule_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_b35b6e3aa40bc2d5 ON time_table (schedule_id)');
        $this->addSql('ALTER TABLE week_day ADD CONSTRAINT fk_256d1361487a6b90 FOREIGN KEY (time_table_id) REFERENCES time_table (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_table ADD CONSTRAINT fk_b35b6e3aa40bc2d5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours ADD date DATE NOT NULL');
        $this->addSql('ALTER TABLE working_hours DROP day');
    }
}
