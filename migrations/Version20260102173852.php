<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260102173852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tier column to organization table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization ADD tier VARCHAR(255) DEFAULT \'BASIC\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization DROP tier');
    }
}
