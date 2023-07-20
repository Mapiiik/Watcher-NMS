<?php
declare(strict_types=1);

namespace App\Controller;

use App\Application;
use App\Controller\Traits\ErrorFormatterTrait;
use Cake\Console\CommandRunner;

/**
 * RadarInterferences Controller
 *
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 * @method \App\Model\Entity\RadarInterference[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadarInterferencesController extends AppController
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

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RadarInterferences.name ILIKE' => '%' . trim($search) . '%',
                    'RadarInterferences.mac_address::character varying ILIKE' => '%' . trim($search) . '%',
                    'RadarInterferences.ssid ILIKE' => '%' . trim($search) . '%',
                    'RadarInterferences.radio_name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'order' => ['name' => 'ASC'],
        ];

        $radarInterferences = $this->paginate($this->RadarInterferences->find(
            'all',
            conditions: $conditions
        ));

        $this->set(compact('radarInterferences'));
    }

    /**
     * View method
     *
     * @param string|null $id Radar Interference id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $radarInterference = $this->RadarInterferences->get($id, contain: [
            'RouterosDeviceInterfaces' => ['RouterosDevices'],
            'Creators',
            'Modifiers',
        ]);

        $this->set('radarInterference', $radarInterference);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $radarInterference = $this->RadarInterferences->newEmptyEntity();
        if ($this->getRequest()->is('post')) {
            $radarInterference = $this->RadarInterferences
                ->patchEntity($radarInterference, $this->getRequest()->getData());

            if ($this->RadarInterferences->save($radarInterference)) {
                $this->Flash->success(__('The radar interference has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radar interference could not be saved. Please, try again.'));
        }
        $this->set(compact('radarInterference'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Radar Interference id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $radarInterference = $this->RadarInterferences->get($id, contain: []);
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $radarInterference = $this->RadarInterferences
                ->patchEntity($radarInterference, $this->getRequest()->getData());

            if ($this->RadarInterferences->save($radarInterference)) {
                $this->Flash->success(__('The radar interference has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radar interference could not be saved. Please, try again.'));
        }
        $this->set(compact('radarInterference'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Radar Interference id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $radarInterference = $this->RadarInterferences->get($id);
        if ($this->RadarInterferences->delete($radarInterference)) {
            $this->Flash->success(__('The radar interference has been deleted.'));
        } else {
            $this->flashValidationErrors($radarInterference->getErrors());
            $this->Flash->error(__('The radar interference could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Update data from configured URL
     *
     * @return \Cake\Http\Response|null Redirects to index.
     */
    public function updateOnline()
    {
        $runner = new CommandRunner(new Application(dirname(__DIR__) . '/../config'), 'cake');
        if ($runner->run(['cake', 'radar_interferences_update']) === 0) {
            $this->Flash->success(__('The radar interferences table has been updated.'));
        } else {
            $this->Flash->error(__('The radar interferences table could not be updated. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * List devices which interfere
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function devices()
    {
        $radarInterferences = $this->RadarInterferences->find();

        $radarInterferences->join([
            'RouterosDeviceInterfaces' => [
                'table' => 'routeros_device_interfaces',
                'type' => 'INNER',
                'conditions' => 'RadarInterferences.mac_address = RouterosDeviceInterfaces.mac_address',
            ],
            'RouterosDevices' => [
                'table' => 'routeros_devices',
                'type' => 'INNER',
                'conditions' => 'RouterosDeviceInterfaces.routeros_device_id = RouterosDevices.id',
            ],
        ]);

        $radarInterferences->select($this->RadarInterferences);
        $radarInterferences->select(['routeros_device_id' => 'RouterosDevices.id']);
        $radarInterferences->select(['routeros_device_name' => 'RouterosDevices.name']);
        $radarInterferences->select(['routeros_device_interface_id' => 'RouterosDeviceInterfaces.id']);
        $radarInterferences->select(['routeros_device_interface_name' => 'RouterosDeviceInterfaces.name']);

        $this->set('radarInterferences', $this->paginate($radarInterferences));
    }
}
