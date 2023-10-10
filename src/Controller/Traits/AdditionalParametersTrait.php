<?php
declare(strict_types=1);

namespace App\Controller\Traits;

/**
 * @psalm-require-extends \Cake\Controller\Controller
 * @method \Cake\Http\ServerRequest getRequest()
 */
trait AdditionalParametersTrait
{
    /*
     * Access Point ID
     */
    protected ?string $access_point_id = null;

    /**
     * Load and set additonal parameters
     *
     * @return void
     */
    protected function loadAdditionalParameters()
    {
        # Load selected access point ID from request
        $this->access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $this->access_point_id);
    }
}
