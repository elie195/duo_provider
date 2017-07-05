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

namespace OCA\Duo\Service;

use OCP\IUser;

interface IDuoService {

    /**
     * @param IUser $user
     * @param string $remote_ip
     */
    public function userEnabled(IUser $user, $remote_ip);

    /**
     * @param IUser $user
     */
    public function renderTemplate(IUser $user);

    /**
     */
    public function setCsp();

    /**
     * @param IUser $user
     * @param string $challenge
     */
    public function validateChallenge(IUser $user, $challenge);


    /**
     * Get IP address of client (either actual IP or from X-Forwarded-For header(s))
     *
     * @param array $headers
     * @return array
     */
    public function getClient($headers);

}
