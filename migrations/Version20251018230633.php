<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251018230633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add timestamps to email_confirmation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_confirmation ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE email_confirmation ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN email_confirmation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN email_confirmation.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_confirmation DROP created_at');
        $this->addSql('ALTER TABLE email_confirmation DROP updated_at');
    }
}
