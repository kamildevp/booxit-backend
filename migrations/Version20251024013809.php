<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024013809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set organization banner_file_id column to null on file delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT FK_C1EE637CC79650AF');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637CC79650AF FOREIGN KEY (banner_file_id) REFERENCES file (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE organization DROP CONSTRAINT fk_c1ee637cc79650af');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT fk_c1ee637cc79650af FOREIGN KEY (banner_file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
