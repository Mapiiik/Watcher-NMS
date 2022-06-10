<?php
declare(strict_types=1);

namespace App\View\Cell;

use Cake\View\Cell;

/**
 * Access Point cell
 */
class AccessPointCell extends Cell
{
    /**
     * List of valid options that can be passed into this
     * cell's constructor.
     *
     * @var array<string>
     */
    protected $_validCellOptions = [];

    /**
     * Initialization logic run at the end of object construction.
     *
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * Default display method.
     *
     * @return void
     */
    public function display()
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');

        if ($access_point_id) {
            $accessPoint = $this->fetchTable('AccessPoints')->get($access_point_id);

            $this->set(compact('accessPoint'));
        }
    }
}
