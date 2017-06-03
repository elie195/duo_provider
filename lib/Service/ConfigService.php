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

use Exception;
use \OCP\IConfig;

class ConfigService {

    /**
     *
     * @var \OCP\IConfig
     */
    protected $duoConfig;

    /**
     *
     * @var string
     */
    protected $appName;

    public function __construct($appName, IConfig $duoConfig) {
        $this->appName = $appName;
        $this->duoConfig = $duoConfig;
    }

    public function hasValue($key) {
        $keys = $this->duoConfig->getAppKeys($this->appName);
        if (in_array($key,$keys)) {
          if (!empty($this->duoConfig->getAppValue($this->appName, $key))) {
            return true;
          }
          return false;
        }
        return false;
    }

    public function getAppValue($key, $default='') {
        return $this->duoConfig->getAppValue($this->appName, $key, $default);
    }

    public function setAppValue($key, $value) {
        $this->duoConfig->setAppValue($this->appName, $key, $value);
    }

    public function deleteAppValues() {
        $this->duoConfig->deleteAppValues($this->appName);
    }
}
