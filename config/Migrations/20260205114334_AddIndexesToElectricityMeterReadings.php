<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexesToElectricityMeterReadings extends BaseMigration
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
        $table = $this->table('electricity_meter_readings');

        $table->addIndex(['access_point_id']);

        $table->update();
    }
}
