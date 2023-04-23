<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230420111118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE working_hours_time_window (working_hours_id INT NOT NULL, time_window_id INT NOT NULL, PRIMARY KEY(working_hours_id, time_window_id))');
        $this->addSql('CREATE INDEX IDX_266B86ED55A755D4 ON working_hours_time_window (working_hours_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_266B86ED137F1495 ON working_hours_time_window (time_window_id)');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT FK_266B86ED55A755D4 FOREIGN KEY (working_hours_id) REFERENCES working_hours (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT FK_266B86ED137F1495 FOREIGN KEY (time_window_id) REFERENCES time_window (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE time_window DROP CONSTRAINT fk_d212b62b55a755d4');
        $this->addSql('DROP INDEX idx_d212b62b55a755d4');
        $this->addSql('ALTER TABLE time_window DROP working_hours_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT FK_266B86ED55A755D4');
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT FK_266B86ED137F1495');
        $this->addSql('DROP TABLE working_hours_time_window');
        $this->addSql('ALTER TABLE time_window ADD working_hours_id INT NOT NULL');
        $this->addSql('ALTER TABLE time_window ADD CONSTRAINT fk_d212b62b55a755d4 FOREIGN KEY (working_hours_id) REFERENCES working_hours (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_d212b62b55a755d4 ON time_window (working_hours_id)');
    }
}
