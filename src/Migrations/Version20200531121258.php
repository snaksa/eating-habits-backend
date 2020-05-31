<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200531121258 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('medicines_intake');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setNotnull(true);

        $table->addColumn('date', 'datetime')
            ->setNotnull(false);

        $table->addColumn('medicine_schedule_id', 'integer')
            ->setNotnull(true);

        $table->addForeignKeyConstraint(
            'medicines_schedule',
            ['medicine_schedule_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_medicines_intake_medicine_schedule_medicine_schedule'
        );

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('medicines_intake');
    }
}
