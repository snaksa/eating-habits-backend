<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200421162438 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('meals');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setNotnull(true);

        $table->addColumn('description', 'string')
            ->setNotnull(false);

        $table->addColumn('date', 'datetime')
            ->setNotnull(true);

        $table->addColumn('type', 'integer')
            ->setNotnull(true);

        $table->addColumn('picture', 'string')
            ->setNotnull(false);

        $table->addColumn('user_id', 'integer')
            ->setNotnull(true);

        $table->addForeignKeyConstraint(
            'users',
            ['user_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_meals_users_user'
        );

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('meals');
    }
}
