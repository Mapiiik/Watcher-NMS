<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddMonthOfElectricityMeterReadingToAccessPoints extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('access_points');
        $table->addColumn('month_of_electricity_meter_reading', 'integer', [
            'default' => null,
            'limit' => 2,
            'null' => true,
        ]);
        $table->update();
    }
}
