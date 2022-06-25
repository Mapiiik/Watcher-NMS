<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreateAuditLogs extends AbstractMigration
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
        $this->table('audit_logs', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => Literal::from('uuid_generate_v4()'),
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('transaction', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 7,
                'null' => false,
            ])
            ->addColumn('primary_key', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('display_value', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('source', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('parent_source', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('original', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('changed', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meta', 'json', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(['transaction'])
            ->addIndex(['type'])
            ->addIndex(['primary_key'])
            ->addIndex(['display_value'])
            ->addIndex(['source'])
            ->addIndex(['parent_source'])
            ->addIndex(['username'])
            ->addIndex(['created'])
            ->create();
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
        $this->table('audit_logs')->drop()->save();
    }
}
