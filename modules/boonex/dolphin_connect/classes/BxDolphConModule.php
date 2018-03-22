<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );

bx_import('BxDolModuleDb');
bx_import('BxDolConnectModule');
bx_import('BxDolInstallerUtils');
bx_import('BxDolProfilesController');
bx_import('BxDolAlerts');

class BxDolphConModule extends BxDolConnectModule
{
    function __construct(&$aModule)
    {
        parent::__construct($aModule);
    }

    /**
     * Generate admin page;
     *
     * @return : (text) - html presentation data;
     */
    function actionAdministration()
    {
        parent::_actionAdministration('bx_dolphcon_api_key', '_bx_dolphcon_settings', '_bx_dolphcon_information', '_bx_dolphcon_information_block');
    }

    /**
     * Redirect to remote Dolphin site login form
     *
     * @return n/a/ - redirect or HTML page in case of error
     */
    function actionStart()
    {
        if (isLogged())
            $this->_redirect ($this -> _oConfig -> sDefaultRedirectUrl);

        if (!$this->_oConfig->sApiID || !$this->_oConfig->sApiSecret || !$this->_oConfig->sApiUrl) {
            $sCode =  MsgBox( _t('_bx_dolphcon_profile_error_api_keys') );
            $this->_oTemplate->getPage(_t('_bx_dolphcon'), $sCode);            
        } 
        else {

            // define redirect URL to the remote Dolphin site                
            $sUrl = bx_append_url_params($this->_oConfig->sApiUrl . 'auth', array(
                'response_type' => 'code',
                'client_id' => $this->_oConfig->sApiID,
                'redirect_uri' => $this->_oConfig->sPageHandle,
                'scope' => $this->_oConfig->sScope,
                'state' => $this->_genCsrfToken(),
            ));
            $this->_redirect($sUrl);
        }
    }

    function actionHandle()
    {
        // check CSRF token
        if ($this->_getCsrfToken() != bx_get('state')) {
            $this->_oTemplate->getPage(_t('_Error'), MsgBox(_t('_bx_dolphcon_state_invalid')));
            return;
        }

        // check code
        $sCode = bx_get('code');
        if (!$sCode) {
            $sErrorDescription = bx_get('error_description') ? bx_get('error_description') : _t('_Error occured');
            $this->_oTemplate->getPage(_t('_Error'), MsgBox($sErrorDescription));
            return;
        }

        // make request for token
        $s = bx_file_get_contents($this->_oConfig->sApiUrl . 'token', array(
            'client_id'     => $this->_oConfig->sApiID,
            'client_secret' => $this->_oConfig->sApiSecret,
            'grant_type'    => 'authorization_code',
            'code'          => $sCode,
            'redirect_uri'  => $this->_oConfig->sPageHandle,
        ), 'post');

        // handle error
        if (!$s || NULL === ($aResponse = json_decode($s, true)) || !isset($aResponse['access_token']) || isset($aResponse['error'])) {
            $sErrorDescription = isset($aResponse['error_description']) ? $aResponse['error_description'] : _t('_Error occured');
            $this->_oTemplate->getPage(_t('_Error'), MsgBox($sErrorDescription));
            return;
        }

        // get the data, especially access_token
        $sAccessToken = $aResponse['access_token'];
        $sExpiresIn = $aResponse['expires_in'];
        $sExpiresAt = new \DateTime('+' . $sExpiresIn . ' seconds');
        $sRefreshToken = $aResponse['refresh_token'];

        // request info about profile
        $s = bx_file_get_contents($this->_oConfig->sApiUrl . 'api/me', array(), 'get', array(
            'Authorization: Bearer ' . $sAccessToken,
        ));

        // handle error
        if (!$s || NULL === ($aResponse = json_decode($s, true)) || !$aResponse || isset($aResponse['error'])) {
            $sErrorDescription = isset($aResponse['error_description']) ? $aResponse['error_description'] : _t('_Error occured'); 
            $this->_oTemplate->getPage(_t('_Error'), MsgBox($sErrorDescription));
            return;
        }

        $aRemoteProfileInfo = $aResponse;

        if ($aRemoteProfileInfo) {

            // check if user logged in before
            $iLocalProfileId = $this->_oDb->getProfileId($aRemoteProfileInfo['id']);
            
            if ($iLocalProfileId) { 
                // user already exists

                $aLocalProfileInfo = getProfileInfo($iLocalProfileId);

                $this->setLogged($iLocalProfileId, $aLocalProfileInfo['Password']);

            }             
            else { 
                // register new user
                $sAlternativeNickName = '';
                if (getID($aRemoteProfileInfo['NickName']))
                    $sAlternativeNickName = $this->getAlternativeName($aRemoteProfileInfo['NickName']);

                $this->getJoinAfterPaymentPage($aRemoteProfileInfo);

                $this->_createProfile($aRemoteProfileInfo, $sAlternativeNickName);
            }
        } 
        else {
            $this->_oTemplate->getPage(_t('_Error'), MsgBox(_t('_bx_dolphcon_profile_error_info')));
        }
    }


    /**
     * @param $aProfileInfo - remote profile info
     * @param $sAlternativeName - suffix to add to NickName to make it unique
     * @return profile array info, ready for the local database
     */
    protected function _convertRemoteFields($aProfileInfo, $sAlternativeName = '')
    {
        $aProfileFields = $aProfileInfo;
        $aProfileFields['NickName'] = $aProfileInfo['NickName'] . $sAlternativeName;
        return $aProfileFields;
    }

    protected function _genCsrfToken($bReturn = false)
    {
        if ($GLOBALS['MySQL']->getParam('sys_security_form_token_enable') != 'on' || defined('BX_DOL_CRON_EXECUTE'))
            return false;

        $oSession = BxDolSession::getInstance();

        $iCsrfTokenLifetime = (int)$this->_oDb->getParam('sys_security_form_token_lifetime');
        if ($oSession->getValue('bx_dolphcon_csrf_token') === false || ($iCsrfTokenLifetime != 0 && time() - (int)$oSession->getValue('csrf_token_time') > $iCsrfTokenLifetime)) {
            $sToken = genRndPwd(20, false);
            $oSession->setValue('bx_dolphcon_csrf_token', $sToken);
            $oSession->setValue('bx_dolphcon_csrf_token_time', time());
        }
        else {
            $sToken = $oSession->getValue('bx_dolphcon_csrf_token');
        }

        return $sToken;
    }

    protected function _getCsrfToken()
    {
        $oSession = BxDolSession::getInstance();
        return $oSession->getValue('bx_dolphcon_csrf_token');
    }

}
