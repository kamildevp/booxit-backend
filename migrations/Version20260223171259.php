<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223171259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop not null constraint on user table password column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER password DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ALTER password SET NOT NULL');
    }
}
