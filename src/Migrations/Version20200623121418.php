<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200623121418 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('users');

        $table->addColumn('lang', 'string')
            ->setNotnull(false);

        $table->addColumn('water_calculation', 'boolean')
            ->setNotnull(false);

        $table->addColumn('water_amount', 'integer')
            ->setNotnull(false);

        $table->addColumn('height', 'integer')
            ->setNotnull(false);

        $table->addColumn('age', 'integer')
            ->setNotnull(false);

        $table->addColumn('gender', 'boolean')
            ->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('users');
        $table->dropColumn('lang');
        $table->dropColumn('water_calculation');
        $table->dropColumn('water_amount');
        $table->dropColumn('height');
        $table->dropColumn('age');
        $table->dropColumn('gender');
    }
}
