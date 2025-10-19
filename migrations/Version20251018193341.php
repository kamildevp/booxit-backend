<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251018193341 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status field to email_confirmation table and change params field type to json';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_confirmation ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE email_confirmation DROP params');
        $this->addSql('ALTER TABLE email_confirmation ADD params JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_confirmation DROP status');
        $this->addSql('ALTER TABLE email_confirmation ADD params TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN email_confirmation.params IS \'(DC2Type:array)\'');
    }
}
