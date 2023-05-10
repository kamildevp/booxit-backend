<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230510153447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT FK_266B86ED55A755D4');
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT FK_266B86ED137F1495');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT FK_266B86ED55A755D4 FOREIGN KEY (working_hours_id) REFERENCES working_hours (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT FK_266B86ED137F1495 FOREIGN KEY (time_window_id) REFERENCES time_window (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT fk_266b86ed55a755d4');
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT fk_266b86ed137f1495');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT fk_266b86ed55a755d4 FOREIGN KEY (working_hours_id) REFERENCES working_hours (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT fk_266b86ed137f1495 FOREIGN KEY (time_window_id) REFERENCES time_window (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
