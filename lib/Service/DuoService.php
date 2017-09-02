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

    /**
     * Check if a given ip is in a network
     * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
     * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
     * @return boolean true if the ip is in this range / false if not.
     */
    public function ip_in_range($ip, $range) {
	if (strpos($range, '/') == false) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }  

    public function __construct($appName, ConfigService $configService)
    {
        $this->appName = $appName;
        $this->configService = $configService;
    }


    /**
     * Get IP address of client (either actual IP or from X-Forwarded-For header(s))
     *
     * @param array $headers
     * @return array
     */
    public function getClient($headers) {
        if (isset($headers['X-Forwarded-For'])) {
            return explode(', ', $headers['X-Forwarded-For']);
        }
        else {
            return array((string)trim(getenv('REMOTE_ADDR')));
        }
    }

    /**
     * @param IUser $user
     * @param array $remote_ip
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
              $networkList = $this->configService->getAppValue("networkList");
              $networkListArray = explode(",", $networkList);
              foreach ($remote_ip as $ip) { 
                  if (in_array($ip,$ipListArray)) {
                      return false;
                  }
                  foreach ($networkListArray as $network) {
                      if ($this->ip_in_range($ip, $network)) {
                          return false;
                      }
                  }
              }
              return true;
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

        // Prepend NetBIOS domain name to username (if enabled)
        $netbiosDomain = $this->configService->getAppValue("netbiosDomain");
        if ($this->configService->getAppValue("netbiosEnabled") == true) {
            $netbiosDomain = $this->configService->getAppValue("netbiosDomain");
            $userName = $user->getUID();
            $fullUser = "$netbiosDomain\\$userName";
            $tmpl->assign('user', $fullUser);
        } else {
            $tmpl->assign('user', $user->getUID());
        }

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
