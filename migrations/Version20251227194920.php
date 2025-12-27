<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227194920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove timezone column from weekday_time_window table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE weekday_time_window DROP timezone');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE weekday_time_window ADD timezone VARCHAR(255) DEFAULT \'Europe/Warsaw\' NOT NULL');
    }
}
