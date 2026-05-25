<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddArchivedAndArchivedByToCustomerConnections extends BaseMigration
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
        $table = $this->table('customer_connections');
        $table->addColumn('archived', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('archived_by', 'uuid', [
            'default' => null,
            'null' => true,
        ]);

        $table->addIndex(['archived']);
        $table->addIndex(['archived_by']);

        $table->addForeignKey('archived_by', 'users', 'id');

        $table->update();
    }
}
