<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    //-- demo api and secret key --//
    // api      : 112808408740127;
    // secret   : 464f98fc9bcac09ca66fa5b8169c9657;

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

    class BxFaceBookConnectConfig extends BxDolConfig
    {
        var $mApiID;
        var $mApiSecret;

        var $sPageReciver;
        var $sSessionKey;
        var $sDefaultRedirectUrl;

        var $sFacebookSessionUid = 'facebook_session';
        var $sFacebookSessionProfile = 'facebook_session_profile';
        var $sFaceBookAlternativePostfix;
        var $sRedirectPage;

        var $bAutoFriends;
        var $aFaceBookReqParams;
        var $sFaceBookFields;

        var $sDefaultCountryCode = 'US';

        /**
         * Class constructor;
         */
        function BxFaceBookConnectConfig($aModule)
        {
            parent::BxDolConfig($aModule);

            $this -> mApiID		  = getParam('bx_facebook_connect_api_key');
            $this -> mApiSecret   = getParam('bx_facebook_connect_secret');
            $this -> sPageReciver = BX_DOL_URL_ROOT . $this -> getBaseUri() . 'login_callback';

            $this -> sDefaultRedirectUrl = BX_DOL_URL_ROOT . 'member.php';
            $this -> sFaceBookAlternativePostfix = '_fb';
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
