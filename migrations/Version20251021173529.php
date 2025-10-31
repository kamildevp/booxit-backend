<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251021173529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reservation_email_confirmation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reservation_email_confirmation (reservation_id INT NOT NULL, email_confirmation_id INT NOT NULL, PRIMARY KEY(reservation_id, email_confirmation_id))');
        $this->addSql('CREATE INDEX IDX_37A2B3AFB83297E7 ON reservation_email_confirmation (reservation_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_37A2B3AF89CC6046 ON reservation_email_confirmation (email_confirmation_id)');
        $this->addSql('ALTER TABLE reservation_email_confirmation ADD CONSTRAINT FK_37A2B3AFB83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation_email_confirmation ADD CONSTRAINT FK_37A2B3AF89CC6046 FOREIGN KEY (email_confirmation_id) REFERENCES email_confirmation (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation_email_confirmation DROP CONSTRAINT FK_37A2B3AFB83297E7');
        $this->addSql('ALTER TABLE reservation_email_confirmation DROP CONSTRAINT FK_37A2B3AF89CC6046');
        $this->addSql('DROP TABLE reservation_email_confirmation');
    }
}
