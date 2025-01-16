<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250116074032 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE category (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE client (id INT NOT NULL, address VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, discount_point INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE discount_code (id SERIAL NOT NULL, code VARCHAR(255) NOT NULL, percentage DOUBLE PRECISION NOT NULL, valid_until TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE image_product (id SERIAL NOT NULL, product_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_465A53714584665A ON image_product (product_id)');
        $this->addSql('CREATE TABLE "like" (id SERIAL NOT NULL, client_id INT NOT NULL, review_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AC6340B319EB6921 ON "like" (client_id)');
        $this->addSql('CREATE INDEX IDX_AC6340B33E2E969B ON "like" (review_id)');
        $this->addSql('COMMENT ON COLUMN "like".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "order" (id SERIAL NOT NULL, client_id INT NOT NULL, applied_discount_id INT DEFAULT NULL, total_price NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F529939819EB6921 ON "order" (client_id)');
        $this->addSql('CREATE INDEX IDX_F5299398CEB5C1F1 ON "order" (applied_discount_id)');
        $this->addSql('CREATE TABLE order_item (id SERIAL NOT NULL, product_id INT NOT NULL, linked_order_id INT NOT NULL, global_price DOUBLE PRECISION NOT NULL, quantity INT NOT NULL, created_at DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52EA1F094584665A ON order_item (product_id)');
        $this->addSql('CREATE INDEX IDX_52EA1F09E39F23E7 ON order_item (linked_order_id)');
        $this->addSql('COMMENT ON COLUMN order_item.created_at IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE product (id SERIAL NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT NOT NULL, price DOUBLE PRECISION NOT NULL, stock_quantity INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, size VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('COMMENT ON COLUMN product.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE review (id SERIAL NOT NULL, client_id INT NOT NULL, product_id INT NOT NULL, content VARCHAR(255) NOT NULL, rating INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_794381C619EB6921 ON review (client_id)');
        $this->addSql('CREATE INDEX IDX_794381C64584665A ON review (product_id)');
        $this->addSql('COMMENT ON COLUMN review.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN review.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('CREATE TABLE wishlist (id SERIAL NOT NULL, client_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9CE12A3119EB6921 ON wishlist (client_id)');
        $this->addSql('CREATE INDEX IDX_9CE12A314584665A ON wishlist (product_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE admin ADD CONSTRAINT FK_880E0D76BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455BF396750 FOREIGN KEY (id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE image_product ADD CONSTRAINT FK_465A53714584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B319EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "like" ADD CONSTRAINT FK_AC6340B33E2E969B FOREIGN KEY (review_id) REFERENCES review (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F529939819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398CEB5C1F1 FOREIGN KEY (applied_discount_id) REFERENCES discount_code (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09E39F23E7 FOREIGN KEY (linked_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C619EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A3119EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wishlist ADD CONSTRAINT FK_9CE12A314584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admin DROP CONSTRAINT FK_880E0D76BF396750');
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455BF396750');
        $this->addSql('ALTER TABLE image_product DROP CONSTRAINT FK_465A53714584665A');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B319EB6921');
        $this->addSql('ALTER TABLE "like" DROP CONSTRAINT FK_AC6340B33E2E969B');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F529939819EB6921');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398CEB5C1F1');
        $this->addSql('ALTER TABLE order_item DROP CONSTRAINT FK_52EA1F094584665A');
        $this->addSql('ALTER TABLE order_item DROP CONSTRAINT FK_52EA1F09E39F23E7');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C619EB6921');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C64584665A');
        $this->addSql('ALTER TABLE wishlist DROP CONSTRAINT FK_9CE12A3119EB6921');
        $this->addSql('ALTER TABLE wishlist DROP CONSTRAINT FK_9CE12A314584665A');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE discount_code');
        $this->addSql('DROP TABLE image_product');
        $this->addSql('DROP TABLE "like"');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_item');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE wishlist');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
