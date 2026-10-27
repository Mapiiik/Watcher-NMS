<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;
use Files\Service\Documentations;

/*
 * This is a quick roles-permissions implementation
 * Rules are evaluated top-down, first matching rule will apply
 * Each line define
 *      [
 *          'role' => 'role' | ['roles'] | '*'
 *          'prefix' => 'Prefix' | , (default = null)
 *          'plugin' => 'Plugin' | , (default = null)
 *          'controller' => 'Controller' | ['Controllers'] | '*',
 *          'action' => 'action' | ['actions'] | '*',
 *          'allowed' => true | false | callback (default = true)
 *      ]
 * You could use '*' to match anything
 * 'allowed' will be considered true if not defined. It allows a callable to manage complex
 * permissions, like this
 * 'allowed' => function (array $user, $role, Request $request) {}
 *
 * Example, using allowed callable to define permissions only for the owner of the Posts to edit/delete
 *
 * (remember to add the 'uses' at the top of the permissions.php file for Hash, TableRegistry and Request
   [
        'role' => ['user'],
        'controller' => ['Posts'],
        'action' => ['edit', 'delete'],
        'allowed' => function(array $user, $role, Request $request) {
            $postId = Hash::get($request->params, 'pass.0');
            $post = TableRegistry::getTableLocator()->get('Posts')->get($postId);
            $userId = Hash::get($user, 'id');
            if (!empty($post->user_id) && !empty($userId)) {
                return $post->user_id === $userId;
            }
            return false;
        }
    ],
 */

/*
 * The people working in the application. The machine roles talk to it through the Api prefix and
 * are let in by rules of their own, so the ordinary pages are not theirs - not even the ones open
 * to everybody else.
 */
$staff = [
    'user',
    'customer-service-technician',
    'network-technician',
    'network-manager',
    'sales-representative',
    'sales-manager',
    'bookkeeper',
    'manager',
];

/*
 * Default permissions
 */
$permissions = [
    'CakeDC/Auth.permissions' => [
        //all bypass
        [
            'prefix' => false,
            'plugin' => null,
            'controller' => 'Users',
            'action' => [
                // signing in and out
                'login',
                'socialLogin',
                'verify',
                'logout',
                // a new password for somebody who has forgotten theirs
                'requestResetPassword',
                'resetPassword',
            ],
            'bypassAuth' => true,
        ],
        // Watcher Agent provisioning access (custom authentication)
        [
            'prefix' => 'Api/Agent',
            'plugin' => null,
            'controller' => 'Provision',
            'action' => [
                'routeros',
            ],
            'bypassAuth' => true,
        ],
        //admin role allowed to all the things
        [
            'role' => 'admin',
            'prefix' => '*',
            'extension' => '*',
            'plugin' => '*',
            'controller' => '*',
            'action' => '*',
        ],
        //specific actions allowed for all the staff in Users plugin
        [
            'role' => $staff,
            'plugin' => null,
            'controller' => 'Users',
            'action' => [
                'profile',
                'changePassword',
                'logout',
                'linkSocial',
                'callbackLinkSocial',
                'unlinkSocial',
                'userSettings',
                // everybody looks after their own one-time codes; taking them away from an account
                // (resetOneTimePasswordAuthenticator) is left to the admins
                'oneTimePassword',
                'disableOneTimePassword',
                'newRecoveryCodes',
                'recoveryCodes',
            ],
        ],
        //the root is where everybody arrives and is only ever a redirect, so no role may be
        //stopped at it - the page it then sends them to is what gets checked, there
        [
            'role' => '*',
            'prefix' => false,
            'plugin' => null,
            'controller' => 'Home',
            'action' => 'index',
        ],
        //the dashboard is the landing page, so every role has to get through it -
        //which cards it then draws is decided per role by the card registry
        [
            'role' => '*',
            'prefix' => false,
            'plugin' => null,
            'controller' => 'Dashboard',
            'action' => [
                'cards',
                'card',
            ],
        ],
        //the maps ask this for addresses, so every role that is shown a map needs it
        [
            'role' => $staff,
            'prefix' => 'Api',
            'plugin' => null,
            'controller' => [
                'GeocoderBridge',
            ],
            'action' => [
                'search',
            ],
        ],
        //always allow access to DebugKit
        [
            'role' => '*',
            'plugin' => 'DebugKit',
            'controller' => '*',
            'action' => '*',
            'bypassAuth' => true,
        ],
        //API access
        [
            'role' => [
                'api',
            ],
            'prefix' => 'Api',
            'plugin' => null,
            'controller' => [
                'AccessPoints',
                'IpAddressRanges',
                'PowerOutages',
                'RouterosDevices',
            ],
            'action' => [
                'index',
                'view',
                'search',
            ],
        ],
        //what all the staff may look at
        [
            'role' => $staff,
            'plugin' => null,
            'controller' => [
                'Settings',
            ],
            'action' => [
                'index',
            ],
        ],
        [
            'role' => $staff,
            'plugin' => null,
            'controller' => [
                'AccessPoints',
            ],
            'action' => [
                'index',
                'view',
                'map',
                'utilization',
            ],
        ],
        [
            'role' => $staff,
            'plugin' => null,
            'controller' => [
                'AccessPointContacts',
                'ElectricityMeterReadings',
                'IpAddressRanges',
            ],
            'action' => [
                'index',
                'view',
            ],
        ],
        [
            'role' => $staff,
            'plugin' => null,
            'controller' => [
                'RouterosDevices',
                'RouterosDeviceIps',
                'RouterosDeviceInterfaces',
                'DeviceTypes',
            ],
            'action' => [
                'index',
                'view',
            ],
        ],
        [
            'role' => $staff,
            'plugin' => null,
            'controller' => [
                'CustomerConnections',
                'CustomerPoints',
                'CustomerConnectionIps',
            ],
            'action' => [
                'view',
            ],
        ],
        //documentation: the staff who may look at a record may look at what is kept about it
        [
            'role' => [
                'customer-service-technician',
                'network-technician',
                'network-manager',
                'sales-representative',
                'sales-manager',
                'bookkeeper',
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'Documentations',
            ],
            'action' => [
                'index',
                'view',
            ],
        ],
        //and fetch back the files kept in it - those only, should anything else ever be filed
        [
            'role' => [
                'customer-service-technician',
                'network-technician',
                'network-manager',
                'sales-representative',
                'sales-manager',
                'bookkeeper',
                'manager',
            ],
            'plugin' => 'Files',
            'controller' => [
                'FileLinks',
            ],
            'action' => [
                'download',
                'open',
                'thumbnail',
                'preview',
            ],
            //Keep it last: the rule is read key by key and this one ends the reading.
            'allowed' => function ($_user, $_role, ServerRequest $request): bool {
                $id = $request->getParam('pass.0');

                if (!is_string($id) || !Validation::uuid($id)) {
                    return false;
                }

                return TableRegistry::getTableLocator()->get('Files.FileLinks')->exists([
                    'FileLinks.id' => $id,
                    'FileLinks.model' => Documentations::MODEL,
                ]);
            },
        ],
        //and keeping it up to date goes with doing the work it records
        [
            'role' => [
                'customer-service-technician',
                'network-technician',
                'network-manager',
                'sales-representative',
                'sales-manager',
                'bookkeeper',
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'Documentations',
            ],
            'action' => [
                'add',
                'edit',
                'delete',
                'addFiles',
                'dropFile',
                'moveFile',
            ],
        ],
        //allow tasks for technicians, sales and bookkeepers
        [
            'role' => [
                'customer-service-technician',
                'network-technician',
                'network-manager',
                'sales-representative',
                'sales-manager',
                'bookkeeper',
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'Tasks',
            ],
            'action' => [
                'index',
                'view',
                'map',
                'add',
                'edit',
            ],
        ],
        //allow all indexes and views for sales-managers and network-managers
        [
            'role' => [
                'network-manager',
                'sales-manager',
                'manager',
            ],
            'plugin' => null,
            'controller' => '*',
            'action' => [
                'index',
                'view',
            ],
        ],
        // specific roles access
        [
            'role' => [
                'network-technician',
                'network-manager',
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'PowerSupplies',
            ],
            'action' => [
                'index',
                'view',
                'add',
                'edit',
            ],
        ],
        [
            'role' => [
                'network-manager',
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'AntennaTypes',
                'AccessPoints',
                'AccessPointContacts',
                'AccessPointTypes',
                'ElectricityMeterReadings',
                'IpAddressRanges',
                'Manufacturers',
                'PaymentPurposes',
                'PowerSupplyTypes',
                'RadioLinks',
                'RadioUnits',
                'RadioUnitBands',
                'RadioUnitTypes',
            ],
            'action' => [
                'index',
                'view',
                'add',
                'edit',
            ],
        ],
        [
            'role' => [
                'sales-manager',
                'bookkeeper',
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'AccessPoints',
                'AccessPointContacts',
                'ElectricityMeterReadings',
                'LandlordPayments',
            ],
            'action' => [
                'index',
                'view',
                'add',
                'edit',
            ],
        ],
        // the overviews read the records and change nothing, so whoever may see the records the
        // overview is of may see the overview
        [
            'role' => [
                'network-technician',
                'network-manager',
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'Overviews',
            ],
            'action' => [
                'overviewOfRadioUnitsAgainstDevices',
                'overviewOfDeviceRadiosAgainstRadioUnits',
                'overviewOfRadioUnitsAgainstRegisteredStations',
                'overviewOfRegisteredStationsAgainstRadioUnits',
            ],
        ],
        // planned outages are no secret and anyone may be asked about one; the rack itself only
        // lists what whoever opened it may follow, so it is open to everyone as well
        [
            'role' => $staff,
            'plugin' => null,
            'controller' => [
                'Overviews',
            ],
            'action' => [
                'index',
                'overviewOfPlannedPowerOutages',
            ],
        ],
        //what a manager may do beyond the other roles, each thing given to it on its own
        [
            'role' => [
                'manager',
            ],
            'plugin' => null,
            'controller' => [
                'DocumentationTypes',
                'TaskStates',
                'TaskTypes',
            ],
            'action' => [
                'add',
                'edit',
                'delete',
            ],
        ],
    ],
];

/*
 * Load local permissions if exists
 */
$localPermissionsFile = CONFIG . 'permissions_local.php';
if (file_exists($localPermissionsFile)) {
    $localPermissions = include $localPermissionsFile;
    /** @psalm-suppress RedundantCondition */
    if (is_array($localPermissions)) {
        // merge permissions - local first in order
        $permissions = array_merge_recursive($localPermissions, $permissions);
    }
}

return $permissions;
