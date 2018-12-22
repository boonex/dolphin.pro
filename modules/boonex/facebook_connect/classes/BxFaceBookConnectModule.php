<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');

bx_import('BxDolConnectModule');
bx_import('BxDolInstallerUtils');
bx_import('BxDolProfilesController');
bx_import('BxDolAlerts');

class BxFaceBookConnectModule extends BxDolConnectModule
{
    var $oFacebook;

    /**
     * Class constructor ;
     *
     * @param   : $aModule (array) - contain some information about this module;
     *                  [ id ]           - (integer) module's  id ;
     *                  [ title ]        - (string)  module's  title ;
     *                  [ vendor ]       - (string)  module's  vendor ;
     *                  [ path ]         - (string)  path to this module ;
     *                  [ uri ]          - (string)  this module's URI ;
     *                  [ class_prefix ] - (string)  this module's php classes file prefix ;
     *                  [ db_prefix ]    - (string)  this module's Db tables prefix ;
     *                  [ date ]         - (string)  this module's date installation ;
     */
    function __construct(&$aModule)
    {
        parent::__construct($aModule);

        require_once(BX_DIRECTORY_PATH_PLUGINS . 'facebook-php-sdk/src/Facebook/autoload.php');

        // Create our Application instance.
        $this->oFacebook = null;

        if (!empty($this->_oConfig->mApiID) && !empty($this->_oConfig->mApiSecret)) {
            session_start();
            $this->oFacebook = new Facebook\Facebook(array(
                'app_id'                => $this->_oConfig->mApiID,
                'app_secret'            => $this->_oConfig->mApiSecret,
                'default_graph_version' => 'v2.4',
            ));
        }
    }

    /**
     * Function will generate facebook's admin page;
     *
     * @return : (text) - html presentation data;
     */
    function actionAdministration()
    {
        parent::_actionAdministration('bx_facebook_connect_api_key', '_bx_facebook_settings',
            '_bx_facebook_information', '_bx_facebook_information_block', $this->_oConfig->sPageReciver);
    }

    /**
     * Facebook login callback url;
     *
     * @return (text) - html presentation data;
     */
    function actionLoginCallback()
    {
        if (isLogged()) {
            header('Location:' . $this->_oConfig->sDefaultRedirectUrl);
            exit;
        }

        if (!$this->_oConfig->mApiID || !$this->_oConfig->mApiSecret) {
            $sCode = MsgBox(_t('_bx_facebook_profile_error_api_keys'));
        }

        if ($sError = $this->_setAccessToken()) {
            $sCode = MsgBox($sError);
        }

        if (!$sCode) {

            //we already logged in facebook
            try {
                $oResponse                         = $this->oFacebook->get('/me?fields=' . $this->_oConfig->sFaceBookFields);
                $aFacebookProfileInfo              = $oResponse->getDecodedBody();
                $aFacebookProfileInfo['nick_name'] = $aFacebookProfileInfo['name'];

            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                $sCode = MsgBox($e->getMessage());
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                $sCode = MsgBox($e->getMessage());
            }

            //process profile info
            if ($aFacebookProfileInfo) {

                // try define user id
                $iProfileId = $this->_oDb
                    ->getProfileId($aFacebookProfileInfo['id']);

                if ($iProfileId) {
                    // get profile info
                    $aDolphinProfileInfo = getProfileInfo($iProfileId);
                    $this->setLogged($iProfileId, $aDolphinProfileInfo['Password']);
                } else {
                    $sAlternativeNickName = '';

                    //process profile's nickname
                    $aFacebookProfileInfo['nick_name'] = $this
                        ->_proccesNickName($aFacebookProfileInfo['first_name']);

                    //-- profile nickname already used by other person --//
                    if (getID($aFacebookProfileInfo['nick_name'])) {
                        $sAlternativeNickName = $this
                            ->getAlternativeName($aFacebookProfileInfo['nick_name']);
                    }
                    //--

                    //try to get profile's image
                    if ($oFacebookProfileImageResponse = $this->oFacebook->get('/me/picture?type=large&redirect=false')) {

                        $aFacebookProfileImage           = $oFacebookProfileImageResponse->getDecodedBody();
                        $aFacebookProfileInfo['picture'] = isset($aFacebookProfileImage['data']['url']) && !$aFacebookProfileImage['data']['is_silhouette']
                            ? $aFacebookProfileImage['data']['url']
                            : '';
                    }

                    $this->getJoinAfterPaymentPage($aFacebookProfileInfo);

                    //create new profile
                    $this->_createProfile($aFacebookProfileInfo, $sAlternativeNickName);
                }
            } else {
                // FB profile info is not defined;
                $sCode = MsgBox(_t('_bx_facebook_profile_error_info'));
            }
        }


        $this->_oTemplate->getPage(_t('_bx_facebook'), $sCode);
    }

    /**
     * Generare facebook login form;
     *
     * @return (text) - html presentation data;
     */
    function actionLoginForm()
    {
        $sCode = '';

        if (isLogged()) {
            header('Location:' . $this->_oConfig->sDefaultRedirectUrl);
            exit;
        }

        if (!$this->_oConfig->mApiID || !$this->_oConfig->mApiSecret) {
            $sCode = MsgBox(_t('_bx_facebook_profile_error_api_keys'));
        } else {

            $oFacebookRedirectLoginHelper = $this->oFacebook->getRedirectLoginHelper();

            //redirect to facebook login form
            $sLoginUrl = $oFacebookRedirectLoginHelper->getLoginUrl(
                $this->_oConfig->aFaceBookReqParams['redirect_uri'],
                explode(',', $this->_oConfig->aFaceBookReqParams['scope'])
            );

            header('location: ' . $sLoginUrl);
            exit;
        }

        $this->_oTemplate->getPage(_t('_bx_facebook'), $sCode);
    }

    function serviceSupported()
    {
        return 1;
    }

    function serviceLogin($aFacebookProfileInfo, $sToken = '')
    {
        if (getParam('enable_dolphin_footer') == 'on') {
            return array('error' => _t('_bx_facebook_error_unlicensed_site'));
        }

        if ($sError = $this->_setAccessToken($sToken)) {
            return array('error' => $sError);
        }

        // try define user id
        $iProfileId = $this->_oDb
            ->getProfileId($aFacebookProfileInfo['id']);

        $aTmp['profile_id']        = $iProfileId;
        $aFacebookProfileInfoCheck = false;
        try {
            $oResponse                 = $this->oFacebook->get('/' . $aFacebookProfileInfo['id'] . '?fields=' . $this->_oConfig->sFaceBookFields);
            $aFacebookProfileInfoCheck = $oResponse->getDecodedBody();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            return array('error' => $e->getMessage());
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            return array('error' => $e->getMessage());
        }

        if (!isset($aFacebookProfileInfoCheck['id']) || $aFacebookProfileInfoCheck['id'] != $aFacebookProfileInfo['id']) {
            return array('error' => _t('_bx_facebook_profile_error_info'));
        }

        if ($iProfileId) {

            $aDolphinProfileInfo = getProfileInfo($iProfileId);
            $this->setLogged($iProfileId, '', '', false);

            require_once(BX_DIRECTORY_PATH_ROOT . 'xmlrpc/BxDolXMLRPCUser.php');

            return array(
                'member_id'       => $iProfileId,
                'member_pwd_hash' => $aDolphinProfileInfo['Password'],
                'member_username' => getUsername($iProfileId),
                'protocol_ver'    => BX_XMLRPC_PROTOCOL_VER,
            );
        } else {

            $sAlternativeNickName = '';

            //process profile's nickname
            $aFacebookProfileInfo['nick_name'] = $this->_proccesNickName($aFacebookProfileInfo['name']);

            //-- profile nickname already used by other person --//
            if (getID($aFacebookProfileInfo['nick_name'])) {
                $sAlternativeNickName = $this
                    ->getAlternativeName($aFacebookProfileInfo['nick_name']);
            }
            //--

            //try to get profile's image
            if ($oFacebookProfileImageResponse = $this->oFacebook->get('/' . $aFacebookProfileInfo['id'] . '/picture?type=large&redirect=false')) {

                $aFacebookProfileImage           = $oFacebookProfileImageResponse->getDecodedBody();
                $aFacebookProfileInfo['picture'] = isset($aFacebookProfileImage['data']['url']) && !$aFacebookProfileImage['data']['is_silhouette']
                    ? $aFacebookProfileImage['data']['url']
                    : '';
            }

            // mobile app doesn't support redirect to join form (or any other redirects)
            if ('join' == $this->_oConfig->sRedirectPage) {
                $this->_oConfig->sRedirectPage = 'pedit';
            }

            //create new profile
            $mixed = $this->_createProfileRaw($aFacebookProfileInfo, $sAlternativeNickName, false, true);

            if (is_string($mixed)) { // known error occured

                return array(
                    'error'        => $mixed,
                    'protocol_ver' => BX_XMLRPC_PROTOCOL_VER,
                );

            } elseif (is_array($mixed) && isset($mixed['profile_id'])) { // everything is good

                $iProfileId          = $mixed['profile_id'];
                $aDolphinProfileInfo = getProfileInfo($iProfileId);
                $sMemberAvatar       = !empty($mixed['remote_profile_info']['picture']) ? $mixed['remote_profile_info']['picture'] : '';

                //assign avatar
                if ($sMemberAvatar && !$mixed['existing_profile']) {
                    $this->_assignAvatar($sMemberAvatar, $iProfileId);
                }

                return array(
                    'member_id'        => $iProfileId,
                    'member_pwd_hash'  => $aDolphinProfileInfo['Password'],
                    'member_username'  => getUsername($iProfileId),
                    'protocol_ver'     => BX_XMLRPC_PROTOCOL_VER,
                    'existing_profile' => isset($mixed['existing_profile']) && $mixed['existing_profile'],
                );

            } else { // unknown error

                return array(
                    'error'        => _t('_Error Occured'),
                    'protocol_ver' => BX_XMLRPC_PROTOCOL_VER,
                );

            }

        }
    }

    /**
     * Make friends
     *
     * @param $iProfileId integer
     * @return void
     */
    function _makeFriends($iProfileId)
    {
        if (!$this->_oConfig->bAutoFriends) {
            return;
        }

        try {
            //get friends from facebook
            $oFriendsResponse = $this->oFacebook->get('/me/friends?limit=5000');
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            return;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            return;
        }

        // paginate through the result
        $oPagesEdge = $oFriendsResponse->getGraphEdge();
        do {
            foreach ($oPagesEdge as $oPage) {
                $aFriend = $oPage->asArray();

                $iFriendId = $this->_oDb->getProfileId($aFriend['id']);
                if ($iFriendId && !is_friends($iProfileId, $iFriendId)) {
                    //add to friends list
                    $this->_oDb->makeFriend($iProfileId, $iFriendId);

                    //create system alert
                    $oZ = new BxDolAlerts('friend', 'accept', $iProfileId, $iFriendId);
                    $oZ->alert();
                }
            }
        } while ($oPagesEdge = $this->oFacebook->next($oPagesEdge));

    }

    /**
     * @param $aProfileInfo     - remote profile info
     * @param $sAlternativeName - suffix to add to NickName to make it unique
     * @return profile array info, ready for the local database
     */
    protected function _convertRemoteFields($aProfileInfo, $sAlternativeName = '')
    {
        // process the date of birth
        if (isset($aProfileInfo['birthday'])) {
            $aProfileInfo['birthday'] = isset($aProfileInfo['birthday'])
                ? date('Y-m-d', strtotime($aProfileInfo['birthday']))
                : '';
        }

        // define user's country and city
        $aLocation = array();
        if (isset($aProfileInfo['location']['name'])) {
            $aLocation = $aProfileInfo['location']['name'];
        } elseif (isset($aProfileInfo['hometown']['name'])) {
            $aLocation = $aProfileInfo['hometown']['name'];
        }

        if ($aLocation) {
            $aCountryInfo = explode(',', $aLocation);
            $sCountry     = $this->_oDb->getCountryCode(trim($aCountryInfo[1]));
            $sCity        = trim($aCountryInfo[0]);

            //set default country name, especially for American brothers
            if ($sCity && !$sCountry) {
                $sCountry = $this->_oConfig->sDefaultCountryCode;
            }
        }

        // try define the user's email
        $sEmail = !empty($aProfileInfo['email'])
            ? $aProfileInfo['email']
            : $aProfileInfo['proxied_email'];

        // fill array with all needed values
        $aProfileFields = array(
            'NickName'    => $aProfileInfo['nick_name'] . $sAlternativeName,
            'Email'       => $sEmail,
            'Sex'         => isset($aProfileInfo['gender']) ? $aProfileInfo['gender'] : '',
            'DateOfBirth' => $aProfileInfo['birthday'],

            'Password' => $aProfileInfo['password'],

            'FullName' => (isset($aProfileInfo['first_name']) ? $aProfileInfo['first_name'] : '') . (isset($aProfileInfo['last_name']) ? ' ' . $aProfileInfo['last_name'] : ''),

            'DescriptionMe' => clear_xss(isset($aProfileInfo['bio']) ? $aProfileInfo['bio'] : ''),
            'Interests'     => isset($aProfileInfo['interests']) ? $aProfileInfo['interests'] : '',

            'Religion' => isset($aProfileInfo['religion']) ? $aProfileInfo['religion'] : '',
            'Country'  => $sCountry,
            'City'     => $sCity,
        );

        return $aProfileFields;
    }

    /**
     * Function will clear all unnecessary sybmols from profile's nickname;
     *
     * @param  : $sProfileName (string) - profile's nickname;
     * @return : (string) - cleared nickname;
     */
    function _proccesNickName($sProfileName)
    {
        $sProfileName = preg_replace("/^http:\/\/|^https:\/\/|\/$/", '', $sProfileName);
        $sProfileName = str_replace('/', '_', $sProfileName);
        $sProfileName = str_replace('.', '-', $sProfileName);

        return $sProfileName;
    }

    function _setAccessToken($sToken = '')
    {
        if ($sToken) {
            $this->oFacebook->setDefaultAccessToken($sToken);

            return '';
        }

        $oFacebookRedirectLoginHelper = $this->oFacebook->getRedirectLoginHelper();

        try {
            $sAccessToken = $oFacebookRedirectLoginHelper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return $e->getMessage();
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return $e->getMessage();
        }

        if (!isset($sAccessToken)) {
            return $oFacebookRedirectLoginHelper->getError() ? $oFacebookRedirectLoginHelper->getErrorDescription() : _t('_Error occured');
        }

        $this->oFacebook->setDefaultAccessToken($sAccessToken);

        return '';
    }

}
