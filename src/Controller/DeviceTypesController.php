<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * DeviceTypes Controller
 *
 * @property \App\Model\Table\DeviceTypesTable $DeviceTypes
 * @method \App\Model\Entity\DeviceType[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DeviceTypesController extends AppController
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

        // search
        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'DeviceTypes.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
        ];

        $deviceTypes = $this->paginate($this->DeviceTypes);

        $this->set(compact('deviceTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Device Type id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $deviceType = $this->DeviceTypes->get($id, [
            'contain' => ['RouterosDevices' => ['AccessPoints', 'CustomerConnections']],
        ]);

        $this->set(compact('deviceType'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $deviceType = $this->DeviceTypes->newEmptyEntity();
        if ($this->request->is('post')) {
            $deviceType = $this->DeviceTypes->patchEntity($deviceType, $this->request->getData());
            if ($this->DeviceTypes->save($deviceType)) {
                $this->Flash->success(__('The device type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The device type could not be saved. Please, try again.'));
        }
        $this->set(compact('deviceType'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Device Type id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $deviceType = $this->DeviceTypes->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $deviceType = $this->DeviceTypes->patchEntity($deviceType, $this->request->getData());
            if ($this->DeviceTypes->save($deviceType)) {
                $this->Flash->success(__('The device type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The device type could not be saved. Please, try again.'));
        }
        $this->set(compact('deviceType'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Device Type id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $deviceType = $this->DeviceTypes->get($id);
        if ($this->DeviceTypes->delete($deviceType)) {
            $this->Flash->success(__('The device type has been deleted.'));
        } else {
            $this->Flash->error(__('The device type could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
