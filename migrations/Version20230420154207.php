<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230420154207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE free_term_id_seq CASCADE');
        $this->addSql('ALTER TABLE free_term DROP CONSTRAINT fk_98bae74da40bc2d5');
        $this->addSql('ALTER TABLE free_term DROP CONSTRAINT fk_98bae74d137f1495');
        $this->addSql('DROP TABLE free_term');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE free_term_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE free_term (id INT NOT NULL, schedule_id INT NOT NULL, time_window_id INT NOT NULL, date VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_98bae74d137f1495 ON free_term (time_window_id)');
        $this->addSql('CREATE INDEX idx_98bae74da40bc2d5 ON free_term (schedule_id)');
        $this->addSql('ALTER TABLE free_term ADD CONSTRAINT fk_98bae74da40bc2d5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE free_term ADD CONSTRAINT fk_98bae74d137f1495 FOREIGN KEY (time_window_id) REFERENCES time_window (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
