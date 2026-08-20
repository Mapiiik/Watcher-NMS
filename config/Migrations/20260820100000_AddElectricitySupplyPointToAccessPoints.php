<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddElectricitySupplyPointToAccessPoints extends BaseMigration
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
        $table = $this->table('access_points');

        // What the distributor answers about a supply point by. Without it an outage can only be
        // guessed at from the addresses around the mast; with it the answer is about this mast and
        // nothing else. Not unique, because two access points standing on one connection is a thing
        // that happens and refusing to record it would be the inventory arguing with the site.
        $table->addColumn('electricity_ean', 'string', [
            'default' => null,
            'limit' => 18,
            'null' => true,
            'comment' => 'The EAN of the supply point, which is what the distributor answers about.',
        ]);

        $table->addColumn('electricity_meter_number', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => true,
        ]);

        // Where the addresses around the mast came from and when. Kept beside the coordinates they
        // were looked up from, so that a mast that has been moved is looked up again rather than
        // answered about the place it used to stand.
        $table->addColumn('supply_resolved', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'null' => true,
            'comment' => 'When the addresses around the access point were last looked up.',
        ]);

        $table->addColumn('supply_resolution_failed', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
            'comment' => 'Why the last look-up of the addresses got nowhere, where it got nowhere.',
        ]);

        $table->addColumn('supply_resolved_gps_x', 'float', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);

        $table->addColumn('supply_resolved_gps_y', 'float', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);

        $table->addIndex(['electricity_ean']);

        $table->update();
    }
}
