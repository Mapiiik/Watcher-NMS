<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreateIpAddressRanges extends AbstractMigration
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
        $this->table('ip_address_ranges', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => Literal::from('uuid_generate_v4()'),
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('created_by', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'limit' => null,
                'null' => true,
                'precision' => 6,
                'scale' => 6,
            ])
            ->addColumn('modified_by', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('ip_network', 'cidr', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('ip_gateway', 'inet', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('access_point_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('parent_ip_address_range_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('note', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('for_subnets', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('for_customer_addresses_set_via_radius', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('for_customer_addresses_set_manually', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('for_technology_addresses_set_manually', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('for_customer_networks_set_via_radius', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('for_customer_networks_set_manually', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('for_technology_networks_set_manually', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->create();
    }
}
