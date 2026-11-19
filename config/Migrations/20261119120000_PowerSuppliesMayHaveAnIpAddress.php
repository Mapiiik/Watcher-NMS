<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * A power supply reachable over the network keeps the address it answers at, so it can be pinged.
 */
class PowerSuppliesMayHaveAnIpAddress extends BaseMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('power_supplies')
            ->addColumn('ip_address', 'inet', ['null' => true])
            ->update();
    }
}
