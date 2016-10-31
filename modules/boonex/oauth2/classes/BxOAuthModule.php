<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxDolPaginate');
bx_import('BxDolAlerts');

require_once (BX_DIRECTORY_PATH_PLUGINS . 'OAuth2/Autoloader.php');
OAuth2\Autoloader::register();

class BxOAuthModule extends BxDolModule
{
    protected $_oStorage;
    protected $_oServer;
    protected $_oAPI;

    function __construct(&$aModule)
    {
        parent::__construct($aModule);

        $aConfig = array (
            'client_table' => 'bx_oauth_clients',
            'access_token_table' => 'bx_oauth_access_tokens',
            'refresh_token_table' => 'bx_oauth_refresh_tokens',
            'code_table' => 'bx_oauth_authorization_codes',
            'user_table' => 'Profiles',
            'jwt_table'  => '',
            'jti_table'  => '',
            'scope_table'  => 'bx_oauth_scopes',
            'public_key_table'  => '',
        );

        $this->_oStorage = new OAuth2\Storage\Pdo(BxDolDb::getInstance()->getLink(), $aConfig);

        $this->_oServer = new OAuth2\Server($this->_oStorage, array (
            'require_exact_redirect_uri' => false,
        ));

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $this->_oServer->addGrantType(new OAuth2\GrantType\ClientCredentials($this->_oStorage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->_oServer->addGrantType(new OAuth2\GrantType\AuthorizationCode($this->_oStorage));

    }

    function actionToken ()
    {
        // Handle a request for an OAuth2.0 Access Token and send the response to the client
        $this->_oServer->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    }

    function actionApi ($sAction)
    {
        // Handle a request to a resource and authenticate the access token
        if (!$this->_oServer->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->_oServer->getResponse()->send();
            return;
        }

        $aToken = $this->_oServer->getAccessTokenData(OAuth2\Request::createFromGlobals());

        if (!$this->_oAPI) {
            bx_import('API', $this->_aModule);
            $this->_oAPI = new BxOAuthAPI($this);
        }

        if (!$sAction || !method_exists($this->_oAPI, $sAction) || 0 === strcasecmp('errorOutput', $sAction) || 0 === strcasecmp('output', $sAction)) {
            $this->_oAPI->errorOutput(404, 'not_found', 'No such API endpoint available');
            return;
        }

        $sScope = $this->_oAPI->aAction2Scope[$sAction];
        if (false === strpos($sScope, $aToken['scope'])) {
            $this->_oAPI->errorOutput(403, 'insufficient_scope', 'The request requires higher privileges than provided by the access token');
            return;
        }

        $this->_oAPI->$sAction($aToken);

        //echo json_encode(array('success' => true, 'message' => 'TODO: process "' . $sAction . '" action for user "' . $aToken['user_id'] . '"'));
    }

    function actionAuth ()
    {
        $oRequest = OAuth2\Request::createFromGlobals();
        $oResponse = new OAuth2\Response();

        // validate the authorize request
        if (!$this->_oServer->validateAuthorizeRequest($oRequest, $oResponse)) {
            $o = json_decode($oResponse->getResponseBody());
            $this->_oTemplate->pageError($o->error_description);
        }

        if (!isLogged()) {
            $_REQUEST['relocate'] = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'auth/?client_id=' . bx_get('client_id') . '&response_type=' . bx_get('response_type') . '&state=' . bx_get('state') . '&redirect_uri=' . bx_get('redirect_uri');
            login_form('', 0, false, 'disable_external_auth no_join_text');
            return;
        }

        if (!($iProfileId = $this->_oDb->getSavedProfile(getLoggedId())) && empty($_POST)) {
            $this->_oTemplate->pageAuth($this->_oDb->getClientTitle(bx_get('client_id')));
            return;
        }

        $bConfirm = $iProfileId ? true : (bool)bx_get('confirm');
        $iProfileId = getLoggedId();

        $this->_oServer->handleAuthorizeRequest($oRequest, $oResponse, $bConfirm, $iProfileId);

        $oResponse->send();
    }

    function actionAdministration ()
    {
        if (!$this->isAdmin()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();        


        bx_import('FormAdd', $this->_aModule);
        $oForm = new BxOAuthFormAdd($this);
        $oForm->initChecker();

        $sContent = '';
        if ($oForm->isSubmittedAndValid ()) {
            $oForm->insert ();
            $sContent = MsgBox(_t('_Success'));
        }
        $sContent .= $oForm->getCode ();

        $aVars = array (
            'content' => $sContent,
        );
        echo $this->_oTemplate->adminBlock ($this->_oTemplate->parseHtmlByName('default_padding', $aVars), _t('_bx_oauth_add'));


        if (is_array($_POST['clients']) && $_POST['clients'])
            $this->_oDb->deleteClients($_POST['clients']);
        bx_import('BxTemplSearchResult');
        $sControls = BxTemplSearchResult::showAdminActionsPanel('bx-oauth-form-add', array(
            'bx-oauth-delete' => _t('_Delete'),
        ), 'clients');

        $aClients = $this->_oDb->getClients();
        $aVars = array (
            'bx_repeat:clients' => $aClients,
            'controls' => $sControls,
        );
        echo $this->_oTemplate->adminBlock ($this->_oTemplate->parseHtmlByName('clients', $aVars), _t('_bx_oauth_clients'));


        $aVars = array (
            'content' => _t('_bx_oauth_help_text', BX_DOL_URL_ROOT)
        );
        echo $this->_oTemplate->adminBlock ($this->_oTemplate->parseHtmlByName('default_padding', $aVars), _t('_bx_oauth_help'));


        $this->_oTemplate->addCssAdmin ('forms_adv.css');
        $this->_oTemplate->pageCodeAdmin (_t('_bx_oauth_administration'));
    }

    function isAdmin ()
    {
        return $GLOBALS['logged']['admin'] ? true : false;
    }
}
