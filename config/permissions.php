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
use Cake\Utility\Hash;

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
 * Default permissions
 */
$permissions = [
    'CakeDC/Auth.permissions' => [
        //RouterOS devices access to configuration script
        [
            'prefix' => false,
            'controller' => 'RouterosDevices',
            'action' => [
                'configurationScript',
            ],
            'bypassAuth' => true,
        ],
        //all bypass
        [
            'prefix' => false,
            'plugin' => null,
            'controller' => 'AppUsers',
            'action' => [
                // LoginTrait
                'socialLogin',
                'login',
                'logout',
                'socialEmail',
                'verify',
                // RegisterTrait
                'register',
                'validateEmail',
                // PasswordManagementTrait used in RegisterTrait
                'changePassword',
                'resetPassword',
                'requestResetPassword',
                // UserValidationTrait used in PasswordManagementTrait
                'resendTokenValidation',
                'linkSocial',
                //U2F actions
                'u2f',
                'u2fRegister',
                'u2fRegisterFinish',
                'u2fAuthenticate',
                'u2fAuthenticateFinish',
            ],
            'bypassAuth' => true,
        ],
        [
            'prefix' => false,
            'plugin' => 'CakeDC/Users',
            'controller' => 'SocialAccounts',
            'action' => [
                'validateAccount',
                'resendValidation',
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
        //specific actions allowed for the all roles in Users plugin
        [
            'role' => '*',
            'plugin' => null,
            'controller' => 'AppUsers',
            'action' => [
                'profile',
                'logout',
                'linkSocial',
                'callbackLinkSocial',
                'userSettings',
            ],
        ],
        [
            'role' => '*',
            'plugin' => null,
            'controller' => 'AppUsers',
            'action' => 'resetOneTimePasswordAuthenticator',
            'allowed' => function (array $user, $role, ServerRequest $request) {
                $userId = Hash::get($request->getAttribute('params'), 'pass.0');
                if (!empty($userId) && !empty($user)) {
                    return $userId === $user['id'];
                }

                return false;
            },
        ],
        //all roles allowed to Pages/display
        [
            'role' => '*',
            'controller' => 'Pages',
            'action' => 'display',
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
                'RouterosDevices',
            ],
            'action' => [
                'index',
                'view',
                'search',
            ],
        ],
        //all roles access
        [
            'role' => '*',
            'plugin' => null,
            'controller' => [
                'Settings',
            ],
            'action' => [
                'index',
            ],
        ],
        [
            'role' => '*',
            'plugin' => null,
            'controller' => [
                'AccessPoints',
            ],
            'action' => [
                'index',
                'view',
                'map',
            ],
        ],
        [
            'role' => '*',
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
            'role' => '*',
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
            'role' => '*',
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
        //allow all indexes and views for sales-managers and network-managers
        [
            'role' => [
                'network-manager',
                'sales-manager',
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
            ],
            'plugin' => null,
            'controller' => [
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
    ],
];

/*
 * Load local permissions if exists
 */
$localPermissionsFile = CONFIG . 'permissions_local.php';
if (file_exists($localPermissionsFile)) {
    $localPermissions = include $localPermissionsFile;
    if (is_array($localPermissions)) {
        // merge permissions - local first in order
        $permissions = array_merge_recursive($localPermissions, $permissions);
    }
}

return $permissions;
