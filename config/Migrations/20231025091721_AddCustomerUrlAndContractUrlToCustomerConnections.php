<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCustomerUrlAndContractUrlToCustomerConnections extends AbstractMigration
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
        $table = $this->table('customer_connections');
        $table->addColumn('customer_url', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('contract_url', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
