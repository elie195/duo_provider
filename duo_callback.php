<?php
require_once __DIR__ . '/../../lib/base.php';
require_once __DIR__ . '/vendor/autoload.php';

$state = isset($_GET['state'])    ? $_GET['state']    : '';
$code  = isset($_GET['duo_code']) ? $_GET['duo_code'] : '';

$session = \OC::$server->getSession();
$urlGen  = \OC::$server->getURLGenerator();

$nonce      = $state;
$savedNonce = $session->get('duo_nonce');

if (empty($code) || empty($savedNonce) || $savedNonce !== $nonce) {
    header('Location: ' . $urlGen->linkToRoute('core.login.showLoginForm'));
    exit();
}

header('Location: ' . $urlGen->linkToRoute('core.TwoFactorChallenge.showChallenge', [
    'challengeProviderId' => 'duo',
]) . '?duo_code=' . urlencode($code));
exit();