<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Db\Literal;

class CreateAccessPointSupplyAddresses extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('access_point_supply_addresses', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => Literal::from('uuid_generate_v4()'),
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('access_point_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            // Nearest first, as the register answered. A mast is not asked about one address but
            // about the few around it, because the power reaching it comes from one of them and
            // which one is not something the inventory knows.
            ->addColumn('rank', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'comment' => 'Where the address stands in the answer, nearest first.',
            ])
            ->addColumn('distance_metres', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('registry_ref', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'What the address registry keeps the address under.',
            ])
            // The distributor names the municipality of an outage by this same number, so the two
            // are compared as numbers rather than as names spelled two ways.
            ->addColumn('town_code', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'The registry number of the municipality, which outages are asked for by.',
            ])
            ->addColumn('town_name', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('town_part_name', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('street_name', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            // The three numbers are kept apart because the distributor keeps them apart too, and
            // an outage listing the registration numbers of a street says nothing about its
            // house numbers.
            ->addColumn('house_number', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('orientation_number', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('orientation_letter', 'string', [
                'default' => null,
                'limit' => 8,
                'null' => true,
            ])
            ->addColumn('number_type', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
                'comment' => 'Whether the house number is a house number or a registration number.',
            ])
            ->addColumn('formatted_address', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['access_point_id', 'rank'], ['unique' => true])
            // What every run starts from: which municipalities to ask the distributor about.
            ->addIndex(['town_code'])
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
