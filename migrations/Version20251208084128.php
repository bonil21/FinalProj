<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251208084128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user management fields, activity logs, and ownership tracking';
    }

    public function up(Schema $schema): void
    {
        // Check and create activity_log table
        $this->addSql('CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT NOT NULL, 
            user_email VARCHAR(180) NOT NULL, 
            user_role VARCHAR(50) NOT NULL, 
            action VARCHAR(80) NOT NULL, 
            entity VARCHAR(80) DEFAULT NULL, 
            entity_id VARCHAR(80) DEFAULT NULL, 
            details JSON DEFAULT NULL, 
            ip_address VARCHAR(64) DEFAULT NULL, 
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add created_by_id to order table (check if column exists first)
        $this->addSql('
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "order" 
                AND COLUMN_NAME = "created_by_id");
            SET @sqlstmt := IF(@exist = 0, 
                "ALTER TABLE `order` ADD created_by_id INT DEFAULT NULL", 
                "SELECT 1");
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ');
        
        // Add foreign key for order.created_by_id (check if exists first)
        $this->addSql('
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "order" 
                AND CONSTRAINT_NAME = "FK_F5299398B03A8386");
            SET @sqlstmt := IF(@exist = 0, 
                "ALTER TABLE `order` ADD CONSTRAINT FK_F5299398B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)", 
                "SELECT 1");
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ');
        
        // Add created_by_id to products table
        $this->addSql('
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "products" 
                AND COLUMN_NAME = "created_by_id");
            SET @sqlstmt := IF(@exist = 0, 
                "ALTER TABLE products ADD created_by_id INT DEFAULT NULL", 
                "SELECT 1");
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ');
        
        // Add foreign key for products.created_by_id
        $this->addSql('
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "products" 
                AND CONSTRAINT_NAME = "FK_B3BA5A5AB03A8386");
            SET @sqlstmt := IF(@exist = 0, 
                "ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)", 
                "SELECT 1");
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ');
        
        // Add name column to user table
        $this->addSql('
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "user" 
                AND COLUMN_NAME = "name");
            SET @sqlstmt := IF(@exist = 0, 
                "ALTER TABLE user ADD name VARCHAR(150) DEFAULT NULL", 
                "SELECT 1");
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ');
        
        // Add status column to user table
        $this->addSql('
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "user" 
                AND COLUMN_NAME = "status");
            SET @sqlstmt := IF(@exist = 0, 
                "ALTER TABLE user ADD status VARCHAR(20) DEFAULT NULL", 
                "SELECT 1");
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ');
        
        // Add created_at column to user table
        $this->addSql('
            SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = "user" 
                AND COLUMN_NAME = "created_at");
            SET @sqlstmt := IF(@exist = 0, 
                "ALTER TABLE user ADD created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'", 
                "SELECT 1");
            PREPARE stmt FROM @sqlstmt;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ');
        
        // Update existing rows with default values
        $this->addSql('UPDATE user SET name = email WHERE name IS NULL OR name = ""');
        $this->addSql('UPDATE user SET status = \'active\' WHERE status IS NULL OR status = ""');
        $this->addSql('UPDATE user SET created_at = NOW() WHERE created_at IS NULL');
        
        // Make columns NOT NULL (only modify if they're still nullable)
        $this->addSql('ALTER TABLE user MODIFY name VARCHAR(150) NOT NULL');
        $this->addSql('ALTER TABLE user MODIFY status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE user MODIFY created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS activity_log');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY IF EXISTS FK_B3BA5A5AB03A8386');
        $this->addSql('DROP INDEX IF EXISTS IDX_B3BA5A5AB03A8386 ON products');
        $this->addSql('ALTER TABLE products DROP COLUMN IF EXISTS created_by_id');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY IF EXISTS FK_F5299398B03A8386');
        $this->addSql('DROP INDEX IF EXISTS IDX_F5299398B03A8386 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP COLUMN IF EXISTS created_by_id');
        $this->addSql('ALTER TABLE user DROP COLUMN IF EXISTS name, DROP COLUMN IF EXISTS status, DROP COLUMN IF EXISTS created_at');
    }
}
