<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * RadarInterferences Controller
 *
 * @property \App\Model\Table\RadarInterferencesTable $RadarInterferences
 *
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
            'contain' => [],
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
}
