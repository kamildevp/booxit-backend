<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251027040947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add address to organization table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS earthdistance CASCADE');
        $this->addSql('ALTER TABLE organization ADD address_street VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE organization ADD address_city VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE organization ADD address_region VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE organization ADD address_postal_code VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE organization ADD address_country VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE organization ADD address_place_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE organization ADD address_formatted_address TEXT NOT NULL');
        $this->addSql('ALTER TABLE organization ADD address_latitude DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE organization ADD address_longitude DOUBLE PRECISION NOT NULL');
        $this->addSql('CREATE INDEX idx_organization_earth_active ON organization USING gist (ll_to_earth(address_latitude, address_longitude)) WHERE deleted_at IS NULL');
        $this->addSql('CREATE INDEX idx_organization_postal_code_city_active ON organization (address_postal_code, address_city) WHERE deleted_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_organization_postal_code_city_active');
        $this->addSql('DROP INDEX organization_earth_active');
        $this->addSql('ALTER TABLE organization DROP address_street');
        $this->addSql('ALTER TABLE organization DROP address_city');
        $this->addSql('ALTER TABLE organization DROP address_region');
        $this->addSql('ALTER TABLE organization DROP address_postal_code');
        $this->addSql('ALTER TABLE organization DROP address_country');
        $this->addSql('ALTER TABLE organization DROP address_place_id');
        $this->addSql('ALTER TABLE organization DROP address_formatted_address');
        $this->addSql('ALTER TABLE organization DROP address_latitude');
        $this->addSql('ALTER TABLE organization DROP address_longitude');
        $this->addSql('DROP EXTENSION IF EXISTS earthdistance');
    }
}
