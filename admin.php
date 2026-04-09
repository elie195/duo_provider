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

$config = \OC::$server->getConfig();

// Migrate legacy ikey/skey config keys to client_id/client_secret on first load
$oldIkey = $config->getAppValue('duo', 'ikey', '');
$oldSkey = $config->getAppValue('duo', 'skey', '');

if (!empty($oldIkey) && !$config->getAppValue('duo', 'client_id', '')) {
    $config->setAppValue('duo', 'client_id', $oldIkey);
    $config->deleteAppValue('duo', 'ikey');
}
if (!empty($oldSkey) && !$config->getAppValue('duo', 'client_secret', '')) {
    $config->setAppValue('duo', 'client_secret', $oldSkey);
    $config->deleteAppValue('duo', 'skey');
}
if ($config->getAppValue('duo', 'akey', '') !== '') {
    $config->deleteAppValue('duo', 'akey');
}

\OCP\Util::addScript('duo', 'duo_admin');
\OCP\Util::addStyle('duo', 'style');

$params = [
    'client_id'      => \OC::$server->getConfig()->getAppValue('duo', 'client_id', ''),
    'client_secret'  => \OC::$server->getConfig()->getAppValue('duo', 'client_secret', ''),
    'host'           => \OC::$server->getConfig()->getAppValue('duo', 'host', ''),
    'globalEnabled'  => \OC::$server->getConfig()->getAppValue('duo', 'globalEnabled', false),
    'ipEnabled'      => \OC::$server->getConfig()->getAppValue('duo', 'ipEnabled', false),
    'ldapEnabled'    => \OC::$server->getConfig()->getAppValue('duo', 'ldapEnabled', false),
    'ipList'         => \OC::$server->getConfig()->getAppValue('duo', 'ipList', ''),
    'networkList'    => \OC::$server->getConfig()->getAppValue('duo', 'networkList', ''),
    'netbiosDomain'  => \OC::$server->getConfig()->getAppValue('duo', 'netbiosDomain', ''),
    'netbiosEnabled' => \OC::$server->getConfig()->getAppValue('duo', 'netbiosEnabled', false),
];

$tmpl = new OCP\Template('duo', 'admin');
foreach ($params as $key => $value) {
    $tmpl->assign($key, $value);
}

return $tmpl->fetchPage();