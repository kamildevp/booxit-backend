<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251222221543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Moved date to start_date_time and end_date_time columns';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_time_window RENAME COLUMN start_time TO start_date_time');
        $this->addSql('ALTER TABLE custom_time_window RENAME COLUMN end_time TO end_date_time');
        $this->addSql('ALTER TABLE custom_time_window ALTER start_date_time TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING (date + start_date_time)::timestamp');
        $this->addSql('ALTER TABLE custom_time_window ALTER end_date_time TYPE TIMESTAMP(0) WITHOUT TIME ZONE USING (date + end_date_time)::timestamp');
        $this->addSql('ALTER TABLE custom_time_window DROP date');
        $this->addSql('ALTER TABLE custom_time_window DROP timezone');
        $this->addSql('COMMENT ON COLUMN custom_time_window.start_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN custom_time_window.end_date_time IS \'(DC2Type:datetime_immutable)\'');
    }
    
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_time_window ADD date DATE');
        $this->addSql('ALTER TABLE custom_time_window ADD timezone VARCHAR(255) DEFAULT \'Europe/Warsaw\' NOT NULL');
        $this->addSql('UPDATE custom_time_window SET date = start_date_time::date');
        $this->addSql('ALTER TABLE custom_time_window ALTER COLUMN date SET NOT NULL');
        $this->addSql('ALTER TABLE custom_time_window ALTER start_date_time TYPE TIME(0) WITHOUT TIME ZONE USING start_date_time::time');
        $this->addSql('ALTER TABLE custom_time_window ALTER end_date_time TYPE TIME(0) WITHOUT TIME ZONE USING end_date_time::time');
        $this->addSql('COMMENT ON COLUMN custom_time_window.start_date_time IS NULL');
        $this->addSql('COMMENT ON COLUMN custom_time_window.end_date_time IS NULL');
        $this->addSql('ALTER TABLE custom_time_window RENAME COLUMN start_date_time TO start_time');
        $this->addSql('ALTER TABLE custom_time_window RENAME COLUMN end_date_time TO end_time');
    }
}
