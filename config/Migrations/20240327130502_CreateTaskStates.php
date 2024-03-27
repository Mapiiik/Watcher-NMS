<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreateTaskStates extends AbstractMigration
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
        $table = $this->table('task_states', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => Literal::from('uuid_generate_v4()'),
            'null' => false,
        ]);
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('color', 'string', [
            'default' => '#ffffff',
            'limit' => 7,
            'null' => false,
        ]);
        $table->addColumn('priority', 'integer', [
            'default' => '0',
            'limit' => 10,
            'null' => false,
        ]);
        $table->addColumn('completed', 'boolean', [
            'default' => false,
            'null' => false,
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

        $table->addForeignKey('created_by', 'users', 'id');
        $table->addForeignKey('modified_by', 'users', 'id');

        $table->create();
    }
}
