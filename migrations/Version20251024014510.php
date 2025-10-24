<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024014510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set file uploaded_by_id column to null on user delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F3610A2B28FE8');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE file DROP CONSTRAINT fk_8c9f3610a2b28fe8');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT fk_8c9f3610a2b28fe8 FOREIGN KEY (uploaded_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
