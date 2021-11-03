<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RadioUnitBands Controller
 *
 * @property \App\Model\Table\RadioUnitBandsTable $RadioUnitBands
 * @method \App\Model\Entity\RadioUnitBand[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadioUnitBandsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $radioUnitBands = $this->paginate($this->RadioUnitBands);

        $this->set(compact('radioUnitBands'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Unit Band id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $radioUnitBand = $this->RadioUnitBands->get($id, [
            'contain' => ['AntennaTypes' => ['Manufacturers'], 'RadioUnitTypes' => ['Manufacturers']],
        ]);

        $this->set('radioUnitBand', $radioUnitBand);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $radioUnitBand = $this->RadioUnitBands->newEmptyEntity();
        if ($this->request->is('post')) {
            $radioUnitBand = $this->RadioUnitBands->patchEntity($radioUnitBand, $this->request->getData());
            if ($this->RadioUnitBands->save($radioUnitBand)) {
                $this->Flash->success(__('The radio unit band has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit band could not be saved. Please, try again.'));
        }
        $this->set(compact('radioUnitBand'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Unit Band id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $radioUnitBand = $this->RadioUnitBands->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $radioUnitBand = $this->RadioUnitBands->patchEntity($radioUnitBand, $this->request->getData());
            if ($this->RadioUnitBands->save($radioUnitBand)) {
                $this->Flash->success(__('The radio unit band has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio unit band could not be saved. Please, try again.'));
        }
        $this->set(compact('radioUnitBand'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Unit Band id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $radioUnitBand = $this->RadioUnitBands->get($id);
        if ($this->RadioUnitBands->delete($radioUnitBand)) {
            $this->Flash->success(__('The radio unit band has been deleted.'));
        } else {
            $this->Flash->error(__('The radio unit band could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
