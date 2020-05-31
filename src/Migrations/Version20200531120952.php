<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200531120952 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('medicines_schedule');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setNotnull(true);

        $table->addColumn('intake_time', 'datetime')
            ->setNotnull(true);

        $table->addColumn('period_span', 'integer')
            ->setNotnull(false);

        $table->addColumn('medicine_id', 'integer')
            ->setNotnull(true);

        $table->addForeignKeyConstraint(
            'medicines',
            ['medicine_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_medicines_schedule_medicine_medicine'
        );

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('medicines_schedule');
    }
}
