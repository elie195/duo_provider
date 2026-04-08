<?php
require_once __DIR__ . '/../../lib/base.php';
require_once __DIR__ . '/vendor/autoload.php';

$state = isset($_GET['state']) ? $_GET['state'] : '';
$code  = isset($_GET['duo_code']) ? $_GET['duo_code'] : '';

$session = \OC::$server->getSession();
$urlGen  = \OC::$server->getURLGenerator();

$payload = json_decode(base64_decode($state), true);

if (empty($code) || empty($payload['nonce'])) {
    header('Location: ' . $urlGen->linkToRoute('core.login.showLoginForm'));
    exit();
}

$savedNonce = $session->get('duo_nonce');

if (empty($savedNonce) || $savedNonce !== $payload['nonce']) {
    header('Location: ' . $urlGen->linkToRoute('core.login.showLoginForm'));
    exit();
}

$session->set('duo_code', $code);
$session->remove('duo_nonce');

header('Location: ' . $urlGen->linkToRoute('core.TwoFactorChallenge.showChallenge', [
    'challengeProviderId' => 'duo',
]));
exit();