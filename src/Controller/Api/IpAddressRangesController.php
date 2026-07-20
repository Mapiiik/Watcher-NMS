<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\View\JsonView;
use Override;

/**
 * IpAddressRanges Controller
 *
 * @property \App\Model\Table\IpAddressRangesTable $IpAddressRanges
 */
class IpAddressRangesController extends AppController
{
    /**
     * Returns supported output types
     */
    #[Override]
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index(): void
    {
        $ipAddressRanges = $this->IpAddressRanges->find(
            'all',
            contain: [
                'AccessPoints',
                'ParentIpAddressRanges',
            ],
        )->all();

        $this->set(compact('ipAddressRanges'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRanges']);
    }

    /**
     * Search method
     *
     * @property \App\Model\Table\IpAddressRangesTable $IpAddressRanges
     * @return void Renders view
     */
    public function search(): void
    {
        // search
        $conditions = [];

        if ($this->getRequest()->getQuery('access_point_id') !== null) {
            $conditions[] = [
                'OR' => [
                    'IpAddressRanges.access_point_id IS NULL',
                    'IpAddressRanges.access_point_id' => $this->getRequest()->getQuery('access_point_id'),
                ],
            ];
        }
        if ($this->getRequest()->getQuery('for_subnets') !== null) {
            $conditions[] = [
                'IpAddressRanges.for_subnets' => $this->getRequest()->getQuery('for_subnets'),
            ];
        }
        if ($this->getRequest()->getQuery('for_customer_addresses_set_via_radius') !== null) {
            $conditions[] = [
                'IpAddressRanges.for_customer_addresses_set_via_radius' =>
                    $this->getRequest()->getQuery('for_customer_addresses_set_via_radius'),
            ];
        }
        if ($this->getRequest()->getQuery('for_customer_addresses_set_manually') !== null) {
            $conditions[] = [
                'IpAddressRanges.for_customer_addresses_set_manually' =>
                    $this->getRequest()->getQuery('for_customer_addresses_set_manually'),
            ];
        }
        if ($this->getRequest()->getQuery('for_technology_addresses_set_manually') !== null) {
            $conditions[] = [
                'IpAddressRanges.for_technology_addresses_set_manually' =>
                    $this->getRequest()->getQuery('for_technology_addresses_set_manually'),
            ];
        }
        if ($this->getRequest()->getQuery('for_customer_networks_set_via_radius') !== null) {
            $conditions[] = [
                'IpAddressRanges.for_customer_networks_set_via_radius' =>
                    $this->getRequest()->getQuery('for_customer_networks_set_via_radius'),
            ];
        }
        if ($this->getRequest()->getQuery('for_customer_networks_set_manually') !== null) {
            $conditions[] = [
                'IpAddressRanges.for_customer_networks_set_manually' =>
                    $this->getRequest()->getQuery('for_customer_networks_set_manually'),
            ];
        }
        if ($this->getRequest()->getQuery('for_technology_networks_set_manually') !== null) {
            $conditions[] = [
                'IpAddressRanges.for_technology_networks_set_manually' =>
                    $this->getRequest()->getQuery('for_technology_networks_set_manually'),
            ];
        }
        if ($this->getRequest()->getQuery('ip_address') !== null) {
            $conditions[] = [
                'IpAddressRanges.ip_network >>=' => $this->getRequest()->getQuery('ip_address'),
            ];
        }

        $ipAddressRanges = $this->IpAddressRanges->find(
            'all',
            contain: [
                'AccessPoints',
                'ParentIpAddressRanges',
            ],
            conditions: $conditions,
            order: [
                'masklen(IpAddressRanges.ip_network)' => 'DESC',
            ],
        )->all();

        $this->set(compact('ipAddressRanges'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRanges']);
    }

    /**
     * View method
     *
     * @param string|null $id IP Address Range id.
     * @return void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $ipAddressRange = $this->IpAddressRanges->get($id, contain: ['AccessPoints', 'ParentIpAddressRanges']);

        $this->set(compact('ipAddressRange'));
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add(): void
    {
        $this->getRequest()->allowMethod(['post', 'put']);
        $ipAddressRange = $this->IpAddressRanges->newEntity($this->getRequest()->getData());
        $message = $this->IpAddressRanges->save($ipAddressRange) ? 'Saved' : 'Error';
        $this->set([
            'message' => $message,
            'accessPoint' => $ipAddressRange,
        ]);
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange', 'message']);
    }

    /**
     * Edit method
     *
     * @param string|null $id IP Address Range id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['patch', 'post', 'put']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        $ipAddressRange = $this->IpAddressRanges->patchEntity($ipAddressRange, $this->getRequest()->getData());

        $message = $this->IpAddressRanges->save($ipAddressRange) ? 'Saved' : 'Error';
        $this->set([
            'message' => $message,
            'accessPoint' => $ipAddressRange,
        ]);
        $this->viewBuilder()->setOption('serialize', ['ipAddressRange', 'message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id IP Address Range id.
     * @return void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): void
    {
        $this->getRequest()->allowMethod(['delete']);
        $ipAddressRange = $this->IpAddressRanges->get($id);
        $message = $this->IpAddressRanges->delete($ipAddressRange) ? 'Deleted' : 'Error';
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
    }
}
