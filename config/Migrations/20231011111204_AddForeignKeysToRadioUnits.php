<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysToRadioUnits extends AbstractMigration
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
        $table = $this->table('radio_units');

        $table->addForeignKey('access_point_id', 'access_points', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('antenna_type_id', 'antenna_types', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('radio_link_id', 'radio_links', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('radio_unit_type_id', 'radio_unit_types', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
