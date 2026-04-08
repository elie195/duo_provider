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
use OCP\ISession;
use OCP\IURLGenerator;
use OCP\Template;

use Duo\DuoUniversal\Client;
use Duo\DuoUniversal\DuoException;

class DuoService implements IDuoService {

    private $appName;
    private $configService;

    /** @var ISession */
    private $session;
 
    /** @var IURLGenerator */
    private $urlGenerator;

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

    public function __construct($appName, ConfigService $configService, ISession $session, IURLGenerator $urlGenerator, \OCP\IRequest $request)
    {
        $this->appName = $appName;
        $this->configService = $configService;
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
        $this->request = $request;
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

    /**
     * Build a Duo Client instance from config
     *
     * @return Client
     * @throws DuoException
     */
    private function buildDuoClient(): Client
    {
        $clientId     = $this->configService->getAppValue("client_id");
        $clientSecret = $this->configService->getAppValue("client_secret");
        $host         = $this->configService->getAppValue("host");
        $redirectUri  = $this->urlGenerator->linkToRouteAbsolute('duo.callback.index');

        return new Client($clientId, $clientSecret, $host, $redirectUri);
    }

    /**
     * Build the Duo auth URL, store state + username in session, return the challenge template.
     *
     * @param IUser $user
     * @return Template
     */
    public function renderTemplate(IUser $user)
    {
        $tmpl = new Template('duo', 'challenge');

        try {
            $duoClient = $this->buildDuoClient();
            $duoClient->healthCheck();

            $hasPendingCode = !empty($this->session->get('duo_code'));

            $tmpl->assign('duo_code_pending', $hasPendingCode);
            $tmpl->assign('duo_error', null);

            if (!$hasPendingCode) {
                // Generate a random nonce for CSRF protection
                $nonce = bin2hex(random_bytes(16));

                // Encode both the nonce AND the current session ID into state,
                // so we can restore the session when Duo redirects back.
                // Format: base64(json({nonce, sid}))
                $sessionId = session_id();
                $statePayload = base64_encode(json_encode([
                    'nonce' => $nonce,
                    'sid'   => $sessionId,
                ]));

                $this->session->set('duo_nonce', $nonce);
                $this->session->set('duo_username', $user->getUID());

                $duoUsername = $this->resolveDuoUsername($user);
                $authUrl = $duoClient->createAuthUrl($duoUsername, $statePayload);

                $tmpl->assign('duo_auth_url', $authUrl);
            } else {
                $tmpl->assign('duo_auth_url', '');
            }

        } catch (DuoException $e) {
            $this->configService->log('Duo error: ' . $e->getMessage());
            $tmpl->assign('duo_auth_url', '');
            $tmpl->assign('duo_code_pending', false);
            $tmpl->assign('duo_error', $e->getMessage());
        }

        return $tmpl;
    }

    /**
     * Validate the Duo challenge by exchanging the stored authorization code.
     *
     * @param IUser $user
     * @param string $challenge  (not used directly — code comes from session)
     * @return bool
     */
    public function validateChallenge(IUser $user, $challenge)
    {
        $code          = $this->session->get('duo_code');
        $savedNonce    = $this->session->get('duo_nonce');
        $savedUsername = $this->session->get('duo_username');

        $this->session->remove('duo_code');
        $this->session->remove('duo_nonce');
        $this->session->remove('duo_username');

        if (empty($code) || empty($savedNonce) || $savedUsername !== $user->getUID()) {
            $this->configService->log('Duo validation failed: missing/mismatched session for ' . $user->getUID());
            return false;
        }

        try {
            $duoClient   = $this->buildDuoClient();
            $duoUsername = $this->resolveDuoUsername($user);
            $duoClient->exchangeAuthorizationCodeFor2FAResult($code, $duoUsername);
            return true;
        } catch (DuoException $e) {
            $this->configService->log('Duo exchange failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve the username to send to Duo, applying NetBIOS domain prepending if configured.
     *
     * @param IUser $user
     * @return string
     */
    private function resolveDuoUsername(IUser $user): string
    {
        if ($this->configService->getAppValue("netbiosEnabled") == true) {
            $netbiosDomain = $this->configService->getAppValue("netbiosDomain");
            return "$netbiosDomain\\" . $user->getUID();
        }
        return $user->getUID();
    }
}
