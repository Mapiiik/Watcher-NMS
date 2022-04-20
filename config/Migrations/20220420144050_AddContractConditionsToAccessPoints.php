<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddContractConditionsToAccessPoints extends AbstractMigration
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
        $table->addColumn('contract_conditions', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
