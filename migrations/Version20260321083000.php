<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321083000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add selected_meals JSON column to subscription for customer meal selections.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription ADD selected_meals JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription DROP selected_meals');
    }
}

