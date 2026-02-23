<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223163636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove username column from user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_8d93d649f85e0677');
        $this->addSql('ALTER TABLE "user" DROP username');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD username VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649f85e0677 ON "user" (username)');
    }
}
