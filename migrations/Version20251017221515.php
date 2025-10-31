<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251017221515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added unique constraint on reservation reference column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42C84955AEA34913 ON reservation (reference)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_42C84955AEA34913');
    }
}
