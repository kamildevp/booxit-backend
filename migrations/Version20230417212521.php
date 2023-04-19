<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230417212521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE time_window_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE time_window (id INT NOT NULL, working_hours_id INT NOT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D212B62B55A755D4 ON time_window (working_hours_id)');
        $this->addSql('ALTER TABLE time_window ADD CONSTRAINT FK_D212B62B55A755D4 FOREIGN KEY (working_hours_id) REFERENCES working_hours (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours DROP time_windows');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE time_window_id_seq CASCADE');
        $this->addSql('ALTER TABLE time_window DROP CONSTRAINT FK_D212B62B55A755D4');
        $this->addSql('DROP TABLE time_window');
        $this->addSql('ALTER TABLE working_hours ADD time_windows TEXT NOT NULL');
        $this->addSql('COMMENT ON COLUMN working_hours.time_windows IS \'(DC2Type:array)\'');
    }
}
