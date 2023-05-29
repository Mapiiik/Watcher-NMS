<?php
declare(strict_types=1);

use Migrations\AbstractMigration;
use Phinx\Util\Literal;

class CreatePaymentPurposes extends AbstractMigration
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
        $table = $this->table('payment_purposes', ['id' => false, 'primary_key' => ['id']]);

        $table->addColumn('id', 'uuid', [
            'default' => Literal::from('uuid_generate_v4()'),
            'limit' => null,
            'null' => false,
        ]);
        $table->addColumn('name', 'string', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('note', 'text', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('created', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'limit' => null,
            'null' => true,
            'precision' => 6,
            'scale' => 6,
        ]);
        $table->addColumn('created_by', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);
        $table->addColumn('modified', 'timestamp', [
            'timezone' => true,
            'default' => null,
            'limit' => null,
            'null' => true,
            'precision' => 6,
            'scale' => 6,
        ]);
        $table->addColumn('modified_by', 'uuid', [
            'default' => null,
            'limit' => null,
            'null' => true,
        ]);

        $table->create();
    }
}
