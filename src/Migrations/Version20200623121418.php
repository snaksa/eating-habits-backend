<?php

declare(strict_types=1);

namespace App\Migrations;

use App\Constant\Gender;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200623121418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('users');

        $table->addColumn('lang', 'string')
            ->setNotnull(false)
            ->setDefault('en');

        $table->addColumn('water_calculation', 'boolean')
            ->setNotnull(false)
            ->setDefault(false);

        $table->addColumn('water_amount', 'integer')
            ->setNotnull(false)
            ->setDefault(5000);

        $table->addColumn('height', 'integer')
            ->setNotnull(false)
            ->setDefault(170);

        $table->addColumn('age', 'integer')
            ->setNotnull(false)
            ->setDefault(18);

        $table->addColumn('gender', 'boolean')
            ->setNotnull(false)
            ->setDefault(Gender::MALE);
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
