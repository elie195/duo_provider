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

namespace OCA\Duo\Controller;

use OCA\Duo\Service\ConfigService;
use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

class AdminController extends Controller {

    /** @var ConfigService */
    private $configService;
   
    /**
     * @param string $AppName
     * @param IRequest $request
     * @param ConfigService $configService
     */
    public function __construct($AppName, IRequest $request, ConfigService $configService) {
        parent::__construct($AppName, $request);
        $this->configService = $configService;
    }

    /**
     * @param string $client_id
     * @param string $client_secret
     * @param string $host
     * @param bool $globalEnabled
     * @param bool $ipEnabled
     * @param bool $ldapEnabled
     * @param string $ipList
     * @param string $networkList
     * @param string $netbiosDomain
     * @param bool $netbiosEnabled
     * @return DataResponse
     */
    public function saveSettings($client_id, $client_secret, $host, $globalEnabled, $ipEnabled, $ldapEnabled, $ipList, $networkList, $netbiosDomain, $netbiosEnabled) {
        $this->configService->setAppValue("client_id",      $client_id);
        $this->configService->setAppValue("client_secret",  $client_secret);
        $this->configService->setAppValue("host",           $host);
        $this->configService->setAppValue("globalEnabled",  $globalEnabled);
        $this->configService->setAppValue("ipEnabled",      $ipEnabled);
        $this->configService->setAppValue("ldapEnabled",    $ldapEnabled);
        $this->configService->setAppValue("ipList",         $ipList);
        $this->configService->setAppValue("networkList",    $networkList);
        $this->configService->setAppValue("netbiosDomain",  $netbiosDomain);
        $this->configService->setAppValue("netbiosEnabled", $netbiosEnabled);
        return new DataResponse(['status' => 'success']);
    }

    /**
     * @return DataResponse
     */
    public function resetSettings() {
        $this->configService->deleteAppValues();
        return new DataResponse(['status' => 'success']);
    }

}