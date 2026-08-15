<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddCustomerConnectionIdToRadioUnits extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('radio_units');

        // Where the unit stands, when it does not stand at an access point of ours. The client end
        // of a link is at the customer, and until now there was nowhere to say so - which left
        // every such unit with no place to compare the registered one against. An alternative to
        // the access point rather than an addition to it: a unit stands in one place.
        $table->addColumn('customer_connection_id', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
            'comment' => 'The customer the unit stands at, where it does not stand at an access point.',
        ]);

        $table->addIndex(['customer_connection_id']);

        $table->addForeignKey('customer_connection_id', 'customer_connections', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
