<?php

/**
 * @author El-ad Blech <elie@theinfamousblix.com>
 *
 * Duo Provider
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

namespace OCA\Duo\Service;

use OCP\IUser;
use OCP\Template;

class DuoService implements IDuoService {

    private $appName;
    private $configService;

    public function __construct($appName, ConfigService $configService)
    {
        $this->appName = $appName;
        $this->configService = $configService;
    }

    /**
     * @param IUser $user
     * @param string $remote_ip
     */
    public function userEnabled(IUser $user, $remote_ip)
    {
        // First, check if Duo is globally enabled in settings
        if ($this->configService->getAppValue("globalEnabled") == true) {
          // Next, check if LDAP bypass is enabled and check if the current user is an LDAP user
          if ($this->configService->getAppValue("ldapEnabled") == true) {
              $backend = $user->getBackendClassName();
                  if ($backend == 'LDAP') {
                      return false;
                  } else {
                      return true;
                  }
          }
          if ($this->configService->getAppValue("ipEnabled") == true) {
              $ipList = $this->configService->getAppValue("ipList");
              $ipListArray = explode(",", $ipList);
              if (in_array($remote_ip,$ipListArray)) {
                  return false;
              } else {
                  return true;
              }
          }
          // This point means that advanced options are off, but Duo is globally on
          return true;
        }
        // Globally disabled
        return false;
    }

    public function setCsp()
    {
        $csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();
        $csp->addAllowedChildSrcDomain('https://*.duosecurity.com');
        $csp->addAllowedStyleDomain('https://*.duosecurity.com');
        $csp->addAllowedFrameDomain('https://*.duosecurity.com');
        return $csp;
    }

    public function renderTemplate(IUser $user)
    {
        $ikey = $this->configService->getAppValue("ikey");
        $skey = $this->configService->getAppValue("skey");
        $host = $this->configService->getAppValue("host");
        $akey = $this->configService->getAppValue("akey");

        $tmpl = new Template('duo', 'challenge');
        $tmpl->assign('user', $user->getUID());
        $tmpl->assign('IKEY', $ikey);
        $tmpl->assign('SKEY', $skey);
        $tmpl->assign('AKEY', $akey);
        $tmpl->assign('HOST', $host);
        return $tmpl;
    } 

    /**
     * @param IUser $user
     * @param string $challenge
     */
    public function validateChallenge(IUser $user, $challenge)
    {
        $ikey = $this->configService->getAppValue("ikey");
        $skey = $this->configService->getAppValue("skey");
        $akey = $this->configService->getAppValue("akey");
        $resp = \Duo\Web::verifyResponse($ikey, $skey, $akey, $challenge);

        if ($resp) {
            return true;
        }
        return false;

    }
}
