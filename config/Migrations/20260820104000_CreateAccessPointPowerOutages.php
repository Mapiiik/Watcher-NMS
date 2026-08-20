<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Db\Literal;

class CreateAccessPointPowerOutages extends BaseMigration
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
        $this->table('access_point_power_outages', ['id' => false, 'primary_key' => ['id']])
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
            ->addColumn('power_outage_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            // Worked out when the outage is read rather than when the page is drawn, so that what
            // a link rests on is written down and can be argued with.
            ->addColumn('certainty', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
                'comment' => 'Whether the outage is known to be this mast or only likely to be.',
            ])
            ->addColumn('matched_by', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('match_note', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'What the match rests on, for whoever doubts it.',
            ])
            ->addColumn('distance_metres', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('access_point_supply_address_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'comment' => 'Which of the addresses around the mast the outage was found on.',
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
            ->addIndex(['access_point_id', 'power_outage_id'], ['unique' => true])
            ->addIndex(['power_outage_id'])
            ->addForeignKey('access_point_id', 'access_points', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('power_outage_id', 'power_outages', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('access_point_supply_address_id', 'access_point_supply_addresses', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
