<?php

/**
 * @author El-ad Blech <elie@theinfamousblix.com>
 *
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

namespace OCA\Duo\Provider;

use OCP\Authentication\TwoFactorAuth\IProvider2;
use OCA\Duo\Service\DuoService;
use OCP\IUser;
use OCP\Template;
use OCP\IConfig;

require_once 'duo/lib/Web.php';


class DuoProvider implements IProvider2 {

	/** @var DuoService */
        private $duoService;

	/**
	 * @param DuoService $duoService
	 */
        public function __construct(DuoService $duoService) {
		$this->duoService = $duoService;
        }


	/**
	 * Get unique identifier of this 2FA provider
	 *
	 * @return string
	 */
	public function getId() {
		return 'duo';
	}

	/**
	 * Get the display name for selecting the 2FA provider
	 *
	 * @return string
	 */
	public function getDisplayName() {
		return 'Duo';
	}

	/**
	 * Get the description for selecting the 2FA provider
	 *
	 * @return string
	 */
	public function getDescription() {
		return 'Duo';
	}

	/**
         * Get the Content Security Policy for the template (required for showing external content, otherwise optional)
         *
         * @return \OCP\AppFramework\Http\ContentSecurityPolicy
         */

	public function getCSP() {
		return $this->duoService->setCsp();
        }

	/**
	 * Get the template for rending the 2FA provider view
	 *
	 * @param IUser $user
	 * @return Template
	 */
	public function getTemplate(IUser $user) {
		return $this->duoService->renderTemplate($user);
	}

	/**
	 * Verify the given challenge
	 *
	 * @param IUser $user
	 * @param string $challenge
	 */
	public function verifyChallenge(IUser $user, $challenge) {
		return $this->duoService->validateChallenge($user, $challenge);
	}

	/**
	 * Decides whether 2FA is enabled for the given user
	 *
	 * @param IUser $user
	 * @return boolean
	 */
	public function isTwoFactorAuthEnabledForUser(IUser $user) {
		$headers = getallheaders();
                // getClient returns either the X-Forwarded-For IP(s), if present, or the client remote IP
		$remote_ip = $this->duoService->getClient($headers);
                return $this->duoService->userEnabled($user, $remote_ip);
	}

}
