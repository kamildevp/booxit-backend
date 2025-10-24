<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251011135635 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create reservation table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE reservation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reservation (id INT NOT NULL, schedule_id INT NOT NULL, service_id INT DEFAULT NULL, organization_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, phone_number VARCHAR(255) NOT NULL, verified BOOLEAN NOT NULL, expiry_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, estimated_price NUMERIC(10, 2) NOT NULL, start_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_42C84955A40BC2D5 ON reservation (schedule_id)');
        $this->addSql('CREATE INDEX IDX_42C84955ED5CA9E6 ON reservation (service_id)');
        $this->addSql('CREATE INDEX IDX_42C8495532C8A3DE ON reservation (organization_id)');
        $this->addSql('CREATE INDEX IDX_42C84955B03A8386 ON reservation (created_by_id)');
        $this->addSql('CREATE INDEX IDX_42C84955896DBBDE ON reservation (updated_by_id)');
        $this->addSql('COMMENT ON COLUMN reservation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reservation.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495532C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955896DBBDE FOREIGN KEY (updated_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER INDEX idx_ed6b83f7a40bc2d5 RENAME TO IDX_E42E03E1A40BC2D5');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE reservation_id_seq CASCADE');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955A40BC2D5');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955ED5CA9E6');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C8495532C8A3DE');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955B03A8386');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955896DBBDE');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('ALTER INDEX idx_e42e03e1a40bc2d5 RENAME TO idx_ed6b83f7a40bc2d5');
    }
}
