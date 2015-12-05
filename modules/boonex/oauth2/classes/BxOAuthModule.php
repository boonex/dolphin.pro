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

    function BxOAuthModule(&$aModule)
    {
        parent::BxDolModule($aModule);

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

        $this->_oStorage = new OAuth2\Storage\Pdo(array(
            'dsn' => $this->_buildDSN(), 
            'username' => $GLOBALS['db']['user'], 
            'password' => $GLOBALS['db']['passwd'],
            'options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"),
        ), $aConfig);

        $this->_oServer = new OAuth2\Server($this->_oStorage, array (
            'require_exact_redirect_uri' => false,
        ));

        // Add the "Client Credentials" grant type (it is the simplest of the grant types)
        $this->_oServer->addGrantType(new OAuth2\GrantType\ClientCredentials($this->_oStorage));

        // Add the "Authorization Code" grant type (this is where the oauth magic happens)
        $this->_oServer->addGrantType(new OAuth2\GrantType\AuthorizationCode($this->_oStorage));

    }

    protected function _buildDSN () 
    {
        $sDSN = 'mysql:';
        if (!empty($GLOBALS['db']['host']))
            $sDSN .= 'host=' . $GLOBALS['db']['host'] . ';';
        if (!empty($GLOBALS['db']['port']))
            $sDSN .= 'port=' . $GLOBALS['db']['port'] . ';';
        if (!empty($GLOBALS['db']['sock']))
            $sDSN .= 'unix_socket=' . $GLOBALS['db']['sock'] . ';';
        $sDSN .= 'dbname=' . $GLOBALS['db']['db'] . ';charset=UTF8';
        return $sDSN;
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

        if (!$sAction || !method_exists($this->_oAPI, $sAction) || 0 == strcasecmp('errorOutput', $sAction) || 0 == strcasecmp('output', $sAction)) {
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

        if (empty($_POST))
            $this->_oTemplate->pageAuth();

        $this->_oServer->handleAuthorizeRequest($oRequest, $oResponse, (bool)bx_get('confirm'), getLoggedId());

        $oResponse->send();
    }

    function actionAdministration ()
    {
        if (!$this->isAdmin()) {
            $this->_oTemplate->displayAccessDenied ();
            return;
        }

        $this->_oTemplate->pageStart();

        $iId = $this->_oDb->getSettingsCategory();
        if(empty($iId)) {
            echo MsgBox(_t('_sys_request_page_not_found_cpt'));
            $this->_oTemplate->pageCodeAdmin (_t('_bx_oauth_administration'));
            return;
        }

        bx_import('BxDolAdminSettings');

        $mixedResult = '';
        if(isset($_POST['save']) && isset($_POST['cat'])) {
            $oSettings = new BxDolAdminSettings($iId);
            $mixedResult = $oSettings->saveChanges($_POST);
        }

        $oSettings = new BxDolAdminSettings($iId);
        $sResult = $oSettings->getForm();

        if($mixedResult !== true && !empty($mixedResult))
            $sResult = $mixedResult . $sResult;

        $aVars = array (
            'content' => $sResult,
        );
        echo $this->_oTemplate->adminBlock ($this->_oTemplate->parseHtmlByName('default_padding', $aVars), _t('_bx_oauth_administration'));

        $aVars = array (
            'content' => _t('_bx_oauth_help_text')
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
