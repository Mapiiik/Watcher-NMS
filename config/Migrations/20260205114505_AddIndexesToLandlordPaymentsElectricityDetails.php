<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexesToLandlordPaymentsElectricityDetails extends BaseMigration
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
        $table = $this->table('landlord_payments_electricity_details');

        $table->addIndex(['landlord_payment_id']);

        $table->update();
    }
}
