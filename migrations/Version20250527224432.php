<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250527224432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_confirmation DROP CONSTRAINT FK_1D2EF46F61220EA6');
        $this->addSql('ALTER TABLE email_confirmation ADD CONSTRAINT FK_1D2EF46F61220EA6 FOREIGN KEY (creator_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE email_confirmation DROP CONSTRAINT fk_1d2ef46f61220ea6');
        $this->addSql('ALTER TABLE email_confirmation ADD CONSTRAINT fk_1d2ef46f61220ea6 FOREIGN KEY (creator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
