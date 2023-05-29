<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

use App\Controller\AppController;
use Cake\Core\Configure;

$cakeDescription = 'Watcher NMS | ' . env('APP_COMPANY', 'ISP');
/** @psalm-scope-this App\View\AppView */
$request = $this->getRequest();
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?> |
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->script(['https://code.jquery.com/jquery.min.js', 'links.js']) ?>

    <?php if (filter_var(env('ENABLE_SELECT2', false), FILTER_VALIDATE_BOOLEAN)) : ?>
        <?= $this->Html->css(['https://cdn.jsdelivr.net/npm/select2@4.0/dist/css/select2.min.css']) ?>
        <?= $this->Html->script([
            'https://cdn.jsdelivr.net/npm/select2@4.0/dist/js/select2.min.js',
            'select2-settings.js',
        ]) ?>
    <?php endif ?>

    <?php
    switch (Configure::read('UI.theme')) {
        case 'legacy':
            echo $this->Html->css(['normalize.min', 'legacy']);
            break;
        case 'dark':
            echo $this->Html->css(['normalize.min', 'milligram.min', 'cake', 'dark']);
            break;
        case 'contrast':
            echo $this->Html->css(['normalize.min', 'milligram.min', 'cake', 'high_contrast']);
            break;
        default:
            echo $this->Html->css(['normalize.min', 'milligram.min', 'cake']);
    }
    ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Watcher</span> NMS</a>
        </div>

        <?php if (!($request->getQuery('win-link') == 'true')) : ?>
        <div class="top-nav-links">
            <?php
            $controller = $this->getName();
            $action = $request->getParam('action');
            $buttonSelected = function ($haystack = []) use ($controller, $action) {
                if (in_array($controller, $haystack)) {
                    return ' button-selected';
                } elseif (in_array($action, $haystack)) {
                    return ' button-selected';
                } else {
                    return '';
                }
            };

            $urlWithQuery = function ($query = []) use ($request) {
                return $this->Url->build(
                    ['?' => $query + $request->getQueryParams()] + $request->getParam('pass')
                );
            }; ?>

            <?= $this->AuthLink->link(
                __('Access Points'),
                ['controller' => 'AccessPoints', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected([
                    'AccessPoints',
                    'AccessPointContacts',
                    'ElectricityMeterReadings',
                    'LandlordPayments',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Customer Points'),
                ['controller' => 'CustomerPoints', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected([
                    'CustomerPoints',
                    'CustomerConnections',
                    'CustomerConnectionIps',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('IP Address Ranges'),
                ['controller' => 'IpAddressRanges', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected([
                    'IpAddressRanges',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('RouterOS Devices'),
                ['controller' => 'RouterosDevices', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected([
                    'RouterosDevices',
                    'RouterosDeviceIps',
                    'RouterosDeviceInterfaces',
                    'RadarInterferences',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Radio Links'),
                ['controller' => 'RadioLinks', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected([
                    'RadioLinks',
                    'RadioUnits',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Power Supplies'),
                ['controller' => 'PowerSupplies', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected([
                    'PowerSupplies',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Settings'),
                ['controller' => 'Settings', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected([
                    'AccessPointTypes',
                    'AntennaTypes',
                    'DeviceTypes',
                    'Manufacturers',
                    'PaymentPurposes',
                    'PowerSupplyTypes',
                    'RadioUnitTypes',
                    'RadioUnitBands',
                    'Settings',
                    'Users',
                ])]
            ) ?>

            <?= env('WATCHER_CRM_URL') ?
                $this->Html->link(
                    __('Customer Relationship Management'),
                    env('WATCHER_CRM_URL'),
                    ['class' => 'button button-small']
                ) : '' ?>

            <?= $request->getParam('action') == 'index' ? $this->Form->select(
                'limit',
                [
                    $urlWithQuery(['limit' => 20]) => 20,
                    $urlWithQuery(['limit' => 50]) => 50,
                    $urlWithQuery(['limit' => 100]) => 100,
                    $urlWithQuery(['limit' => 500]) => 500,
                    $urlWithQuery(['limit' => 1000]) => 1000,
                    $urlWithQuery(['limit' => 5000]) => 5000,
                    $urlWithQuery(['limit' => 10000]) => 10000,
                ],
                [
                    'value' => $urlWithQuery(['limit' => Configure::read('UI.number_of_rows_per_page')]),
                    'escape' => false,
                    'onchange' => 'location = this.value;',
                    'class' => 'button button-small button-outline',
                ]
            ) : '' ?>

            <?= $this->Form->select(
                'theme',
                [
                    $urlWithQuery(['theme' => 'default']) => __('Default'),
                    $urlWithQuery(['theme' => 'contrast']) => __('Contrast'),
                    $urlWithQuery(['theme' => 'legacy']) => __('Legacy'),
                    $urlWithQuery(['theme' => 'dark']) => __('Dark') . ' (dev)',
                ],
                [
                    'value' => $urlWithQuery(['theme' => Configure::read('UI.theme')]),
                    'escape' => false,
                    'onchange' => 'location = this.value;',
                    'class' => 'button button-small button-outline',
                ]
            ) ?>

            <?= $this->Form->select(
                'language',
                [
                    $urlWithQuery(['language' => 'cs_CZ']) => 'Čeština',
                    $urlWithQuery(['language' => 'en_US']) => 'English',
                ],
                [
                    'value' => $urlWithQuery(['language' => Configure::read('UI.language')]),
                    'escape' => false,
                    'onchange' => 'location = this.value;',
                    'class' => 'button button-small button-outline',
                ]
            ) ?>

            <?= $request->getAttribute('identity') != null ? $this->AuthLink->link(
                __('Logout'),
                ['controller' => 'AppUsers', 'action' => 'logout', 'plugin' => null],
                ['class' => 'button button-small button-outline']
            ) : '' ?>
        </div>
        <?php endif; ?>
    </nav>

    <?php if (!($request->getQuery('win-link') == 'true')) : ?>
    <div class="container nav-container to-right">
        <?php
        if (
            in_array($this->getName(), [
                'AccessPoints',
                'AccessPointContacts',
                'ElectricityMeterReadings',
                'LandlordPayments',
                ])
        ) : ?>
            <?= $this->AuthLink->link(
                __('Access Points'),
                ['controller' => 'AccessPoints', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['AccessPoints'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Access Point Contacts'),
                ['controller' => 'AccessPointContacts', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['AccessPointContacts'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Electricity Meter Readings'),
                ['controller' => 'ElectricityMeterReadings', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['ElectricityMeterReadings'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Landlord Payments'),
                ['controller' => 'LandlordPayments', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['LandlordPayments'])]
            ) ?>
        <?php endif; ?>
        <?php
        if (
            in_array($this->getName(), [
                'CustomerPoints',
                'CustomerConnections',
                'CustomerConnectionIps',
            ])
        ) : ?>
            <?= $this->AuthLink->link(
                __('Customer Points'),
                ['controller' => 'CustomerPoints', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['CustomerPoints'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Customer Connections'),
                ['controller' => 'CustomerConnections', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['CustomerConnections'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Customer Connection Ips'),
                ['controller' => 'CustomerConnectionIps', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['CustomerConnectionIps'])]
            ) ?>
        <?php endif; ?>
        <?php
        if (
            in_array($this->getName(), [
                'RouterosDevices',
                'RouterosDeviceIps',
                'RouterosDeviceInterfaces',
                'RadarInterferences',
            ])
        ) : ?>
            <?= $this->AuthLink->link(
                __('RouterOS Devices'),
                ['controller' => 'RouterosDevices', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['RouterosDevices'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('RouterOS Device Interfaces'),
                ['controller' => 'RouterosDeviceInterfaces', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['RouterosDeviceInterfaces'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('RouterOS Device Ips'),
                ['controller' => 'RouterosDeviceIps', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['RouterosDeviceIps'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Radar Interferences'),
                ['controller' => 'RadarInterferences', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['RadarInterferences'])]
            ) ?>
        <?php endif; ?>
        <?php
        if (
            in_array($this->getName(), [
                'RadioLinks',
                'RadioUnits',
            ])
        ) : ?>
            <?= $this->AuthLink->link(
                __('Radio Links'),
                ['controller' => 'RadioLinks', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['RadioLinks'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Radio Units'),
                ['controller' => 'RadioUnits', 'action' => 'index', 'plugin' => null],
                ['class' => 'button button-small' . $buttonSelected(['RadioUnits'])]
            ) ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?= $request->getParam('access_point_id') ? $this->cell('AccessPoint') : ''; ?>

    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
        <br>
        <div class="container">
            <div class="float-right" title="<?= __('Changelog') . ': ' . PHP_EOL . h(AppController::getChangelog()) ?>">
                <?= __('Version') . ': ' . h(AppController::getVersion()) ?>
            </div>
            <br><br>
        </div>
    </footer>
</body>
</html>
