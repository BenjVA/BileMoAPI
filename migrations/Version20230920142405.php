<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230920142405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD roles JSON NOT NULL, CHANGE name name VARCHAR(180) NOT NULL, CHANGE email email VARCHAR(180) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E09E7927C74 ON customer (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_81398E095E237E06 ON customer (name)');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9395C3F3');
        $this->addSql('DROP INDEX IDX_D34A04AD9395C3F3 ON product');
        $this->addSql('ALTER TABLE product DROP customer_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_81398E09E7927C74 ON customer');
        $this->addSql('DROP INDEX UNIQ_81398E095E237E06 ON customer');
        $this->addSql('ALTER TABLE customer DROP roles, CHANGE email email VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE product ADD customer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD9395C3F3 ON product (customer_id)');
    }
}
