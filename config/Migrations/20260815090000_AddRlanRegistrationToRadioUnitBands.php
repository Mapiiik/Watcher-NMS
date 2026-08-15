<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddRlanRegistrationToRadioUnitBands extends BaseMigration
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
        $table = $this->table('radio_unit_bands');

        // Whether a unit on this band is expected to be registered with the regulator. Off by
        // default: turning it on for a band nobody has written a station number against would
        // report every unit of it at once, which is a decision to be made band by band rather
        // than by a migration.
        $table->addColumn('units_require_rlan_registration', 'boolean', [
            'default' => false,
            'null' => false,
            'comment' => 'Whether a radio unit on this band has to be registered with the regulator.',
        ]);

        $table->update();
    }
}
