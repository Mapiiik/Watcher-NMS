<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddPeriodToLandlordPayments extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('landlord_payments');
        $table->addColumn('period_from', 'date', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('period_until', 'date', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
