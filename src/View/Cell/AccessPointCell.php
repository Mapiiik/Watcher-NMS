<?php
declare(strict_types=1);

namespace App\View\Cell;

use App\Model\Table\AccessPointsTable;
use Cake\View\Cell;
use Override;

/**
 * Access Point cell
 *
 * @extends \Cake\View\Cell<\App\View\AppView>
 */
class AccessPointCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var list<string>
     */
    protected array $_validCellOptions = [];

    /**
     * Initialization logic run at the end of object construction.
     *
     * @return void
     */
    #[Override]
    public function initialize(): void
    {
    }

    /**
     * Default display method.
     *
     * @return void
     */
    public function display(): void
    {
        $access_point_id = $this->request->getParam('access_point_id');

        if ($access_point_id) {
            $accessPoint = $this->fetchTable(AccessPointsTable::class)->get($access_point_id);

            $this->set(compact('accessPoint'));
        }
    }
}
