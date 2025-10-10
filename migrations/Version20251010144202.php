<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010144202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create date_time_window table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE date_time_window (id UUID NOT NULL, schedule_id INT NOT NULL, date DATE NOT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ED6B83F7A40BC2D5 ON date_time_window (schedule_id)');
        $this->addSql('ALTER TABLE date_time_window ADD CONSTRAINT FK_ED6B83F7A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_597f16ca40bc2d5 RENAME TO IDX_1C93AD14A40BC2D5');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE date_time_window DROP CONSTRAINT FK_ED6B83F7A40BC2D5');
        $this->addSql('DROP TABLE date_time_window');
        $this->addSql('ALTER INDEX idx_1c93ad14a40bc2d5 RENAME TO idx_597f16ca40bc2d5');
    }
}
