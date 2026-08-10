<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddFrequencyRangeAndDeviceRequirementToRadioUnitBands extends BaseMigration
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

        // Where the band sits, so that a frequency read off a device can be told which band it is
        // on without the reader knowing. Left empty the band is simply not recognised by frequency.
        $table->addColumn('minimum_frequency', 'integer', [
            'default' => null,
            'null' => true,
            'comment' => 'Lowest frequency of the band, in MHz as the radio units record it.',
        ]);
        $table->addColumn('maximum_frequency', 'integer', [
            'default' => null,
            'null' => true,
            'comment' => 'Highest frequency of the band, in MHz as the radio units record it.',
        ]);

        // Whether a radio a device reports on this band is expected to be recorded as a radio unit.
        // Off by default: turning it on for a band nobody has recorded yet would report every radio
        // on it, which is a decision to be made band by band rather than by a migration.
        $table->addColumn('devices_require_radio_unit', 'boolean', [
            'default' => false,
            'null' => false,
            'comment' => 'Whether a device radio on this band has to be recorded as a radio unit.',
        ]);

        $table->update();
    }
}
