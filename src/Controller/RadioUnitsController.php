<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RadioUnits Controller
 *
 * @property \App\Model\Table\RadioUnitsTable $RadioUnits
 *
 * @method \App\Model\Entity\RadioUnit[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadioUnitsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['AccessPoints', 'RadioLinks', 'RadioUnitTypes', 'AntennaTypes'],
        ];
        $radioUnits = $this->paginate($this->RadioUnits);

        $this->set(compact('radioUnits'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $radioUnit = $this->RadioUnits->get($id, [
            'contain' => ['AccessPoints', 'RadioLinks', 'RadioUnitTypes', 'AntennaTypes'],
        ]);

        $this->set('radioUnit', $radioUnit);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $radioUnit = $this->RadioUnits->newEmptyEntity();
        if ($this->request->is('post')) {
            $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->request->getData());
            if ($this->RadioUnits->save($radioUnit)) {
                $this->Flash->success(__('The radio unit has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', ['limit' => 200]);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', ['limit' => 200]);
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', ['limit' => 200]);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', ['limit' => 200]);
        $this->set(compact('radioUnit', 'accessPoints', 'radioLinks', 'radioUnitTypes', 'antennaTypes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $radioUnit = $this->RadioUnits->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $radioUnit = $this->RadioUnits->patchEntity($radioUnit, $this->request->getData());
            if ($this->RadioUnits->save($radioUnit)) {
                $this->Flash->success(__('The radio unit has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit could not be saved. Please, try again.'));
        }
        $accessPoints = $this->RadioUnits->AccessPoints->find('list', ['limit' => 200]);
        $radioLinks = $this->RadioUnits->RadioLinks->find('list', ['limit' => 200]);
        $radioUnitTypes = $this->RadioUnits->RadioUnitTypes->find('list', ['limit' => 200]);
        $antennaTypes = $this->RadioUnits->AntennaTypes->find('list', ['limit' => 200]);
        $this->set(compact('radioUnit', 'accessPoints', 'radioLinks', 'radioUnitTypes', 'antennaTypes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Unit id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $radioUnit = $this->RadioUnits->get($id);
        if ($this->RadioUnits->delete($radioUnit)) {
            $this->Flash->success(__('The radio unit has been deleted.'));
        } else {
            $this->Flash->error(__('The radio unit could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
