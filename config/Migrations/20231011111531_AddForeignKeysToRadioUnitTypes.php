<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysToRadioUnitTypes extends AbstractMigration
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
        $table = $this->table('radio_unit_types');

        $table->addForeignKey('manufacturer_id', 'manufacturers', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('radio_unit_band_id', 'radio_unit_bands', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
