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
     * Migrate legacy ikey/skey config keys to client_id/client_secret on first load.
     * Safe to call repeatedly — only runs if old keys exist and new ones are absent.
     */
    private function migrateLegacyKeys() {
        $oldIkey = $this->configService->getAppValue("ikey");
        $oldSkey = $this->configService->getAppValue("skey");
 
        if (!empty($oldIkey) && !$this->configService->hasValue("client_id")) {
            $this->configService->setAppValue("client_id", $oldIkey);
            $this->configService->deleteAppValue("ikey");
        }
        if (!empty($oldSkey) && !$this->configService->hasValue("client_secret")) {
            $this->configService->setAppValue("client_secret", $oldSkey);
            $this->configService->deleteAppValue("skey");
        }
        // Remove legacy akey — not used in v4
        if ($this->configService->hasValue("akey")) {
            $this->configService->deleteAppValue("akey");
        }
    }

    /**
     * @AdminRequired
     * @return TemplateResponse
     */
    public function index() {
        $this->migrateLegacyKeys();
 
        $params = [
            'client_id'      => $this->configService->getAppValue("client_id"),
            'client_secret'  => $this->configService->getAppValue("client_secret"),
            'host'           => $this->configService->getAppValue("host"),
            'globalEnabled'  => $this->configService->getAppValue("globalEnabled"),
            'ipEnabled'      => $this->configService->getAppValue("ipEnabled"),
            'ldapEnabled'    => $this->configService->getAppValue("ldapEnabled"),
            'ipList'         => $this->configService->getAppValue("ipList"),
            'networkList'    => $this->configService->getAppValue("networkList"),
            'netbiosDomain'  => $this->configService->getAppValue("netbiosDomain"),
            'netbiosEnabled' => $this->configService->getAppValue("netbiosEnabled"),
        ];
        return new TemplateResponse($this->appName, 'admin', $params, 'admin');
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
