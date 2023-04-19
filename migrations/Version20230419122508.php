<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230419122508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE reservation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reservation (id INT NOT NULL, schedule_id INT NOT NULL, time_window_id INT NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, verified BOOLEAN NOT NULL, confirmed BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_42C84955A40BC2D5 ON reservation (schedule_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42C84955137F1495 ON reservation (time_window_id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955137F1495 FOREIGN KEY (time_window_id) REFERENCES time_window (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE reservation_id_seq CASCADE');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955A40BC2D5');
        $this->addSql('ALTER TABLE reservation DROP CONSTRAINT FK_42C84955137F1495');
        $this->addSql('DROP TABLE reservation');
    }
}
