<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251027020035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category column to service table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service ADD category VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP category');
    }
}
