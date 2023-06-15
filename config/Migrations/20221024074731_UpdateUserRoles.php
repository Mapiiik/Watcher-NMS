<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class UpdateUserRoles extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     *
     * @return void
     */
    public function up()
    {
        $this->getQueryBuilder('update')
            ->update('users')
            ->set('role', 'network-technician')
            ->where(['role' => 'operator'])
            ->execute();

            $this->getQueryBuilder('update')
            ->update('users')
            ->set('role', 'customer-service-technician')
            ->where(['role' => 'technician'])
            ->execute();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     *
     * @return void
     */
    public function down()
    {
        $this->getQueryBuilder('update')
            ->update('users')
            ->set('role', 'operator')
            ->where(['role' => 'network-technician'])
            ->execute();

            $this->getQueryBuilder('update')
            ->update('users')
            ->set('role', 'technician')
            ->where(['role' => 'customer-service-technician'])
            ->execute();
    }
}
