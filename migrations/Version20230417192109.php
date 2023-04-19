<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230417192109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE working_hours ADD time_windows TEXT NOT NULL');
        $this->addSql('ALTER TABLE working_hours DROP start_time');
        $this->addSql('ALTER TABLE working_hours DROP end_time');
        $this->addSql('COMMENT ON COLUMN working_hours.time_windows IS \'(DC2Type:array)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE working_hours ADD start_time TIME(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE working_hours ADD end_time TIME(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE working_hours DROP time_windows');
    }
}
