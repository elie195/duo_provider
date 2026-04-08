<?php
namespace OCA\Duo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;

class CallbackController extends Controller {

    private $session;
    private $urlGenerator;

    public function __construct($AppName, IRequest $request, ISession $session, IURLGenerator $urlGenerator) {
        parent::__construct($AppName, $request);
        $this->session      = $session;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @PublicPage
     * @NoCSRFRequired
     */
    public function index() {
        $returnedState = $this->request->getParam('state', '');
        $code          = $this->request->getParam('duo_code', '');

        // Decode the state payload to recover the original session ID
        $payload = json_decode(base64_decode($returnedState), true);

        if (empty($code) || empty($payload) || empty($payload['sid']) || empty($payload['nonce'])) {
            return new RedirectResponse(
                $this->urlGenerator->linkToRoute('core.login.showLoginForm')
            );
        }

        $originalSid = $payload['sid'];

        // Close the current (empty) session and reopen the original one.
        // This is the key step: it reconnects us to the session that holds
        // two_factor_auth_uid and duo_nonce.
        session_write_close();
        session_id($originalSid);
        session_start();

        // Now re-read session values through the OC session wrapper,
        // which is already bound to the (now-restored) PHP session
        $savedNonce = $this->session->get('duo_nonce');

        if (empty($savedNonce) || $savedNonce !== $payload['nonce']) {
            $this->session->remove('duo_nonce');
            $this->session->remove('duo_username');
            return new RedirectResponse(
                $this->urlGenerator->linkToRoute('core.login.showLoginForm')
            );
        }

        // Store the code — validateChallenge() will consume it
        $this->session->set('duo_code', $code);

        // Redirect back to the 2FA challenge page — the session is now valid
        return new RedirectResponse(
            $this->urlGenerator->linkToRoute('core.TwoFactorChallenge.showChallenge', [
                'challengeProviderId' => 'duo',
            ])
        );
    }
}