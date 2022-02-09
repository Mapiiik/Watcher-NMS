<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;
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
        $this->paginate = [
            'contain' => ['RouterosDevices'],
        ];

        $search = new SearchForm();
        if ($this->request->is(['get']) && ($this->request->getQuery('search')) !== null) {
            if ($search->execute(['search' => $this->request->getQuery('search')])) {
                $this->Flash->success(__('Search Set.'));
            } else {
                $this->Flash->error(__('There was a problem setting search.'));
            }
        }
        $this->set('search', $search);

        if ($search->getData('search') <> '') {
            $this->paginate['conditions']['OR'] = [
                'RouterosDevices.name ILIKE' => '%'
                . \trim($search->getData('search')) . '%',
                'RouterosDeviceIps.name ILIKE' => '%'
                . \trim($search->getData('search')) . '%',
                'RouterosDeviceIps.ip_address::character varying ILIKE' => '%'
                . \trim($search->getData('search')) . '%',
            ];
        }

        if ($this->request->getQuery('maximum_age') <> '') {
            $this->paginate['conditions']['RouterosDeviceIps.modified >'] =
                (new FrozenDate())->subDays((int)$this->request->getQuery('maximum_age'));
        } else {
            $this->paginate['conditions']['RouterosDeviceIps.modified >'] =
                (new FrozenDate())->subDays(14);
        }

        $routerosDeviceIps = $this->paginate($this->RouterosDeviceIps);

        $this->set(compact('routerosDeviceIps'));
    }

    /**
     * View method
     *
     * @param string|null $id Routeros Device Ip id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $routerosDeviceIp = $this->RouterosDeviceIps->get($id, [
            'contain' => ['RouterosDevices'],
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
        if ($this->request->is('post')) {
            $routerosDeviceIp = $this->RouterosDeviceIps->patchEntity($routerosDeviceIp, $this->request->getData());
            if ($this->RouterosDeviceIps->save($routerosDeviceIp)) {
                $this->Flash->success(__('The routeros device ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The routeros device ip could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceIps->RouterosDevices->find('list', ['order' => 'name']);
        $this->set(compact('routerosDeviceIp', 'routerosDevices'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Routeros Device Ip id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $routerosDeviceIp = $this->RouterosDeviceIps->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $routerosDeviceIp = $this->RouterosDeviceIps->patchEntity($routerosDeviceIp, $this->request->getData());
            if ($this->RouterosDeviceIps->save($routerosDeviceIp)) {
                $this->Flash->success(__('The routeros device ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The routeros device ip could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceIps->RouterosDevices->find('list', ['order' => 'name']);
        $this->set(compact('routerosDeviceIp', 'routerosDevices'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Routeros Device Ip id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $routerosDeviceIp = $this->RouterosDeviceIps->get($id);
        if ($this->RouterosDeviceIps->delete($routerosDeviceIp)) {
            $this->Flash->success(__('The routeros device ip has been deleted.'));
        } else {
            $this->Flash->error(__('The routeros device ip could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
