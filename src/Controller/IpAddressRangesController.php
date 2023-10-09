<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ErrorFormatterTrait;

/**
 * IpAddressRanges Controller
 *
 * @property \App\Model\Table\IpAddressRangesTable $IpAddressRanges
 * @method \App\Model\Entity\IpAddressRange[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class IpAddressRangesController extends AppController
{
    use ErrorFormatterTrait;

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // filter
        $conditions = [];
        if (isset($this->access_point_id)) {
            $conditions[] = [
                'ipAddressRanges.access_point_id' => $this->access_point_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'IpAddressRanges.name ILIKE' => '%' . trim($search) . '%',
                    'IpAddressRanges.ip_network::character varying ILIKE' => '%' . trim($search) . '%',
                    'IpAddressRanges.ip_gateway::character varying ILIKE' => '%' . trim($search) . '%',
                    'AccessPoints.name ILIKE' => '%' . trim($search) . '%',
                    'ParentIpAddressRanges.name ILIKE' => '%' . trim($search) . '%',
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
            conditions: $conditions
        ));

        $this->set(compact('ipAddressRanges'));
    }

    /**
     * View method
     *
     * @param string|null $id IP Address Range id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
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
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $ipAddressRange = $this->IpAddressRanges->newEmptyEntity();

        if (isset($this->access_point_id)) {
            $ipAddressRange->access_point_id = $this->access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->getRequest()->getData());
            if ($this->IpAddressRanges->save($ipAddressRange)) {
                $this->Flash->success(__('The IP address range has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The IP address range could not be saved. Please, try again.'));
        }
        $accessPoints = $this->IpAddressRanges->AccessPoints
            ->find('list', order: ['name'])
            ->all();
        $parentIpAddressRanges = $this->IpAddressRanges->ParentIpAddressRanges
            ->find('list', order: ['name'])
            ->all();
        $this->set(compact('ipAddressRange', 'accessPoints', 'parentIpAddressRanges'));
    }

    /**
     * Edit method
     *
     * @param string|null $id IP Address Range id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $ipAddressRange = $this->IpAddressRanges->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->getRequest()->getData());
            if ($this->IpAddressRanges->save($ipAddressRange)) {
                $this->Flash->success(__('The IP address range has been saved.'));

                return $this->redirect(['action' => 'index']);
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
    }

    /**
     * Delete method
     *
     * @param string|null $id IP Address Range id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        if ($this->IpAddressRanges->delete($ipAddressRange)) {
            $this->Flash->success(__('The IP address range has been deleted.'));
        } else {
            $this->flashValidationErrors($ipAddressRange->getErrors());
            $this->Flash->error(__('The IP address range could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
