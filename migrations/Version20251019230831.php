<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251019230831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reserved_by column to reservation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation ADD reserved_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955BCDB4AF4 FOREIGN KEY (reserved_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_42C84955BCDB4AF4 ON reservation (reserved_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955BCDB4AF4');
        $this->addSql('DROP INDEX IDX_42C84955BCDB4AF4');
        $this->addSql('ALTER TABLE reservation DROP reserved_by_id');
    }
}
