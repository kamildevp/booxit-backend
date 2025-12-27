<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227193553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add timezone column to schedule table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule ADD timezone VARCHAR(255) DEFAULT \'Europe/Warsaw\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule DROP timezone');
    }
}
