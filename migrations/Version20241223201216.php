<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241223201216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE discount_code (id SERIAL NOT NULL, code VARCHAR(255) NOT NULL, percentage DOUBLE PRECISION NOT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE "order" ADD applied_discount_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F529939853F76825 FOREIGN KEY (applied_discount_id) REFERENCES discount_code (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F529939853F76825 ON "order" (applied_discount_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F529939853F76825');
        $this->addSql('DROP TABLE discount_code');
        $this->addSql('DROP INDEX IDX_F529939853F76825');
        $this->addSql('ALTER TABLE "order" DROP applied_discount_id');
    }
}
