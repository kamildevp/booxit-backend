<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024023210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set reservation service_id to not null';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955ED5CA9E6');
        $this->addSql('ALTER TABLE reservation ALTER service_id SET NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT fk_42c84955ed5ca9e6');
        $this->addSql('ALTER TABLE reservation ALTER service_id DROP NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT fk_42c84955ed5ca9e6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
