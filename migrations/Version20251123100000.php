<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251123100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add division to schedule table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule ADD division INT DEFAULT 15 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule DROP division');
    }
}
