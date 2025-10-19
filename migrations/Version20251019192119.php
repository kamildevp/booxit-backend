<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251019192119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Changed start_date_time and end_date_time reservation columns to datetime_immutable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation ALTER start_date_time TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE reservation ALTER end_date_time TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN reservation.start_date_time IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reservation.end_date_time IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation ALTER start_date_time TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE reservation ALTER end_date_time TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN reservation.start_date_time IS NULL');
        $this->addSql('COMMENT ON COLUMN reservation.end_date_time IS NULL');
    }
}
