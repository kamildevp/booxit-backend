<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251024020445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CASCADE DELETE to weekday_time_window table on schedule delete';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE weekday_time_window DROP CONSTRAINT fk_597f16ca40bc2d5');
        $this->addSql('ALTER TABLE weekday_time_window ADD CONSTRAINT FK_1C93AD14A40BC2D5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE weekday_time_window DROP CONSTRAINT FK_1C93AD14A40BC2D5');
        $this->addSql('ALTER TABLE weekday_time_window ADD CONSTRAINT fk_597f16ca40bc2d5 FOREIGN KEY (schedule_id) REFERENCES schedule (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
