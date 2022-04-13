<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\IpAddressRange $ipAddressRange
 * @var \Cake\Collection\CollectionInterface|string[] $accessPoints
 * @var \Cake\Collection\CollectionInterface|string[] $parentIpAddressRanges
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Ip Address Ranges'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-90">
        <div class="ipAddressRanges form content">
            <?= $this->Form->create($ipAddressRange) ?>
            <fieldset>
                <legend><?= __('Add Ip Address Range') ?></legend>
                <?php
                echo $this->Form->control('name');
                echo $this->Form->control('ip_network');
                echo $this->Form->control('ip_gateway');
                if (!isset($access_point_id)) {
                    echo $this->Form->control('access_point_id', [
                        'options' => $accessPoints,
                        'empty' => true,
                    ]);
                }
                echo $this->Form->control('parent_ip_address_range_id', [
                    'options' => $parentIpAddressRanges,
                    'empty' => true,
                ]);
                echo $this->Form->control('note');
                echo $this->Form->control('for_subnets');
                ?>
                <div class="row">
                    <div class="column-responsive">
                        <?php
                        echo $this->Form->control('for_customer_addresses_set_via_radius');
                        echo $this->Form->control('for_customer_addresses_set_manually');
                        echo $this->Form->control('for_technology_addresses_set_manually');
                        ?>
                    </div>
                    <div class="column-responsive">
                        <?php
                        echo $this->Form->control('for_customer_networks_set_via_radius');
                        echo $this->Form->control('for_customer_networks_set_manually');
                        echo $this->Form->control('for_technology_networks_set_manually');
                        ?>
                    </div>
                </div>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
