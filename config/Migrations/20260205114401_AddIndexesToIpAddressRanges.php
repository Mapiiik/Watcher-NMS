<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexesToIpAddressRanges extends BaseMigration
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
        $table = $this->table('ip_address_ranges');

        $table->addIndex(['access_point_id']);
        $table->addIndex(['parent_ip_address_range_id']);

        $table->update();
    }
}
