<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\Http\Response;
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
     * Send a request whose route names an access point the record does not belong to on to the URL
     * the record does answer to.
     *
     * The nested routes match any id against any record: `/access-points/{stranger}/radio-units/
     * view/{id}` answers with the radio unit all the same, under a heading naming an access point
     * it has nothing to do with. A hand-written or gone-stale URL should not be an error - the
     * record exists and the caller is welcome to it - so it is answered where it belongs instead.
     *
     * Only reading is redirected. A `delete` arrives as a POST and would come back as a GET, which
     * would leave the record standing and say it was removed; and a submitted `edit` carries the
     * form, which a redirect would drop. Neither reads the route's id anyway - what belongs to what
     * is the record's own to say - so both are left to go on with the record they were given.
     *
     * @return \Cake\Http\Response|null
     */
    protected function redirectIfTheRouteNamesAnother(): ?Response
    {
        $request = $this->getRequest();

        if (!$request->is('get') || !in_array($request->getParam('action'), ['view', 'edit'], true)) {
            return null;
        }

        $id = $request->getParam('pass.0');
        $parameters = ['access_point_id' => $this->access_point_id];
        $parameters = array_filter($parameters, fn(?string $value): bool => $value !== null);

        if (!is_string($id) || $parameters === []) {
            return null;
        }

        $table = $this->fetchTable();
        $fields = array_intersect(array_keys($parameters), $table->getSchema()->columns());
        $primaryKey = $table->getPrimaryKey();

        // a key of several columns is not something one passed id names
        if ($fields === [] || !is_string($primaryKey)) {
            return null;
        }

        $record = $table->find()
            ->select($fields)
            ->where([$primaryKey => $id])
            ->disableHydration()
            ->first();

        // a record the route cannot name is a record the action will not find either, and saying so
        // is its business rather than this one's
        if (!is_array($record) || $record == array_intersect_key($parameters, $record)) {
            return null;
        }

        return $this->redirect([
            'action' => $request->getParam('action'),
            $id,
        ] + $record);
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
