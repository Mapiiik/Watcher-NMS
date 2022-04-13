<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;

/**
 * IpAddressRanges Controller
 *
 * @property \App\Model\Table\IpAddressRangesTable $IpAddressRanges
 * @method \App\Model\Entity\IpAddressRange[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class IpAddressRangesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['AccessPoints', 'ParentIpAddressRanges'],
        ];
        $ipAddressRanges = $this->paginate($this->IpAddressRanges);

        $this->set(compact('ipAddressRanges'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRanges']);
    }

    /**
     * View method
     *
     * @param string|null $id Ip Address Range id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ipAddressRange = $this->IpAddressRanges->get($id, [
            'contain' => ['AccessPoints', 'ParentIpAddressRanges'],
        ]);

        $this->set(compact('ipAddressRange'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange']);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->request->allowMethod(['post', 'put']);
        $ipAddressRange = $this->IpAddressRanges->newEntity($this->request->getData());
        if ($this->IpAddressRanges->save($ipAddressRange)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'accessPoint' => $ipAddressRange,
        ]);
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange', 'message']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Ip Address Range id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->request->allowMethod(['patch', 'post', 'put']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->request->getData());
        if ($this->IpAddressRanges->save($ipAddressRange)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'accessPoint' => $ipAddressRange,
        ]);
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange', 'message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Ip Address Range id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['delete']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        if ($this->IpAddressRanges->delete($ipAddressRange)) {
            $message = 'Deleted';
        } else {
            $message = 'Error';
        }
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
