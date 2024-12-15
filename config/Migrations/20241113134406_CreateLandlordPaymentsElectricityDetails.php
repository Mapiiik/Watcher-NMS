<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreateLandlordPaymentsElectricityDetails extends AbstractMigration
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
        $table = $this->table('landlord_payments_electricity_details', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => Literal::from('uuid_generate_v4()'),
            'null' => false,
        ]);

        $table->addColumn('landlord_payment_id', 'uuid', [
            'null' => false,
        ]);

        $table->addColumn('low_rate_kwh_used', 'decimal', [
            'default' => null,
            'null' => true,
            'precision' => 8,
            'scale' => 3,
        ]);
        $table->addColumn('low_rate_price_per_kwh', 'decimal', [
            'default' => null,
            'null' => true,
            'precision' => 8,
            'scale' => 2,
        ]);
        $table->addColumn('high_rate_kwh_used', 'decimal', [
            'default' => null,
            'null' => true,
            'precision' => 8,
            'scale' => 3,
        ]);
        $table->addColumn('high_rate_price_per_kwh', 'decimal', [
            'default' => null,
            'null' => true,
            'precision' => 8,
            'scale' => 2,
        ]);

        $table->addColumn('created', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('created_by', 'uuid', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified_by', 'uuid', [
            'default' => null,
            'null' => true,
        ]);

        $table->addForeignKey('landlord_payment_id', 'landlord_payments', 'id');

        $table->addForeignKey('created_by', 'users', 'id');
        $table->addForeignKey('modified_by', 'users', 'id');

        $table->create();
    }
}
