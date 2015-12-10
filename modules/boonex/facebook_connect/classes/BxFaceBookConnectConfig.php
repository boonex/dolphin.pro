<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConnectConfig');

class BxFaceBookConnectConfig extends BxDolConnectConfig
{
    public $mApiID;
    public $mApiSecret;

    public $sPageReciver;

    public $bAutoFriends;
    public $aFaceBookReqParams;
    public $sFaceBookFields;

    public $sDefaultCountryCode = 'US';

    function BxFaceBookConnectConfig($aModule)
    {
        parent::BxDolConnectConfig($aModule);

        $this -> mApiID		  = getParam('bx_facebook_connect_api_key');
        $this -> mApiSecret   = getParam('bx_facebook_connect_secret');
        $this -> sPageReciver = BX_DOL_URL_ROOT . $this -> getBaseUri() . 'login_callback';

        $this -> sSessionUid = 'facebook_session';
        $this -> sSessionProfile = 'facebook_session_profile';

        $this -> sEmailTemplatePasswordGenerated = 't_fb_connect_password_generated';
        $this -> sDefaultTitleLangKey = '_bx_facebook';

        $this -> sRedirectPage = getParam('bx_facebook_connect_redirect_page');

        $this -> bAutoFriends = 'on' == getParam('bx_facebook_connect_auto_friends')
            ? true
            : false;

        $this -> aFaceBookReqParams = array(
            'scope' => getParam('bx_facebook_connect_extended_info') 
                ? 'email,public_profile,user_friends,user_birthday,user_about_me,user_hometown,user_location'
                : 'email,public_profile,user_friends',
            'redirect_uri' => $this -> sPageReciver,
        );

        $this -> sFaceBookFields = getParam('bx_facebook_connect_extended_info') 
            ? 'name,email,first_name,last_name,gender,birthday,bio,hometown,location'
            : 'name,email,first_name,last_name,gender';
    }
}
