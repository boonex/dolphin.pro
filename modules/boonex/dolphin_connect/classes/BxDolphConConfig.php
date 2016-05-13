<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectConfig');

class BxDolphConConfig extends BxDolConnectConfig
{
    var $sApiID;
    var $sApiSecret;
    var $sApiUrl;

    var $sPageStart;
    var $sPageHandle;

    var $sScope = 'basic';

    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this -> sApiID = getParam('bx_dolphcon_api_key');
        $this -> sApiSecret = getParam('bx_dolphcon_connect_secret');
        $this -> sApiUrl = trim(getParam('bx_dolphcon_connect_url'), '/') . (getParam('bx_dolphcon_connect_url_rewrite') ? '/m/oauth2/' : '/modules/?r=oauth2/');

        $this -> sSessionUid = 'dolphcon_session';
        $this -> sSessionProfile = 'dolphcon_session_profile';

        $this -> sEmailTemplatePasswordGenerated = 't_bx_dolphcon_password_generated';
        $this -> sDefaultTitleLangKey = '_bx_dolphcon';

        $this -> sPageStart = BX_DOL_URL_ROOT . $this -> getBaseUri() . 'start';
        $this -> sPageHandle = BX_DOL_URL_ROOT . $this -> getBaseUri() . 'handle';

        $this -> sRedirectPage = getParam('bx_dolphcon_connect_redirect_page');
    }
}
