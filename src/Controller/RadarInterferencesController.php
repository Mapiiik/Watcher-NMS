<?php
declare(strict_types=1);

namespace App\Controller;

use App\Application;
use Cake\Console\CommandRunner;

/**
 * RadarInterferences Controller
 *
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 * @method \App\Model\Entity\RadarInterference[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadarInterferencesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $radarInterferences = $this->paginate($this->RadarInterferences);

        $this->set(compact('radarInterferences'));
    }

    /**
     * View method
     *
     * @param string|null $id Radar Interference id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $radarInterference = $this->RadarInterferences->get($id, [
            'contain' => ['RouterosDeviceInterfaces' => ['RouterosDevices']],
        ]);

        $this->set('radarInterference', $radarInterference);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $radarInterference = $this->RadarInterferences->newEmptyEntity();
        if ($this->request->is('post')) {
            $radarInterference = $this->RadarInterferences->patchEntity($radarInterference, $this->request->getData());
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
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $radarInterference = $this->RadarInterferences->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $radarInterference = $this->RadarInterferences->patchEntity($radarInterference, $this->request->getData());
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
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $radarInterference = $this->RadarInterferences->get($id);
        if ($this->RadarInterferences->delete($radarInterference)) {
            $this->Flash->success(__('The radar interference has been deleted.'));
        } else {
            $this->Flash->error(__('The radar interference could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

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
