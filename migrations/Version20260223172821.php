<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223172821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add auth provider columns to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD auth_provider VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD auth_provider_user_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP auth_provider');
        $this->addSql('ALTER TABLE "user" DROP auth_provider_user_id');
    }
}
