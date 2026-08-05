<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\ORM\Table;

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

    /**
     * Add the ids the route carries to the data a form submitted.
     *
     * The forms under an access point do not render that field: the route already says which one it
     * is, and the template leaves it out for exactly that reason. It has to reach the entity as
     * data rather than be set on it afterwards, or it misses the marshalling that checks and casts
     * it, and the validator asking for it - that reads what the request carried, not what the
     * entity ended up holding.
     *
     * Adding it after reading the request also settles which of the two wins: the route. Set on the
     * entity beforehand, as this used to be, a hand-written request could name a different access
     * point in the body and have it override the one in the URL.
     *
     * This is for creating a record only. Which access point an existing one belongs to is its own
     * to say, and a route naming another one must not quietly move it there.
     *
     * @param \Cake\ORM\Table $table Table the data is being marshalled for.
     * @param array<mixed> $data Data the request carried.
     * @return array<mixed>
     */
    protected function dataWithAdditionalParameters(Table $table, array $data): array
    {
        $parameters = [
            'access_point_id' => $this->access_point_id,
        ];

        foreach ($parameters as $field => $value) {
            if ($value !== null && $table->getSchema()->hasColumn($field)) {
                $data[$field] = $value;
            }
        }

        return $data;
    }
}
