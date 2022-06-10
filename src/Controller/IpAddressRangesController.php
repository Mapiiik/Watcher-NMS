<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * IpAddressRanges Controller
 *
 * @property \App\Model\Table\IpAddressRangesTable $IpAddressRanges
 * @method \App\Model\Entity\IpAddressRange[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class IpAddressRangesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        // filter
        $conditions = [];
        if (isset($access_point_id)) {
            $conditions[] = [
                'ipAddressRanges.access_point_id' => $access_point_id,
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
            'contain' => ['AccessPoints', 'ParentIpAddressRanges'],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
        ];
        $ipAddressRanges = $this->paginate($this->IpAddressRanges);

        $this->set(compact('ipAddressRanges'));
    }

    /**
     * View method
     *
     * @param string|null $id Ip Address Range id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ipAddressRange = $this->IpAddressRanges->get($id, [
            'contain' => [
                'AccessPoints',
                'ParentIpAddressRanges',
                'Creators',
                'Modifiers',
            ],
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
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $ipAddressRange = $this->IpAddressRanges->newEmptyEntity();

        if (isset($access_point_id)) {
            $ipAddressRange->access_point_id = $access_point_id;
        }

        if ($this->getRequest()->is('post')) {
            $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->getRequest()->getData());
            if ($this->IpAddressRanges->save($ipAddressRange)) {
                $this->Flash->success(__('The ip address range has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The ip address range could not be saved. Please, try again.'));
        }
        $accessPoints = $this->IpAddressRanges->AccessPoints
            ->find('list', ['order' => 'name'])
            ->all();
        $parentIpAddressRanges = $this->IpAddressRanges->ParentIpAddressRanges
            ->find('list', ['order' => 'name'])
            ->all();
        $this->set(compact('ipAddressRange', 'accessPoints', 'parentIpAddressRanges'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Ip Address Range id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $access_point_id = $this->getRequest()->getParam('access_point_id');
        $this->set('access_point_id', $access_point_id);

        $ipAddressRange = $this->IpAddressRanges->get($id, [
            'contain' => [],
        ]);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->getRequest()->getData());
            if ($this->IpAddressRanges->save($ipAddressRange)) {
                $this->Flash->success(__('The ip address range has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The ip address range could not be saved. Please, try again.'));
        }
        $accessPoints = $this->IpAddressRanges->AccessPoints
            ->find('list', ['order' => 'name'])
            ->all();
        $parentIpAddressRanges = $this->IpAddressRanges->ParentIpAddressRanges
            ->find('list', ['order' => 'name'])
            ->where(['ParentIpAddressRanges.id !=' => $id])
            ->all();
        $this->set(compact('ipAddressRange', 'accessPoints', 'parentIpAddressRanges'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Ip Address Range id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        if ($this->IpAddressRanges->delete($ipAddressRange)) {
            $this->Flash->success(__('The ip address range has been deleted.'));
        } else {
            $this->Flash->error(__('The ip address range could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
