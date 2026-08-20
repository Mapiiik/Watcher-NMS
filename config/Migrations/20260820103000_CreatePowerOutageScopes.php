<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Migrations\Db\Literal;

class CreatePowerOutageScopes extends BaseMigration
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
        $this->table('power_outage_scopes', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'uuid', [
                'default' => Literal::from('uuid_generate_v4()'),
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('power_outage_id', 'uuid', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            // Which reading saw the outage - one municipality, or one supply point. The mirror is
            // read a question at a time and most questions come back with nothing, so an empty
            // answer cannot be taken for a broken one the way a whole register could be. What may
            // be swept is therefore decided by which questions were answered this run, and an
            // outage nobody asked about again keeps standing.
            ->addColumn('scope', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
                'comment' => 'The reading the outage was seen by.',
            ])
            ->addColumn('created', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'timezone' => true,
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['power_outage_id', 'scope'], ['unique' => true])
            ->addIndex(['scope'])
            ->addForeignKey('power_outage_id', 'power_outages', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->create();
    }
}
