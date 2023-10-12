<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\DateTime;

/**
 * RouterosDeviceInterfaces Controller
 *
 * @property \App\Model\Table\RouterosDeviceInterfacesTable $RouterosDeviceInterfaces
 * @method \App\Model\Entity\RouterosDeviceInterface[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RouterosDeviceInterfacesController extends AppController
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
                'RouterosDeviceInterfaces.modified >' => DateTime::now()->subDays((int)$maximum_age),
            ];
        } else {
            $conditions[] = [
                'RouterosDeviceInterfaces.modified >' => DateTime::now()->subDays(14),
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RouterosDeviceInterfaces.name ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.comment ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.mac_address::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.ssid ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.bssid::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.band ILIKE' => '%' . trim($search) . '%',
                    'RouterosDeviceInterfaces.frequency::character varying ILIKE' => '%' . trim($search) . '%',
                    'RouterosDevices.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $routerosDeviceInterfaces = $this->paginate($this->RouterosDeviceInterfaces->find(
            'all',
            contain: [
                'RouterosDevices',
            ],
            conditions: $conditions
        ));

        $this->set(compact('routerosDeviceInterfaces'));
    }

    /**
     * View method
     *
     * @param string|null $id RouterOS Device Interface id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->get($id, contain: [
            'RouterosDevices',
            'Creators',
            'Modifiers',
        ]);

        $this->set('routerosDeviceInterface', $routerosDeviceInterface);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $routerosDeviceInterface = $this->RouterosDeviceInterfaces
                ->patchEntity($routerosDeviceInterface, $this->getRequest()->getData());

            if ($this->RouterosDeviceInterfaces->save($routerosDeviceInterface)) {
                $this->Flash->success(__('The RouterOS device interface has been saved.'));

                return $this->afterAddRedirect(['action' => 'view', $routerosDeviceInterface->id]);
            }
            $this->Flash->error(__('The RouterOS device interface could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceInterfaces->RouterosDevices->find('list', order: ['name']);
        $this->set(compact('routerosDeviceInterface', 'routerosDevices'));
    }

    /**
     * Edit method
     *
     * @param string|null $id RouterOS Device Interface id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $routerosDeviceInterface = $this->RouterosDeviceInterfaces
                ->patchEntity($routerosDeviceInterface, $this->getRequest()->getData());

            if ($this->RouterosDeviceInterfaces->save($routerosDeviceInterface)) {
                $this->Flash->success(__('The RouterOS device interface has been saved.'));

                return $this->afterEditRedirect(['action' => 'view', $routerosDeviceInterface->id]);
            }
            $this->Flash->error(__('The RouterOS device interface could not be saved. Please, try again.'));
        }
        $routerosDevices = $this->RouterosDeviceInterfaces->RouterosDevices->find('list', order: ['name']);
        $this->set(compact('routerosDeviceInterface', 'routerosDevices'));
    }

    /**
     * Delete method
     *
     * @param string|null $id RouterOS Device Interface id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $routerosDeviceInterface = $this->RouterosDeviceInterfaces->get($id);
        if ($this->RouterosDeviceInterfaces->delete($routerosDeviceInterface)) {
            $this->Flash->success(__('The RouterOS device interface has been deleted.'));
        } else {
            $this->flashValidationErrors($routerosDeviceInterface->getErrors());
            $this->Flash->error(__('The RouterOS device interface could not be deleted. Please, try again.'));
        }

        return $this->afterDeleteRedirect(['action' => 'index']);
    }
}
