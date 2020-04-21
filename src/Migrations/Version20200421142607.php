<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200421142607 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create Users table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('users');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setNotnull(true);

        $table->addColumn('name', 'string')
            ->setNotnull(false);

        $table->addColumn('username', 'string')
            ->setNotnull(true)
            ->setLength(180);

        $table->addColumn('password', 'string')
            ->setNotnull(true);

        $table->addColumn('roles', 'json')
            ->setNotnull(true);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['username']);
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('users');
    }
}
