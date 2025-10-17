<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251015035357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Payment and Transaction entities with relationships';
    }

    public function up(Schema $schema): void
    {
        // Create payment table
        $this->addSql('CREATE TABLE payment (
            id INT AUTO_INCREMENT NOT NULL,
            customer_id INT NOT NULL,
            subscription_id INT DEFAULT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(3) NOT NULL,
            status VARCHAR(20) NOT NULL,
            stripe_payment_intent_id VARCHAR(255) DEFAULT NULL,
            stripe_charge_id VARCHAR(255) DEFAULT NULL,
            payment_method VARCHAR(50) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            paid_at DATETIME DEFAULT NULL,
            failure_reason LONGTEXT DEFAULT NULL,
            INDEX IDX_6D28840D9395C3F3 (customer_id),
            INDEX IDX_6D28840D9A1887DC (subscription_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create transaction table
        $this->addSql('CREATE TABLE transaction (
            id INT AUTO_INCREMENT NOT NULL,
            customer_id INT NOT NULL,
            payment_id INT DEFAULT NULL,
            subscription_id INT DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            currency VARCHAR(3) NOT NULL,
            status VARCHAR(20) NOT NULL,
            reference VARCHAR(255) DEFAULT NULL,
            description LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            processed_at DATETIME DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            INDEX IDX_723705D19395C3F3 (customer_id),
            INDEX IDX_723705D14C3A3BB (payment_id),
            INDEX IDX_723705D19A1887DC (subscription_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D9A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D14C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9395C3F3');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D9A1887DC');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19395C3F3');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D14C3A3BB');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19A1887DC');

        // Drop tables
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE transaction');
    }
}
