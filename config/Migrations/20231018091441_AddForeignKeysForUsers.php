<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysForUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $related_tables = [
            'access_point_contacts',
            'access_point_types',
            'access_points',
            'antenna_types',
            'customer_connection_ips',
            'customer_connections',
            'customer_points',
            'device_types',
            'electricity_meter_readings',
            'ip_address_ranges',
            'landlord_payments',
            'manufacturers',
            'payment_purposes',
            'power_supplies',
            'power_supply_types',
            'radar_interferences',
            'radio_links',
            'radio_unit_bands',
            'radio_unit_types',
            'radio_units',
            'routeros_device_interfaces',
            'routeros_device_ips',
            'routeros_devices',
        ];

        foreach ($related_tables as $related_table) {
            $table = $this->table($related_table);

            $table->addForeignKey('created_by', 'users', 'id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
            ]);
            $table->addForeignKey('modified_by', 'users', 'id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
            ]);
            $table->update();

            unset($table);
        }
    }
}
