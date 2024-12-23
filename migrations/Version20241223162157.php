<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241223162157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_user RENAME COLUMN discr TO type');
        $this->addSql('ALTER TABLE client RENAME COLUMN phone TO phone_number');
        $this->addSql('ALTER TABLE client RENAME COLUMN nb_point TO discount_point');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE app_user RENAME COLUMN type TO discr');
        $this->addSql('ALTER TABLE client RENAME COLUMN phone_number TO phone');
        $this->addSql('ALTER TABLE client RENAME COLUMN discount_point TO nb_point');
    }
}
