<?php
/**
 * Duo Universal Prompt v4 callback handler.
 * This file is accessed directly (not via the OC app framework) because
 * OC_Util::checkLoggedIn() blocks app framework routes during 2FA pending state.
 */

// Bootstrap ownCloud without the full app framework dispatcher
require_once __DIR__ . '/../../lib/base.php';

use OCA\Duo\AppInfo\Application;

// Load the duo app's autoloader so we can use its classes
require_once __DIR__ . '/vendor/autoload.php';

$state = isset($_GET['state']) ? $_GET['state'] : '';
$code  = isset($_GET['duo_code']) ? $_GET['duo_code'] : '';

$session    = \OC::$server->getSession();
$urlGen     = \OC::$server->getURLGenerator();

// Decode and validate state
$payload = json_decode(base64_decode($state), true);

if (empty($code) || empty($payload['sid']) || empty($payload['nonce'])) {
    header('Location: ' . $urlGen->linkToRoute('core.login.showLoginForm'));
    exit();
}

// Restore the original session
session_write_close();
session_id($payload['sid']);
session_start();

$savedNonce = $session->get('duo_nonce');

if (empty($savedNonce) || $savedNonce !== $payload['nonce']) {
    $session->remove('duo_nonce');
    $session->remove('duo_username');
    header('Location: ' . $urlGen->linkToRoute('core.login.showLoginForm'));
    exit();
}

// Store code for validateChallenge()
$session->set('duo_code', $code);

// Redirect to the 2FA challenge page — session is valid, 2FA is still pending
header('Location: ' . $urlGen->linkToRoute('core.TwoFactorChallenge.showChallenge', [
    'challengeProviderId' => 'duo',
]));
exit();