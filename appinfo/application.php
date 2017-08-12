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

class Application extends App
{
    public function __construct(array $urlParams = [])
    {
        parent::__construct('duo', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers
         */
        $container->registerService('AdminController', function($c) {
            return new AdminController(
                $c->query('Logger'),
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('DuoService')
            );
        });

        $container->registerService('Logger', function($c) {
            return $c->query('ServerContainer')->getLogger();
        });

        $container->registerAlias(IDuoService::class, DuoService::class);
    }

    public function registerSettings() {
        \OCP\App::registerAdmin('duo', 'admin');
        
    }
}
