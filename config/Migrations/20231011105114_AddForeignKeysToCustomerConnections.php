<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysToCustomerConnections extends AbstractMigration
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

        $table->addForeignKey('access_point_id', 'access_points', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('customer_point_id', 'customer_points', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
