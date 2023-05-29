<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreateLandlordPayments extends AbstractMigration
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
        $table = $this->table('landlord_payments', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => Literal::from('uuid_generate_v4()'),
            'limit' => null,
            'null' => false,
        ]);
        $table->addColumn('access_point_id', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('payment_purpose_id', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('payment_date', 'date', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('amount_paid', 'decimal', [
            'default' => null,
            'precision' => 8,
            'scale' => 2,
        ]);
        $table->addColumn('note', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('created', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'limit' => null,
            'null' => true,
            'precision' => 6,
            'scale' => 6,
        ]);
        $table->addColumn('created_by', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'limit' => null,
            'null' => true,
            'precision' => 6,
            'scale' => 6,
        ]);
        $table->addColumn('modified_by', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);

        $table->addForeignKey('access_point_id', 'access_points', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('payment_purpose_id', 'payment_purposes', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->create();
    }
}
