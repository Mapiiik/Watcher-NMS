<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\Event\EventInterface;

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
     * Global beforeFilter
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event An Event instance
     * @return \Cake\Http\Response|null|void
     * @link https://book.cakephp.org/4/en/controllers.html#request-life-cycle-callbacks
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function beforeFilter(EventInterface $event)
    {
        # Load selected access point ID from request
        $this->access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $this->access_point_id);

        parent::beforeFilter($event);
    }
}
