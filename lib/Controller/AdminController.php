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
     * @AdminRequired
     * @return TemplateResponse
     */
    public function index() {
        if (!$this->configService->hasValue("akey")) {
            $this->configService->setAppValue("akey","8749032634b9c0ee14fa785c3e59b424a13d2073");
        }
        $params = [
            'ikey' => $this->configService->getAppValue("ikey"),
            'skey' => $this->configService->getAppValue("skey"),
            'host' => $this->configService->getAppValue("host"),
            'akey' => $this->configService->getAppValue("akey"),
            'globalEnabled' => $this->configService->getAppValue("globalEnabled"),
            'ipEnabled' => $this->configService->getAppValue("ipEnabled"),
            'ldapEnabled' => $this->configService->getAppValue("ldapEnabled"),
            'ipList' => $this->configService->getAppValue("ipList")
        ];
        return new TemplateResponse($this->appName, 'admin', $params, 'admin');
    }

    /**
     * @param string $ikey
     * @param string $skey
     * @param string $host
     * @param string $akey
     * @param bool $globalEnabled
     * @param bool $ipEnabled
     * @param bool $ldapEnabled
     * @param string $ipList
     * @return TemplateResponse
     */
    public function saveSettings($ikey, $skey, $host, $akey, $globalEnabled, $ipEnabled, $ldapEnabled, $ipList) {
        $this->configService->setAppValue("ikey", $ikey);
        $this->configService->setAppValue("skey", $skey);
        $this->configService->setAppValue("host", $host);
        $this->configService->setAppValue("globalEnabled", $globalEnabled);
        $this->configService->setAppValue("ipEnabled", $ipEnabled);
        $this->configService->setAppValue("ldapEnabled", $ldapEnabled);
        $this->configService->setAppValue("ipList", $ipList);
        return $this->configService->setAppValue("akey", $akey);
    }

    /**
     * @return bool
     */
    public function resetSettings() {
        $this->configService->deleteAppValues();
        return true;
    }
}
