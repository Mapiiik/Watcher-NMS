<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Cake\Log\Log;
use Exception;

/**
 * CustomerConnections Controller
 *
 * @property \App\Model\Table\CustomerConnectionsTable $CustomerConnections
 */
class CustomerConnectionsController extends AppController
{
    /**
     * Index method
     *
     * Displays either active or archived customer connections based on the given filter.
     *
     * @param string|null $param Filter for the listing:
     *   - 'active' (default): shows only non-archived records
     *   - 'archived': shows only archived records
     * @return void Renders view
     */
    public function index(?string $param = 'active'): void
    {
        // normalize param
        $finder = $param === 'archived' ? 'archived' : 'active';

        //base query
        $customerConnectionsQuery = $this->CustomerConnections
            ->find($finder)
            ->contain([
                'AccessPoints',
                'CustomerPoints',
            ]);

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $search = trim((string)$search);

            $customerConnectionsQuery->where([
                'OR' => [
                    'CustomerConnections.name ILIKE' => '%' . $search . '%',
                    'CustomerConnections.customer_number ILIKE' => '%' . $search . '%',
                    'CustomerConnections.contract_number ILIKE' => '%' . $search . '%',
                    'CustomerPoints.name ILIKE' => '%' . $search . '%',
                    'AccessPoints.name ILIKE' => '%' . $search . '%',
                ],
            ]);
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $customerConnections = $this->paginate($customerConnectionsQuery);

        $this->set(compact('customerConnections', 'finder'));
    }

    /**
     * View method
     *
     * @param string|null $id Customer Connection id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $customerConnection = $this->CustomerConnections->get($id, contain: [
            'CustomerPoints',
            'AccessPoints',
            'CustomerConnectionIps',
            'RouterosDevices' => [
                'AccessPoints',
                'DeviceTypes',
            ],
            'RadioUnits' => [
                'RadioUnitTypes',
                'RadioLinks',
                'AntennaTypes',
            ],
            'Creators',
            'Modifiers',
        ]);

        $this->set('customerConnection', $customerConnection);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $customerConnection = $this->CustomerConnections->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $customerConnection = $this->CustomerConnections
                ->patchEntity($customerConnection, $this->getRequest()->getData());

            if ($this->CustomerConnections->save($customerConnection)) {
                $this->Flash->success(__('The customer connection has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $customerConnection->id]);
            }
            $this->Flash->error(__('The customer connection could not be saved. Please, try again.'));
        }
        $customerPoints = $this->CustomerConnections->CustomerPoints->find('list', order: ['name']);
        $accessPoints = $this->CustomerConnections->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('customerConnection', 'customerPoints', 'accessPoints'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $customerConnection = $this->CustomerConnections->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $customerConnection = $this->CustomerConnections
                ->patchEntity($customerConnection, $this->getRequest()->getData());

            if ($this->CustomerConnections->save($customerConnection)) {
                $this->Flash->success(__('The customer connection has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $customerConnection->id]);
            }
            $this->Flash->error(__('The customer connection could not be saved. Please, try again.'));
        }
        $customerPoints = $this->CustomerConnections->CustomerPoints->find('list', order: ['name']);
        $accessPoints = $this->CustomerConnections->AccessPoints
            ->find('list', order: ['name'])
            ->all();

        $this->set(compact('customerConnection', 'customerPoints', 'accessPoints'));

        return null;
    }

    /**
     * Archive method
     *
     * Marks the customer connection as archived (soft-delete) by setting archived timestamp
     * and archived_by user ID. Does not remove the record from the database.
     *
     * @param string|null $id Customer Connection ID
     * @return \Cake\Http\Response|null Redirects to index
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function archive(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $customerConnection = $this->CustomerConnections->get($id);

        try {
            $this->CustomerConnections->archive(
                $customerConnection,
                $this->getRequest()->getAttribute('identity')['id'] ?? null,
            );

            $this->Flash->success(__('The customer connection has been archived.'));
        } catch (Exception $e) {
            Log::error('Failed to archive customer connection: ' . $e->getMessage());
            $this->Flash->error(
                __('The customer connection could not be archived. Please try again.'),
            );
        }

        return $this->afterEditRedirect(['action' => 'view', $customerConnection->id]);
    }

    /**
     * Restore method
     *
     * Reverts an archived customer connection back to active state by clearing the
     * archived timestamp and archived_by user ID.
     *
     * @param string|null $id Customer Connection ID
     * @return \Cake\Http\Response|null Redirects to index
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function restore(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $customerConnection = $this->CustomerConnections->get($id);

        try {
            $this->CustomerConnections->restore($customerConnection);

            $this->Flash->success(
                __('The customer connection has been restored.'),
            );
        } catch (Exception $e) {
            Log::error('Failed to restore customer connection: ' . $e->getMessage());
            $this->Flash->error(
                __('The customer connection could not be restored. Please try again.'),
            );
        }

        return $this->afterEditRedirect(['action' => 'view', $customerConnection->id]);
    }

    /**
     * Delete method
     *
     * @param string|null $id Customer Connection id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $customerConnection = $this->CustomerConnections->get($id);
        if ($this->CustomerConnections->delete($customerConnection)) {
            $this->Flash->success(__('The customer connection has been deleted.'));
        } else {
            $this->flashValidationErrors($customerConnection->getErrors());
            $this->Flash->error(__('The customer connection could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
