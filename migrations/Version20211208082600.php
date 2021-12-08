<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211208082600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE order_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE "orders_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE book_author (book_id INT NOT NULL, author_id INT NOT NULL, PRIMARY KEY(book_id, author_id))');
        $this->addSql('CREATE INDEX IDX_9478D34516A2B381 ON book_author (book_id)');
        $this->addSql('CREATE INDEX IDX_9478D345F675F31B ON book_author (author_id)');
        $this->addSql('CREATE TABLE "orders" (id INT NOT NULL, phone VARCHAR(20) NOT NULL, address VARCHAR(255) NOT NULL, status VARCHAR(15) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE order_book (order_id INT NOT NULL, book_id INT NOT NULL, PRIMARY KEY(order_id, book_id))');
        $this->addSql('CREATE INDEX IDX_861499268D9F6D38 ON order_book (order_id)');
        $this->addSql('CREATE INDEX IDX_8614992616A2B381 ON order_book (book_id)');
        $this->addSql('ALTER TABLE book_author ADD CONSTRAINT FK_9478D34516A2B381 FOREIGN KEY (book_id) REFERENCES "books" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE book_author ADD CONSTRAINT FK_9478D345F675F31B FOREIGN KEY (author_id) REFERENCES "authors" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_book ADD CONSTRAINT FK_861499268D9F6D38 FOREIGN KEY (order_id) REFERENCES "orders" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_book ADD CONSTRAINT FK_8614992616A2B381 FOREIGN KEY (book_id) REFERENCES "books" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('ALTER TABLE books ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE books ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE order_book DROP CONSTRAINT FK_861499268D9F6D38');
        $this->addSql('DROP SEQUENCE "orders_id_seq" CASCADE');
        $this->addSql('CREATE SEQUENCE order_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "order" (id INT NOT NULL, phone VARCHAR(20) NOT NULL, address VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('DROP TABLE book_author');
        $this->addSql('DROP TABLE "orders"');
        $this->addSql('DROP TABLE order_book');
        $this->addSql('ALTER TABLE "books" DROP created_at');
        $this->addSql('ALTER TABLE "books" DROP updated_at');
    }
}
