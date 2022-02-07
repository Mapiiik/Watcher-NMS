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

$cakeDescription = 'Watcher NMS | ' . env('APP_COMPANY', 'ISP');
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

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'cake']) ?>
    
    <?= $this->Html->script(['https://code.jquery.com/jquery.min.js', 'links.js']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-title">
            <a href="<?= $this->Url->build('/') ?>"><span>Watcher</span> NMS</a>
        </div>

        <?php if (!($this->request->getQuery('win-link') == 'true')) : ?>
        <div class="top-nav-links">
            <?php
            $controller = $this->name;
            $action = $this->request->getParam('action');
            $buttonSelected = function ($haystack = []) use ($controller, $action) {
                if (in_array($controller, $haystack)) {
                    return ' button-selected';
                } elseif (in_array($action, $haystack)) {
                    return ' button-selected';
                } else {
                    return '';
                }
            };

            $request = $this->request;
            $urlWithQuery = function ($query = []) use ($request) {
                return $this->Url->build(
                    ['?' => $query + $this->request->getQueryParams()] + $this->request->getParam('pass')
                );
            }; ?>

            <?= $this->AuthLink->link(
                __('Access Points'),
                ['controller' => 'AccessPoints', 'action' => 'index', 'plugin' => null],
                ['class' => 'button' . $buttonSelected([
                    'AccessPoints',
                    'AccessPointContacts',
                    'ElectricityMeterReadings',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Customer Points'),
                ['controller' => 'CustomerPoints', 'action' => 'index', 'plugin' => null],
                ['class' => 'button' . $buttonSelected([
                    'CustomerPoints',
                    'CustomerConnections',
                    'CustomerConnectionIps',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('RouterOS Devices'),
                ['controller' => 'RouterosDevices', 'action' => 'index', 'plugin' => null],
                ['class' => 'button' . $buttonSelected([
                    'RouterosDevices',
                    'RouterosDeviceIps',
                    'RouterosDeviceInterfaces',
                    'DeviceTypes',
                    'RadarInterferences',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Radio Links'),
                ['controller' => 'RadioLinks', 'action' => 'index', 'plugin' => null],
                ['class' => 'button' . $buttonSelected([
                    'RadioLinks',
                    'RadioUnits',
                    'RadioUnitTypes',
                    'RadioUnitBands',
                    'AntennaTypes',
                ])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Power Supplies'),
                ['controller' => 'PowerSupplies', 'action' => 'index', 'plugin' => null],
                ['class' => 'button' . $buttonSelected(['PowerSupplies', 'PowerSupplyTypes'])]
            ) ?>
            <?= $this->AuthLink->link(
                __('Users'),
                ['controller' => 'Users', 'action' => 'profile', 'plugin' => 'CakeDC/Users'],
                ['class' => 'button' . $buttonSelected(['Users', 'Profile'])]
            ) ?>

            <?php if ($this->request->getParam('action') == 'index') : ?>
            <select name="limit" class="button button-outline" onchange="location = this.value;">
                <option <?= $this->request->getQuery('limit') == 20 ? 'selected="selected"' : '' ?>
                    value="<?= $urlWithQuery(['limit' => 20]) ?>">20</option>
                <option <?= $this->request->getQuery('limit') == 50 ? 'selected="selected"' : '' ?>
                    value="<?= $urlWithQuery(['limit' => 50]) ?>">50</option>
                <option <?= $this->request->getQuery('limit') == 100 ? 'selected="selected"' : '' ?>
                    value="<?= $urlWithQuery(['limit' => 100]) ?>">100</option>
                <option <?= $this->request->getQuery('limit') == 500 ? 'selected="selected"' : '' ?>
                    value="<?= $urlWithQuery(['limit' => 500]) ?>">500</option>
                <option <?= $this->request->getQuery('limit') == 1000 ? 'selected="selected"' : '' ?>
                    value="<?= $urlWithQuery(['limit' => 1000]) ?>">1000</option>
            </select>
            <?php endif; ?>
            
            <?php $language = $this->request
                ->getSession()->read('Config.language', Cake\I18n\I18n::getDefaultLocale());
            ?>
            <select name="language" class="button button-outline" onchange="location = this.value;">
                <option <?= $language == 'cs_CZ' ? 'selected="selected"' : '' ?>
                    value="<?= $urlWithQuery(['language' => 'cs_CZ']) ?>">Čeština</option>
                <option <?= $language == 'en_US' ? 'selected="selected"' : '' ?>
                    value="<?= $urlWithQuery(['language' => 'en_US']) ?>">English</option>
            </select>

            <?= !is_null($this->request->getSession()->read('Auth.id')) ? $this->AuthLink->link(
                __('Logout'),
                ['controller' => 'Users', 'action' => 'logout', 'plugin' => 'CakeDC/Users'],
                ['class' => 'button button-outline']
            ) : '' ?>

            <br />
            <?php if (in_array($this->name, ['AccessPoints', 'AccessPointContacts', 'ElectricityMeterReadings'])) : ?>
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
            <?php endif; ?>
            <?php if (in_array($this->name, ['CustomerPoints', 'CustomerConnections', 'CustomerConnectionIps'])) : ?>
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
                in_array($this->name, [
                    'RouterosDevices',
                    'RouterosDeviceIps',
                    'RouterosDeviceInterfaces',
                    'DeviceTypes',
                    'RadarInterferences',
                ])
            ) : ?>
                <?= $this->AuthLink->link(
                    __('Routeros Devices'),
                    ['controller' => 'RouterosDevices', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['RouterosDevices'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Routeros Device Interfaces'),
                    ['controller' => 'RouterosDeviceInterfaces', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['RouterosDeviceInterfaces'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('RouterOS Device Ips'),
                    ['controller' => 'RouterosDeviceIps', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['RouterosDeviceIps'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Device Types'),
                    ['controller' => 'DeviceTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['DeviceTypes'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Radar Interferences'),
                    ['controller' => 'RadarInterferences', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['RadarInterferences'])]
                ) ?>
            <?php endif; ?>
            <?php
            if (
                in_array($this->name, [
                    'RadioLinks',
                    'RadioUnits',
                    'RadioUnitTypes',
                    'RadioUnitBands',
                    'AntennaTypes',
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
                <?= $this->AuthLink->link(
                    __('Radio Unit Types'),
                    ['controller' => 'RadioUnitTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['RadioUnitTypes'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Radio Unit Bands'),
                    ['controller' => 'RadioUnitBands', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['RadioUnitBands'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Antenna Types'),
                    ['controller' => 'AntennaTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['AntennaTypes'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Manufacturers'),
                    ['controller' => 'Manufacturers', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['Manufacturers'])]
                ) ?>
            <?php endif; ?>
            <?php if (in_array($this->name, ['PowerSupplies', 'PowerSupplyTypes'])) : ?>
                <?= $this->AuthLink->link(
                    __('Power Supplies'),
                    ['controller' => 'PowerSupplies', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['PowerSupplies'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Power Supply Types'),
                    ['controller' => 'PowerSupplyTypes', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['PowerSupplyTypes'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Manufacturers'),
                    ['controller' => 'Manufacturers', 'action' => 'index', 'plugin' => null],
                    ['class' => 'button button-small' . $buttonSelected(['Manufacturers'])]
                ) ?>
            <?php endif; ?>
            <?php if (in_array($this->name, ['Users'])) : ?>
                <?= $this->AuthLink->link(
                    __('Profile'),
                    ['controller' => 'Users', 'action' => 'profile', 'plugin' => 'CakeDC/Users'],
                    ['class' => 'button button-small' . $buttonSelected(['profile'])]
                ) ?>
                <?= $this->AuthLink->link(
                    __('Index'),
                    ['controller' => 'Users', 'action' => 'index', 'plugin' => 'CakeDC/Users'],
                    ['class' => 'button button-small' . $buttonSelected(['index'])]
                ) ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </nav>

    <?= $this->request->getParam('access_point_id') ? $this->cell('AccessPoint') : ''; ?>

    <main class="main">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <footer>
    </footer>
</body>
</html>
