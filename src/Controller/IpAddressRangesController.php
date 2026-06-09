<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * IpAddressRanges Controller
 *
 * @property \App\Model\Table\IpAddressRangesTable $IpAddressRanges
 */
class IpAddressRangesController extends AppController
{
    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        // filter
        $conditions = [];
        if ($this->access_point_id !== null) {
            $conditions[] = [
                'ipAddressRanges.access_point_id' => $this->access_point_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'IpAddressRanges.name ILIKE' => '%' . trim((string)$search) . '%',
                    'IpAddressRanges.ip_network::character varying ILIKE' => '%' . trim((string)$search) . '%',
                    'IpAddressRanges.ip_gateway::character varying ILIKE' => '%' . trim((string)$search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim((string)$search) . '%',
                    'ParentIpAddressRanges.name ILIKE' => '%' . trim((string)$search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];
        $ipAddressRanges = $this->paginate($this->IpAddressRanges->find(
            'all',
            contain: [
                'AccessPoints',
                'ParentIpAddressRanges',
            ],
            conditions: $conditions,
        ));

        $this->set(compact('ipAddressRanges'));
    }

    /**
     * View method
     *
     * @param string|null $id IP Address Range id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $ipAddressRange = $this->IpAddressRanges->get($id, contain: [
            'AccessPoints',
            'ParentIpAddressRanges',
            'Creators',
            'Modifiers',
        ]);

        $this->set(compact('ipAddressRange'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $ipAddressRange = $this->IpAddressRanges->newEmptyEntity();

        if ($this->access_point_id !== null) {
            $ipAddressRange->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->getRequest()->getData());
            if ($this->IpAddressRanges->save($ipAddressRange)) {
                $this->Flash->success(__('The IP address range has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $ipAddressRange->id]);
            }
            $this->Flash->error(__('The IP address range could not be saved. Please, try again.'));
        }
        $accessPoints = $this->IpAddressRanges->AccessPoints
            ->find('active')
            ->find('list', order: ['name'])
            ->all();
        $parentIpAddressRanges = $this->IpAddressRanges->ParentIpAddressRanges
            ->find('list', order: ['name'])
            ->all();
        $this->set(compact('ipAddressRange', 'accessPoints', 'parentIpAddressRanges'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id IP Address Range id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $ipAddressRange = $this->IpAddressRanges->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->getRequest()->getData());
            if ($this->IpAddressRanges->save($ipAddressRange)) {
                $this->Flash->success(__('The IP address range has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $ipAddressRange->id]);
            }
            $this->Flash->error(__('The IP address range could not be saved. Please, try again.'));
        }
        $accessPoints = $this->IpAddressRanges->AccessPoints
            ->find('list', order: ['name'])
            ->all();
        $parentIpAddressRanges = $this->IpAddressRanges->ParentIpAddressRanges
            ->find('list', order: ['name'])
            ->where(['ParentIpAddressRanges.id !=' => $id])
            ->all();
        $this->set(compact('ipAddressRange', 'accessPoints', 'parentIpAddressRanges'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id IP Address Range id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        if ($this->IpAddressRanges->delete($ipAddressRange)) {
            $this->Flash->success(__('The IP address range has been deleted.'));
        } else {
            $this->flashValidationErrors($ipAddressRange->getErrors());
            $this->Flash->error(__('The IP address range could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
