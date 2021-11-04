<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RadioUnitTypes Controller
 *
 * @property \App\Model\Table\RadioUnitTypesTable $RadioUnitTypes
 * @method \App\Model\Entity\RadioUnitType[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadioUnitTypesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['RadioUnitBands', 'Manufacturers'],
        ];
        $radioUnitTypes = $this->paginate($this->RadioUnitTypes);

        $this->set(compact('radioUnitTypes'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $radioUnitType = $this->RadioUnitTypes->get($id, [
            'contain' => [
                'RadioUnitBands',
                'Manufacturers',
                'RadioUnits' => [
                    'AccessPoints',
                    'RadioLinks',
                    'AntennaTypes',
                ],
            ],
        ]);

        $this->set('radioUnitType', $radioUnitType);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $radioUnitType = $this->RadioUnitTypes->newEmptyEntity();
        if ($this->request->is('post')) {
            $radioUnitType = $this->RadioUnitTypes->patchEntity($radioUnitType, $this->request->getData());
            if ($this->RadioUnitTypes->save($radioUnitType)) {
                $this->Flash->success(__('The radio unit type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit type could not be saved. Please, try again.'));
        }
        $radioUnitBands = $this->RadioUnitTypes->RadioUnitBands->find('list', ['order' => 'name']);
        $manufacturers = $this->RadioUnitTypes->Manufacturers->find('list', ['order' => 'name']);
        $this->set(compact('radioUnitType', 'radioUnitBands', 'manufacturers'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $radioUnitType = $this->RadioUnitTypes->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $radioUnitType = $this->RadioUnitTypes->patchEntity($radioUnitType, $this->request->getData());
            if ($this->RadioUnitTypes->save($radioUnitType)) {
                $this->Flash->success(__('The radio unit type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit type could not be saved. Please, try again.'));
        }
        $radioUnitBands = $this->RadioUnitTypes->RadioUnitBands->find('list', ['order' => 'name']);
        $manufacturers = $this->RadioUnitTypes->Manufacturers->find('list', ['order' => 'name']);
        $this->set(compact('radioUnitType', 'radioUnitBands', 'manufacturers'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Unit Type id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $radioUnitType = $this->RadioUnitTypes->get($id);
        if ($this->RadioUnitTypes->delete($radioUnitType)) {
            $this->Flash->success(__('The radio unit type has been deleted.'));
        } else {
            $this->Flash->error(__('The radio unit type could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
