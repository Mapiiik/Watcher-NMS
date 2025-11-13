<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIpNetworkToRouterosDeviceIps extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     *
     * BROKEN SINCE cakephp/migrations 4.7.x with builtin backend
     */

    /*
    public function change(): void
    {
        $table = $this->table('routeros_device_ips');
        $table->addColumn('ip_network', Literal::from('cidr GENERATED ALWAYS AS (network("ip_address")) STORED'));
        $table->update();
    }
    */

    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     *
     * @return void
     */
    public function up(): void
    {
        // Add generated column ip_network (PostgreSQL 12+)
        $this->execute(
            'ALTER TABLE "routeros_device_ips"
             ADD COLUMN "ip_network" cidr
             GENERATED ALWAYS AS (network("ip_address")) STORED;',
        );
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     *
     * @return void
     */
    public function down(): void
    {
        // Drop column ip_network
        $this->execute(
            'ALTER TABLE "routeros_device_ips"
             DROP COLUMN "ip_network";',
        );
    }
}
