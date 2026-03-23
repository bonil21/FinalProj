<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251011070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Placeholder migration restored to reconcile executed migration history.';
    }

    public function up(Schema $schema): void
    {
        // Intentionally empty: this version already exists in the database history.
    }

    public function down(Schema $schema): void
    {
        // Intentionally empty.
    }
}
