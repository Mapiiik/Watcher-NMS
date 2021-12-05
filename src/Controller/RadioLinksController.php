<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\SearchForm;

/**
 * RadioLinks Controller
 *
 * @property \App\Model\Table\RadioLinksTable $RadioLinks
 * @method \App\Model\Entity\RadioLink[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RadioLinksController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['RadioUnits' => [
                'RadioUnitTypes' => ['RadioUnitBands'],
                'AccessPoints',
                'RadioLinks',
                'AntennaTypes',
            ]],
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
                'RadioLinks.name ILIKE' => '%' . \trim($search->getData('search')) . '%',
            ];
        }

        if ($this->request->getQuery('band') <> '') {
            $this->paginate['finder'] = [
                'band' => [
                    'radio_unit_band_id' => $this->request->getQuery('band'),
                ],
            ];
        }

        $radioLinks = $this->paginate($this->RadioLinks);

        $radioUnitBands = $this->RadioLinks->RadioUnits->RadioUnitTypes->RadioUnitBands->find('list', ['order' => 'name']);

        $this->set(compact('radioLinks', 'radioUnitBands'));
    }

    /**
     * View method
     *
     * @param string|null $id Radio Link id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $radioLink = $this->RadioLinks->get($id, [
            'contain' => ['RadioUnits' => ['RadioUnitTypes', 'AccessPoints', 'AntennaTypes']],
        ]);

        $this->set('radioLink', $radioLink);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $radioLink = $this->RadioLinks->newEmptyEntity();
        if ($this->request->is('post')) {
            $radioLink = $this->RadioLinks->patchEntity($radioLink, $this->request->getData());
            if ($this->RadioLinks->save($radioLink)) {
                $this->Flash->success(__('The radio link has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio link could not be saved. Please, try again.'));
        }
        $this->set(compact('radioLink'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Radio Link id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $radioLink = $this->RadioLinks->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $radioLink = $this->RadioLinks->patchEntity($radioLink, $this->request->getData());
            if ($this->RadioLinks->save($radioLink)) {
                $this->Flash->success(__('The radio link has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The radio link could not be saved. Please, try again.'));
        }
        $this->set(compact('radioLink'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Radio Link id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $radioLink = $this->RadioLinks->get($id);
        if ($this->RadioLinks->delete($radioLink)) {
            $this->Flash->success(__('The radio link has been deleted.'));
        } else {
            $this->Flash->error(__('The radio link could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
