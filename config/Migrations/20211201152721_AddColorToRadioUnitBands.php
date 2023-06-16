<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddColorToRadioUnitBands extends AbstractMigration
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
        $table = $this->table('radio_unit_bands');
        $table->addColumn('color', 'string', [
            'default' => '#ffffff',
            'limit' => 7,
            'null' => false,
        ]);
        $table->update();
    }
}
