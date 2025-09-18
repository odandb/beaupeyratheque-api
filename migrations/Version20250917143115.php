<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917143115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add indexes to author and book tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_nationality ON author (nationality)');
        $this->addSql('CREATE INDEX idx_publication_year ON book (publication_year)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_nationality');
        $this->addSql('DROP INDEX idx_publication_year');
    }
}
