<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexesToPowerSupplies extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('power_supplies');

        $table->addIndex(['access_point_id']);
        $table->addIndex(['power_supply_type_id']);

        $table->update();
    }
}
