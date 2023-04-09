<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230330195948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE email_verification_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE email_confirmation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE email_confirmation (id INT NOT NULL, creator_id INT NOT NULL, email VARCHAR(255) NOT NULL, verification_route VARCHAR(255) NOT NULL, params TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1D2EF46F61220EA6 ON email_confirmation (creator_id)');
        $this->addSql('COMMENT ON COLUMN email_confirmation.params IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE email_confirmation ADD CONSTRAINT FK_1D2EF46F61220EA6 FOREIGN KEY (creator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE email_confirmation_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE email_verification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE email_confirmation DROP CONSTRAINT FK_1D2EF46F61220EA6');
        $this->addSql('DROP TABLE email_confirmation');
    }
}
