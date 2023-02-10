<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class AddIpNetworkToRouterosDeviceIps extends AbstractMigration
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
        $table = $this->table('routeros_device_ips');
        $table->addColumn('ip_network', Literal::from('cidr GENERATED ALWAYS AS (network("ip_address")) STORED'));
        $table->update();
    }
}
