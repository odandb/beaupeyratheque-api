<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250919194716 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add book published';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book ADD is_published BOOLEAN DEFAULT true NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book DROP is_published');
    }
}
