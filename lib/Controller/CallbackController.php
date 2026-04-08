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

namespace OCA\Duo\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\ISession;
use OCP\IURLGenerator;

class CallbackController extends Controller {

    /** @var ISession */
    private $session;

    /** @var IURLGenerator */
    private $urlGenerator;

    /**
     * @param string $AppName
     * @param IRequest $request
     * @param ISession $session
     * @param IURLGenerator $urlGenerator
     */
    public function __construct($AppName, IRequest $request, ISession $session, IURLGenerator $urlGenerator) {
        parent::__construct($AppName, $request);
        $this->session      = $session;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Handles the redirect back from Duo after authentication.
     * Validates the state parameter, stores the Duo code in session,
     * then redirects back to ownCloud's 2FA challenge page.
     *
     * @PublicPage
     * @NoCSRFRequired
     */
    public function index() {
        $returnedState = $this->request->getParam('state', '');
        $code          = $this->request->getParam('duo_code', '');
        $savedState    = $this->session->get('duo_state');

        // State mismatch or missing code — possible CSRF or error, abort to login
        if (empty($code) || empty($returnedState) || $returnedState !== $savedState) {
            $this->session->remove('duo_state');
            $this->session->remove('duo_username');
            return new RedirectResponse(
                $this->urlGenerator->linkToRoute('core.login.showLoginForm')
            );
        }

        // Store the authorization code — DuoService::validateChallenge() will consume it
        $this->session->set('duo_code', $code);

        // Redirect back to the ownCloud 2FA challenge page for the duo provider.
        // The challenge template will detect duo_code_pending and auto-submit the form,
        // which triggers verifyChallenge() in the ownCloud core.
        return new RedirectResponse(
            $this->urlGenerator->linkToRoute('core.TwoFactorChallenge.showChallenge', [
                'challengeProviderId' => 'duo',
            ])
        );
    }
}