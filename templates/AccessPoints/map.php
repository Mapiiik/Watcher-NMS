<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AccessPoint> $accessPoints
 * @var \App\Form\MapOptionsForm $mapOptions
 * @var \Cake\Collection\CollectionInterface<string, string>|array<string> $accessPointsFilter
 * @var \Cake\Collection\CollectionInterface<string, string>|array<string> $routerosDevicesFilter
 * @var array<string, \App\Maps\Marker> $mapMarkers
 * @var array<string, \App\Maps\Polyline> $mapPolylines
 */
?>
<div class="accessPoints map content">
    <?= $this->AuthLink->link(__('New Access Point'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <?= $this->AuthLink->link(__('List Access Points'), ['action' => 'index'], ['class' => 'button float-right']) ?>
    <h3><?= __('Access Points') ?></h3>
<?= $this->element('Maps/overview', [
    'mapMarkers' => $mapMarkers,
    'mapPolylines' => $mapPolylines,
    'mapHeight' => '600px',
]) ?>
    <div class="column column-90">
        <div class="accessPoints form content">
            <?= $this->Form->create($mapOptions) ?>
            <fieldset>
                <legend><?= __('Map Options') ?></legend>
                <?php
                echo $this->Form->control('routeros_ip_links', [
                    'label' => __('RouterOS IP Links'),
                ]);
                echo $this->Form->control('routeros_wireless_links', [
                    'label' => __('RouterOS Wireless Links'),
                ]);
                echo $this->Form->control('linked_customers', [
                    'label' => __('Linked Customers'),
                ]);
                echo $this->Form->control('access_point_id', [
                    'options' => $accessPointsFilter,
                    'empty' => true,
                ]);
                echo $this->Form->control('routeros_device_id', [
                    'label' => __('RouterOS Device'),
                    'options' => $routerosDevicesFilter,
                    'empty' => true,
                ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>    
</div>
