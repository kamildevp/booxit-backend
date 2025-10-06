<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006195416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create schedule_assignment table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE schedule_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE schedule_assignment (id INT NOT NULL, schedule_id INT NOT NULL, organization_member_id INT NOT NULL, access_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_600F33F8A40BC2D5 ON schedule_assignment (schedule_id)');
        $this->addSql('CREATE INDEX IDX_600F33F84DA009F8 ON schedule_assignment (organization_member_id)');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F8A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F84DA009F8 FOREIGN KEY (organization_member_id) REFERENCES organization_member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE schedule_assignment_id_seq CASCADE');
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT FK_600F33F8A40BC2D5');
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT FK_600F33F84DA009F8');
        $this->addSql('DROP TABLE schedule_assignment');
    }
}
