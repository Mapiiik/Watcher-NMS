<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddForeignKeysToIpAddressRanges extends AbstractMigration
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
        $table = $this->table('ip_address_ranges');

        $table->addForeignKey('access_point_id', 'access_points', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);
        $table->addForeignKey('parent_ip_address_range_id', 'ip_address_ranges', 'id', [
            'delete' => 'NO_ACTION',
            'update' => 'NO_ACTION',
        ]);

        $table->update();
    }
}
