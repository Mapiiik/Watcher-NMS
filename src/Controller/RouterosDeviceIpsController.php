<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenDate;

/**
 * RouterosDeviceIps Controller
 *
 * @property \App\Model\Table\RouterosDeviceIpsTable $RouterosDeviceIps
 * @method \App\Model\Entity\RouterosDeviceIp[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RouterosDeviceIpsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // filter
        $conditions = [];
        $maximum_age = $this->getRequest()->getQuery('maximum_age');
        if (!empty($maximum_age)) {
            $conditions[] = [
                'RouterosDeviceIps.modified >' => FrozenDate::create()->subDays((int)$maximum_age),
            ];
        } else {
            $conditions[] = [
                'RouterosDeviceIps.modified >' => FrozenDate::create()->subDays(14),
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RouterosDeviceIps.name ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceIps.ip_address::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'contain' => ['RouterosDevices'],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
        ];

        $routerosDeviceIps = $this->paginate($this->RouterosDeviceIps);

        $this->set(compact('routerosDeviceIps'));
    }

    /**
     * View method
     *
     * @param string|null $id RouterOS Device IP id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $routerosDeviceIp = $this->RouterosDeviceIps->get($id, [
            'contain' => [
                'RouterosDevices',
                'Creators',
                'Modifiers',
            ],
        ]);

        $this->set('routerosDeviceIp', $routerosDeviceIp);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $routerosDeviceIp = $this->RouterosDeviceIps->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $routerosDeviceIp = $this->RouterosDeviceIps
                ->patchEntity($routerosDeviceIp, $this->getRequest()->getData());

            if ($this->RouterosDeviceIps->save($routerosDeviceIp)) {
                $this->Flash->success(__('The RouterOS device IP has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The RouterOS device IP could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceIps->RouterosDevices->find('list', ['order' => 'name']);
        $this->set(compact('routerosDeviceIp', 'routerosDevices'));
    }

    /**
     * Edit method
     *
     * @param string|null $id RouterOS Device IP id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $routerosDeviceIp = $this->RouterosDeviceIps->get($id, [
            'contain' => [],
        ]);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $routerosDeviceIp = $this->RouterosDeviceIps
                ->patchEntity($routerosDeviceIp, $this->getRequest()->getData());

            if ($this->RouterosDeviceIps->save($routerosDeviceIp)) {
                $this->Flash->success(__('The RouterOS device IP has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The RouterOS device IP could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceIps->RouterosDevices->find('list', ['order' => 'name']);
        $this->set(compact('routerosDeviceIp', 'routerosDevices'));
    }

    /**
     * Delete method
     *
     * @param string|null $id RouterOS Device IP id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $routerosDeviceIp = $this->RouterosDeviceIps->get($id);
        if ($this->RouterosDeviceIps->delete($routerosDeviceIp)) {
            $this->Flash->success(__('The RouterOS device IP has been deleted.'));
        } else {
            $this->Flash->error(__('The RouterOS device IP could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
