<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddIndexesToAccessPoints extends BaseMigration
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
        $table = $this->table('access_points');

        $table->addIndex(['parent_access_point_id']);
        $table->addIndex(['access_point_type_id']);

        $table->update();
    }
}
