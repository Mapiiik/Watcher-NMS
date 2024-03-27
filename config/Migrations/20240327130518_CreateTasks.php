<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreateTasks extends AbstractMigration
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
        $table = $this->table('tasks', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => Literal::from('uuid_generate_v4()'),
            'null' => false,
        ]);
        $table->addColumn('nid', 'biginteger', [
            'identity' => true,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('task_state_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('task_type_id', 'uuid', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => true,
        ]);

        $table->addColumn('subject', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('text', 'text', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('priority', 'integer', [
            'default' => '0',
            'limit' => 10,
            'null' => false,
        ]);

        $table->addColumn('email', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('phone', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);

        $table->addColumn('start_date', 'date', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('finish_date', 'date', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('estimated_date', 'date', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('critical_date', 'date', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);

        $table->addColumn('access_point_id', 'uuid', [
            'default' => null,
            'null' => true,
        ]);

        $table->addColumn('created', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('created_by', 'uuid', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified_by', 'uuid', [
            'default' => null,
            'null' => true,
        ]);

        $table->addIndex('nid', [
            'unique' => true,
        ]);

        $table->addForeignKey('task_state_id', 'task_states', 'id');
        $table->addForeignKey('task_type_id', 'task_types', 'id');
        $table->addForeignKey('user_id', 'users', 'id');

        $table->addForeignKey('access_point_id', 'access_points', 'id');

        $table->addForeignKey('created_by', 'users', 'id');
        $table->addForeignKey('modified_by', 'users', 'id');

        $table->create();
    }
}
