<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;

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
        $ipAddressRanges = $this->IpAddressRanges->find('all', [
            'contain' => [
                'AccessPoints',
                'ParentIpAddressRanges',
            ],
        ])->all();

        $this->set(compact('ipAddressRanges'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRanges']);
    }

    /**
     * Search method
     *
     * @property \App\Model\Table\IpAddressRangesTable $IpAddressRanges
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function search()
    {
        $options = [
            'contain' => [
                'AccessPoints',
                'ParentIpAddressRanges',
            ],
            'order' => ['masklen(IpAddressRanges.ip_network)' => 'DESC'],
        ];

        if ($this->request->getQuery('access_point_id') !== null) {
            $options['conditions']['IpAddressRanges.access_point_id'] = $this->request->getQuery('access_point_id');
        }
        if ($this->request->getQuery('for_subnets') !== null) {
            $options['conditions']['IpAddressRanges.for_subnets'] = $this->request->getQuery('for_subnets');
        }
        if ($this->request->getQuery('for_customer_addresses_set_via_radius') !== null) {
            $options['conditions']['IpAddressRanges.for_customer_addresses_set_via_radius']
                = $this->request->getQuery('for_customer_addresses_set_via_radius');
        }
        if ($this->request->getQuery('for_customer_addresses_set_manually') !== null) {
            $options['conditions']['IpAddressRanges.for_customer_addresses_set_manually']
                = $this->request->getQuery('for_customer_addresses_set_manually');
        }
        if ($this->request->getQuery('for_technology_addresses_set_manually') !== null) {
            $options['conditions']['IpAddressRanges.for_technology_addresses_set_manually']
                = $this->request->getQuery('for_technology_addresses_set_manually');
        }
        if ($this->request->getQuery('for_customer_networks_set_via_radius') !== null) {
            $options['conditions']['IpAddressRanges.for_customer_networks_set_via_radius']
                = $this->request->getQuery('for_customer_networks_set_via_radius');
        }
        if ($this->request->getQuery('for_customer_networks_set_manually') !== null) {
            $options['conditions']['IpAddressRanges.for_customer_networks_set_manually']
                = $this->request->getQuery('for_customer_networks_set_manually');
        }
        if ($this->request->getQuery('for_technology_networks_set_manually') !== null) {
            $options['conditions']['IpAddressRanges.for_technology_networks_set_manually']
                = $this->request->getQuery('for_technology_networks_set_manually');
        }

        if ($this->request->getQuery('ip_address') !== null) {
            $options['conditions']['IpAddressRanges.ip_network >>='] = $this->request->getQuery('ip_address');
        }

        $ipAddressRanges = $this->IpAddressRanges->find('all', $options);

        $this->set(compact('ipAddressRanges'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRanges']);
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
            'contain' => ['AccessPoints', 'ParentIpAddressRanges'],
        ]);

        $this->set(compact('ipAddressRange'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->request->allowMethod(['post', 'put']);
        $ipAddressRange = $this->IpAddressRanges->newEntity($this->request->getData());
        if ($this->IpAddressRanges->save($ipAddressRange)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'accessPoint' => $ipAddressRange,
        ]);
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange', 'message']);
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
        $this->request->allowMethod(['patch', 'post', 'put']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->request->getData());
        if ($this->IpAddressRanges->save($ipAddressRange)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'accessPoint' => $ipAddressRange,
        ]);
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange', 'message']);
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
        $this->request->allowMethod(['delete']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        if ($this->IpAddressRanges->delete($ipAddressRange)) {
            $message = 'Deleted';
        } else {
            $message = 'Error';
        }
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
