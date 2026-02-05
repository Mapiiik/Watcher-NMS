<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexesToRadioUnits extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('radio_units');

        $table->addIndex(['access_point_id']);
        $table->addIndex(['radio_link_id']);
        $table->addIndex(['radio_unit_type_id']);
        $table->addIndex(['antenna_type_id']);

        $table->update();
    }
}
