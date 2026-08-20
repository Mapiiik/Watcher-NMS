<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Db\Literal;

class CreatePowerOutages extends BaseMigration
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
        $this->table('power_outages', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => Literal::from('uuid_generate_v4()'),
                'limit' => null,
                'null' => false,
            ])
            // The country is divided between distributors and only one of them is read so far, so
            // this says whose outage a row is rather than leaving it to be assumed.
            ->addColumn('distributor', 'string', [
                'default' => 'CEZD',
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('outage_number', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
                'comment' => 'What the distributor keeps the outage under.',
            ])
            ->addColumn('begins_at', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('ends_at', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            // Only the reading made by supply point carries a withdrawal, so an outage read by
            // municipality alone is never known to have been called off.
            ->addColumn('cancelled', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('cancelled_at', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('announcement_url', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'Where the official announcement of the outage is published.',
            ])
            ->addColumn('town_code', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('town_name', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('district', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('summary', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'One line of where the outage is, for listings and mail.',
            ])
            // Where the outage reaches, in our own shape rather than the distributor's. Written on
            // the way in so that the places an outage covers can be compared against the addresses
            // of a mast again later without asking anybody anything.
            ->addColumn('places', 'jsonb', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            // The outage as it arrived. Nothing the distributor hands over is promised, and the
            // first question asked of a surprising row is what it actually said.
            ->addColumn('raw', 'jsonb', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            // Also when the outage was last read, which is what the reading is swept by and what
            // the listing reports its own age from.
            ->addColumn('modified', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['distributor', 'outage_number'], ['unique' => true])
            ->addIndex(['begins_at'])
            ->addIndex(['town_code'])
            ->create();
    }
}
