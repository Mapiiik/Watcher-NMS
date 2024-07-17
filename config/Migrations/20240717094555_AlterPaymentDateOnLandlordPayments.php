<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AlterPaymentDateOnLandlordPayments extends AbstractMigration
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
        $table = $this->table('landlord_payments');
        $table->changeColumn('payment_date', 'date', [
            'default' => null,
            'null' => false,
        ]);
        $table->update();
    }
}
