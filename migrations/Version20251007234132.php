<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251007234132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create weekday_time_window table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE weekday_time_window (id UUID NOT NULL, schedule_id INT NOT NULL, weekday VARCHAR(255) NOT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_597F16CA40BC2D5 ON weekday_time_window (schedule_id)');
        $this->addSql('ALTER TABLE weekday_time_window ADD CONSTRAINT FK_597F16CA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE weekday_time_window DROP CONSTRAINT FK_597F16CA40BC2D5');
        $this->addSql('DROP TABLE weekday_time_window');
    }
}
