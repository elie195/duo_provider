<?php

/**
 * @author El-ad Blech <elie@theinfamousblix.com>
 *
 * Duo MFA
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Duo\AppInfo;

use OCP\AppFramework\App;
use OCA\Duo\Controller\AdminController;
//use OCA\Duo\Controller\CallbackController;
use OCA\Duo\Service\ConfigService;
use OCA\Duo\Service\DuoService;
use OCA\Duo\Service\IDuoService;

class Application extends App
{
    public function __construct(array $urlParams = [])
    {
        parent::__construct('duo', $urlParams);

        $container = $this->getContainer();

        /**
         * Services
         */
        $container->registerService('ConfigService', function ($c) {
            return new ConfigService(
                $c->query('Logger'),
                $c->query('AppName'),
                $c->query('ServerContainer')->getConfig()
            );
        });
 
        $container->registerService('DuoService', function ($c) {
            return new DuoService(
                $c->query('AppName'),
                $c->query('ConfigService'),
                $c->query('ServerContainer')->getSession(),
                $c->query('ServerContainer')->getURLGenerator(),
                $c->query('Request')
            );
        });
 
        $container->registerAlias(IDuoService::class, DuoService::class);

        /**
         * Controllers
         */
        $container->registerService('AdminController', function($c) {
            return new AdminController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('ConfigService')
            );
        });

        // $container->registerService('CallbackController', function ($c) {
        //     return new CallbackController(
        //         $c->query('AppName'),
        //         $c->query('Request'),
        //         $c->query('ServerContainer')->getSession(),
        //         $c->query('ServerContainer')->getURLGenerator()
        //     );
        // });

        $container->registerService('Logger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });
    }

    public function registerSettings() {
        \OCP\App::registerAdmin('duo', 'admin');
        
    }
}
