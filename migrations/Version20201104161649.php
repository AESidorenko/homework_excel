<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201104161649 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cell (id INT AUTO_INCREMENT NOT NULL, sheet_id INT NOT NULL, row INT NOT NULL, col INT NOT NULL, value DOUBLE PRECISION NOT NULL, INDEX IDX_CB8787E21C3E8C5B (sheet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cell ADD CONSTRAINT FK_CB8787E21C3E8C5B FOREIGN KEY (sheet_id) REFERENCES sheet (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE cell');
    }
}
