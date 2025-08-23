<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250823130849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organization (id INT NOT NULL, name VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, banner VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE organization_member (id INT NOT NULL, organization_id INT NOT NULL, app_user_id INT NOT NULL, roles TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_756A2A8D32C8A3DE ON organization_member (organization_id)');
        $this->addSql('CREATE INDEX IDX_756A2A8D4A3353D8 ON organization_member (app_user_id)');
        $this->addSql('COMMENT ON COLUMN organization_member.roles IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE reservation (id INT NOT NULL, schedule_id INT NOT NULL, service_id INT DEFAULT NULL, time_window_id INT NOT NULL, email VARCHAR(180) NOT NULL, phone_number VARCHAR(255) NOT NULL, verified BOOLEAN NOT NULL, confirmed BOOLEAN NOT NULL, date VARCHAR(255) NOT NULL, expiry_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_42C84955A40BC2D5 ON reservation (schedule_id)');
        $this->addSql('CREATE INDEX IDX_42C84955ED5CA9E6 ON reservation (service_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42C84955137F1495 ON reservation (time_window_id)');
        $this->addSql('CREATE TABLE schedule (id INT NOT NULL, organization_id INT NOT NULL, name VARCHAR(50) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A3811FB32C8A3DE ON schedule (organization_id)');
        $this->addSql('CREATE TABLE schedule_service (schedule_id INT NOT NULL, service_id INT NOT NULL, PRIMARY KEY(schedule_id, service_id))');
        $this->addSql('CREATE INDEX IDX_6CF7B663A40BC2D5 ON schedule_service (schedule_id)');
        $this->addSql('CREATE INDEX IDX_6CF7B663ED5CA9E6 ON schedule_service (service_id)');
        $this->addSql('CREATE TABLE schedule_assignment (id INT NOT NULL, schedule_id INT NOT NULL, organization_member_id INT NOT NULL, access_type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_600F33F8A40BC2D5 ON schedule_assignment (schedule_id)');
        $this->addSql('CREATE INDEX IDX_600F33F84DA009F8 ON schedule_assignment (organization_member_id)');
        $this->addSql('CREATE TABLE service (id INT NOT NULL, organization_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, duration VARCHAR(255) NOT NULL, estimated_price VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E19D9AD232C8A3DE ON service (organization_id)');
        $this->addSql('COMMENT ON COLUMN service.duration IS \'(DC2Type:dateinterval)\'');
        $this->addSql('CREATE TABLE time_window (id INT NOT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE working_hours (id INT NOT NULL, schedule_id INT NOT NULL, day VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D72CDC3DA40BC2D5 ON working_hours (schedule_id)');
        $this->addSql('CREATE TABLE working_hours_time_window (working_hours_id INT NOT NULL, time_window_id INT NOT NULL, PRIMARY KEY(working_hours_id, time_window_id))');
        $this->addSql('CREATE INDEX IDX_266B86ED55A755D4 ON working_hours_time_window (working_hours_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_266B86ED137F1495 ON working_hours_time_window (time_window_id)');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D4A3353D8 FOREIGN KEY (app_user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955137F1495 FOREIGN KEY (time_window_id) REFERENCES time_window (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_service ADD CONSTRAINT FK_6CF7B663A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_service ADD CONSTRAINT FK_6CF7B663ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F8A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F84DA009F8 FOREIGN KEY (organization_member_id) REFERENCES organization_member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD232C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours ADD CONSTRAINT FK_D72CDC3DA40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT FK_266B86ED55A755D4 FOREIGN KEY (working_hours_id) REFERENCES working_hours (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE working_hours_time_window ADD CONSTRAINT FK_266B86ED137F1495 FOREIGN KEY (time_window_id) REFERENCES time_window (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8D32C8A3DE');
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8D4A3353D8');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955A40BC2D5');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955ED5CA9E6');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955137F1495');
        $this->addSql('ALTER TABLE schedule DROP CONSTRAINT FK_5A3811FB32C8A3DE');
        $this->addSql('ALTER TABLE schedule_service DROP CONSTRAINT FK_6CF7B663A40BC2D5');
        $this->addSql('ALTER TABLE schedule_service DROP CONSTRAINT FK_6CF7B663ED5CA9E6');
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT FK_600F33F8A40BC2D5');
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT FK_600F33F84DA009F8');
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD232C8A3DE');
        $this->addSql('ALTER TABLE working_hours DROP CONSTRAINT FK_D72CDC3DA40BC2D5');
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT FK_266B86ED55A755D4');
        $this->addSql('ALTER TABLE working_hours_time_window DROP CONSTRAINT FK_266B86ED137F1495');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE organization_member');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE schedule_service');
        $this->addSql('DROP TABLE schedule_assignment');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE time_window');
        $this->addSql('DROP TABLE working_hours');
        $this->addSql('DROP TABLE working_hours_time_window');
    }
}
