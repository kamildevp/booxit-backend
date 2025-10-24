<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024020046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CASCADE DELETE to schedule_assignment table on schedule or organization_member delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT FK_600F33F8A40BC2D5');
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT FK_600F33F84DA009F8');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F8A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT FK_600F33F84DA009F8 FOREIGN KEY (organization_member_id) REFERENCES organization_member (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT fk_600f33f8a40bc2d5');
        $this->addSql('ALTER TABLE schedule_assignment DROP CONSTRAINT fk_600f33f84da009f8');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT fk_600f33f8a40bc2d5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule_assignment ADD CONSTRAINT fk_600f33f84da009f8 FOREIGN KEY (organization_member_id) REFERENCES organization_member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
