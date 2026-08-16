<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Http\ServerRequest;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

/*
 * This file is loaded in the context of the `Application` class.
  * So you can use  `$this` to reference the application class instance
  * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
        * Access Points - nested routes
        */
        $builder
            ->connect('/access-points/{access_point_id}', [
                'controller' => 'AccessPoints',
                'action' => 'view',
            ])
            ->setPatterns([
                'access_point_id' => RouteBuilder::UUID,
            ])
            ->setPass(['access_point_id']);

        $builder
            ->connect('/access-points/{access_point_id}/{action}', [
                'controller' => 'AccessPoints',
            ])
            ->setPatterns([
                'action' => 'edit|delete',
                'access_point_id' => RouteBuilder::UUID,
            ])
            ->setPass(['access_point_id']);

        $builder
            ->connect('/access-points/{access_point_id}/{controller}', [
                'action' => 'index',
            ])
            ->setPatterns([
                'access_point_id' => RouteBuilder::UUID,
            ]);

        $builder
            ->connect('/access-points/{access_point_id}/{controller}/{action}/*', [])
            ->setPatterns([
                'access_point_id' => RouteBuilder::UUID,
            ]);

        // The landing page
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);

        /*
        * ...and connect the rest of 'Pages' controller's URLs.
        */
        $builder->connect('/pages/*', 'Pages::display');

        /*
        * Connect catchall routes for all controllers.
        *
        * The `fallbacks` method is a shortcut for
        *
        * ```
        * $builder->connect('/:controller', ['action' => 'index']);
        * $builder->connect('/:controller/:action/*', []);
        * ```
        *
        * You can remove these routes once you've connected the
        * routes you want in your application.
        */
        $builder->fallbacks();
    });

    /*
    * If you need a different set of middleware or none at all,
    * open new scope and define routes there.
    *
    * ```
    * $routes->scope('/api', function (RouteBuilder $builder) {
    *     // No $builder->applyMiddleware() here.
    *
    *     // Parse specified extensions from URLs
    *     // $builder->setExtensions(['json', 'xml']);
    *
    *     // Connect API actions here.
    * });
    * ```
    */

    // API access
    $routes->prefix('Api', function (RouteBuilder $builder): void {
        $builder->setExtensions(['json']);

        $builder->resources('AccessPoints');
        $builder->resources('IpAddressRanges', [
            'map' => [
                'search' => [
                    'action' => 'search',
                    'method' => 'GET',
                ],
            ],
        ]);
        $builder->resources('RouterosDevices', [
            'map' => [
                'search' => [
                    'action' => 'search',
                    'method' => 'GET',
                ],
            ],
        ]);

        // Agent API endpoints
        $builder->prefix('Agent', function (RouteBuilder $agentBuilder): void {
            $agentBuilder->setExtensions(['json']);

            $agentBuilder->post(
                '/provision/routeros',
                ['controller' => 'Provision', 'action' => 'routeros'],
            );
        });
    });

    //the filter reads the request it is generating a URL under, and a console
    //run has none - `Router::getRequest()` answers null there and hands that
    //straight over, so the filter has to take it and step aside. It used to be
    //left unregistered when PHP_SAPI was cli instead, which also left it out
    //under the test runner and built different URLs there than in a browser.
    Router::addUrlFilter(function (array $params, ?ServerRequest $request) {
        if ($request === null) {
            return $params;
        }

        //persistent win-link parameter, unless the caller asked for something
        //else - passing null opts out, for links meant to be followed outside
        //the popup window. Note isset() would not see that null.
        $winLink = $request->getQuery('win-link') == 'true';
        if ($winLink && !array_key_exists('win-link', $params['?'] ?? [])) {
            $params['?']['win-link'] = 'true';
        }

        //controllers related to access points
        $accessPointControllers = [
            'AccessPointContacts',
            'ElectricityMeterReadings',
            'IpAddressRanges',
            'LandlordPayments',
            'PowerSupplies',
            'RadioUnits',
            'RouterosDevices',
        ];
        $controller = $params['controller'] ?? $request->getParam('controller');
        if (in_array($controller, $accessPointControllers)) {
            //inject access_point_id, unless the caller asked for something else -
            //passing null opts out, for links meant to leave the nesting behind.
            //Note isset() would not see that null.
            $accessPointId = $request->getParam('access_point_id');
            if ($accessPointId && !array_key_exists('access_point_id', $params)) {
                $params['access_point_id'] = $accessPointId;
            }
            if (
                array_key_exists('access_point_id', $params)
                && $params['access_point_id'] === null
            ) {
                unset($params['access_point_id']);
            }
        }

        return $params;
    });
};
