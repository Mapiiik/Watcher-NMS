<?php
declare(strict_types=1);

namespace App\Controller;

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
        // filter
        $conditions = [];
        $finder = [];
        $radio_unit_band_id = $this->getRequest()->getQuery('radio_unit_band_id');
        if (!empty($radio_unit_band_id)) {
            $finder['band'] = [
                'radio_unit_band_id' => $radio_unit_band_id,
            ];
        }

        // search
        $search = $this->getRequest()->getQuery('search');
        if (!empty($search)) {
            $conditions[] = [
                'OR' => [
                    'RadioLinks.name ILIKE' => '%' . trim($search) . '%',
                ],
            ];
        }

        $this->paginate = [
            'contain' => ['RadioUnits' => [
                'RadioUnitTypes' => ['RadioUnitBands'],
                'AccessPoints',
                'RadioLinks',
                'AntennaTypes',
            ]],
            'order' => ['name' => 'ASC'],
            'conditions' => $conditions,
            'finder' => $finder,
        ];

        $radioLinks = $this->paginate($this->RadioLinks);

        $radioUnitBands = $this->RadioLinks
            ->RadioUnits->RadioUnitTypes->RadioUnitBands->find('list', ['order' => 'name']);

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
            'contain' => [
                'RadioUnits' => ['RadioUnitTypes', 'AccessPoints', 'AntennaTypes'],
                'Creators',
                'Modifiers',
            ],
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
        if ($this->getRequest()->is('post')) {
            $radioLink = $this->RadioLinks->patchEntity($radioLink, $this->getRequest()->getData());
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
        if ($this->getRequest()->is(['patch', 'post', 'put'])) {
            $radioLink = $this->RadioLinks->patchEntity($radioLink, $this->getRequest()->getData());
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
        $this->getRequest()->allowMethod(['post', 'delete']);
        $radioLink = $this->RadioLinks->get($id);
        if ($this->RadioLinks->delete($radioLink)) {
            $this->Flash->success(__('The radio link has been deleted.'));
        } else {
            $this->Flash->error(__('The radio link could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
