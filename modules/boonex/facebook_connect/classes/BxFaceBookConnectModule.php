<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );

    bx_import('BxDolModuleDb');
    bx_import('BxDolModule');
    bx_import('BxDolInstallerUtils');
    bx_import('BxDolProfilesController');
    bx_import('BxDolAlerts');

    class BxFaceBookConnectModule extends BxDolModule
    {
        // contain some module information ;
        var $aModuleInfo;

        // contain path for current module;
        var $sPathToModule;
        var $sHomeUrl;

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
        function BxFaceBookConnectModule(&$aModule)
        {
            parent::BxDolModule($aModule);

            require_once(BX_DIRECTORY_PATH_PLUGINS . 'facebook-php-sdk/src/Facebook/autoload.php');

            // prepare the location link ;
            $this -> sPathToModule  = BX_DOL_URL_ROOT . $this -> _oConfig -> getBaseUri();

            $this -> aModuleInfo    = $aModule;
            $this -> sHomeUrl       = $this ->_oConfig -> _sHomeUrl;

            // Create our Application instance.
            $this -> oFacebook = null;

            if ($this -> _oConfig -> mApiID) {
                session_start();
                $this -> oFacebook = new Facebook\Facebook(array(
                    'app_id'  => $this -> _oConfig -> mApiID,
                    'app_secret' => $this -> _oConfig -> mApiSecret,
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
            $GLOBALS['iAdminPage'] = 1;

            if( !isAdmin() ) {
                header('location: ' . BX_DOL_URL_ROOT);
            }

            // get sys_option's category id;
            $iCatId = $this-> _oDb -> getSettingsCategoryId('bx_facebook_connect_api_key');
            if(!$iCatId) {
                $sOptions = MsgBox( _t('_Empty') );
            } else {
                bx_import('BxDolAdminSettings');

                $oSettings = new BxDolAdminSettings($iCatId);

                $mixedResult = '';
                if(isset($_POST['save']) && isset($_POST['cat'])) {
                    $mixedResult = $oSettings -> saveChanges($_POST);
                }

                // get option's form;
                $sOptions = $oSettings -> getForm();
                if($mixedResult !== true && !empty($mixedResult)) {
                    $sOptions = $mixedResult . $sOptions;
                }
            }

            $sCssStyles = $this -> _oTemplate -> addCss('forms_adv.css', true);

            $this -> _oTemplate-> pageCodeAdminStart();

                echo DesignBoxAdmin( _t('_bx_facebook_information')
                        , $GLOBALS['oSysTemplate'] -> parseHtmlByName('default_padding.html', array('content' => _t('_bx_facebook_information_block', BX_DOL_URL_ROOT))) );
                echo DesignBoxAdmin( _t('_Settings')
                        , $GLOBALS['oSysTemplate'] -> parseHtmlByName('default_padding.html', array('content' => $sCssStyles . $sOptions) ));

            $this -> _oTemplate->pageCodeAdmin( _t('_bx_facebook_settings') );
        }

        /**
         * Facebook login callback url;
         *
         * @return (text) - html presentation data;
         */
        function actionLoginCallback()
        {
            if (isLogged()) {
                header ('Location:' . $this -> _oConfig -> sDefaultRedirectUrl);
                exit;
            }

            if (!$this -> _oConfig -> mApiID || !$this -> _oConfig -> mApiSecret)
                $sCode =  MsgBox( _t('_bx_facebook_profile_error_api_keys') );

            if ($sError = $this->_setAccessToken())
                $sCode = MsgBox($sError);

            if (!$sCode) {

                //we already logged in facebook
                try {
                    $oResponse = $this -> oFacebook -> get('/me?fields=' . $this -> _oConfig -> sFaceBookFields);
                    $aFacebookProfileInfo = $oResponse -> getDecodedBody();
                    $aFacebookProfileInfo['nick_name'] = $aFacebookProfileInfo['name'];

                } catch (Facebook\Exceptions\FacebookResponseException $e) {
                    $sCode =  MsgBox($e->getMessage());
                } catch (Facebook\Exceptions\FacebookSDKException $e) {
                    $sCode =  MsgBox($e->getMessage());
                }

                //process profile info
                if($aFacebookProfileInfo) {

                    // try define user id
                    $iProfileId = $this -> _oDb
                        -> getProfileId($aFacebookProfileInfo['id']);

                    if($iProfileId) {
                           // get profile info
                           $aDolphinProfileInfo = getProfileInfo($iProfileId);
                           $this -> setLogged($iProfileId, $aDolphinProfileInfo['Password']);
                    } else {
                        $sAlternativeNickName = '';

                        //process profile's nickname
                        $aFacebookProfileInfo['nick_name'] = $this
                            -> _proccesNickName($aFacebookProfileInfo['first_name']);

                        //-- profile nickname already used by other person --//
                        if( getID($aFacebookProfileInfo['nick_name']) ) {
                               $sAlternativeNickName = $this
                                -> getAlternativeName($aFacebookProfileInfo['nick_name']);
                        }
                        //--

                        //try to get profile's image
                        if ($oFacebookProfileImageResponse = $this -> oFacebook -> get('/me/picture?type=large&redirect=false')) {

                            $aFacebookProfileImage = $oFacebookProfileImageResponse -> getDecodedBody();
                            $aFacebookProfileInfo['picture'] = isset($aFacebookProfileImage['data']['url']) && !$aFacebookProfileImage['data']['is_silhouette']
                                ? $aFacebookProfileImage['data']['url']
                                : '';
                        }

						$this->getJoinAfterPaymentPage($aFacebookProfileInfo);

                        //create new profile
                        $this -> _createProfile($aFacebookProfileInfo, $sAlternativeNickName);
                    }
                } else {
                    // FB profile info is not defined;
                    $sCode = MsgBox( _t('_bx_facebook_profile_error_info') );
                }
            }


            $this -> _oTemplate -> getPage( _t('_bx_facebook'), $sCode );
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
                header ('Location:' . $this -> _oConfig -> sDefaultRedirectUrl);
                exit;
            }

            if (!$this -> _oConfig -> mApiID || !$this -> _oConfig -> mApiSecret) {
                $sCode =  MsgBox( _t('_bx_facebook_profile_error_api_keys') );
            } 
            else {

                $oFacebookRedirectLoginHelper = $this -> oFacebook -> getRedirectLoginHelper();

                //redirect to facebook login form
                $sLoginUrl = $oFacebookRedirectLoginHelper->getLoginUrl(
                    $this -> _oConfig -> aFaceBookReqParams['redirect_uri'],
                    explode(',', $this -> _oConfig -> aFaceBookReqParams['scope'])
                );

                header('location: ' . $sLoginUrl);
                exit;
            }

            $this -> _oTemplate -> getPage( _t('_bx_facebook'), $sCode );
        }

        function serviceSupported ()
        {
            return 1;
        }

        function serviceLogin ($aFacebookProfileInfo)
        {
            if ($sError = $this->_setAccessToken())
                return array ('error' => $sError);

            // try define user id
            $iProfileId = $this -> _oDb
                -> getProfileId($aFacebookProfileInfo['id']);

            $aTmp['profile_id'] = $iProfileId;
            $aFacebookProfileInfoCheck = false;
            try {
                $oResponse = $this -> oFacebook -> get('/' . $aFacebookProfileInfo['id'] .'?fields=' . $this -> _oConfig -> sFaceBookFields);
                $aFacebookProfileInfoCheck = $oResponse -> getDecodedBody();
                $aFacebookProfileInfoCheck['nick_name'] = $aFacebookProfileInfoCheck['name'];
            } catch (Facebook\Exceptions\FacebookResponseException $e) {
                return array ('error' => $e->getMessage());
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                return array ('error' => $e->getMessage());
            }

            if (!isset($aFacebookProfileInfoCheck['id']) 
                || $aFacebookProfileInfoCheck['id'] != $aFacebookProfileInfo['id'] 
                || $aFacebookProfileInfoCheck['username'] != $aFacebookProfileInfo['username']
                || $aFacebookProfileInfoCheck['email'] != $aFacebookProfileInfo['email'])
                return array ('error' => _t('_bx_facebook_profile_error_info'));

            if ($iProfileId) { 

                $aDolphinProfileInfo = getProfileInfo($iProfileId);
                $this -> setLogged($iProfileId, '', '', false);

                require_once(BX_DIRECTORY_PATH_ROOT . 'xmlrpc/BxDolXMLRPCUser.php');

                return array(
                    'member_id' => $iProfileId,                    
                    'member_pwd_hash' => $aDolphinProfileInfo['Password'],
                    'member_username' => getUsername($iProfileId),
                    'protocol_ver' => BX_XMLRPC_PROTOCOL_VER,
                );
            } else {
                
                $sAlternativeNickName = '';

                //process profile's nickname
                $aFacebookProfileInfo['nick_name'] = $this
                    -> _proccesNickName($aFacebookProfileInfo['username']);

                //-- profile nickname already used by other person --//
                if( getID($aFacebookProfileInfo['nick_name']) ) {
                       $sAlternativeNickName = $this
                        -> getAlternativeName($aFacebookProfileInfo['nick_name']);
                }
                //--

                //try to get profile's image
                if ($oFacebookProfileImageResponse = $this -> oFacebook -> get('/' . $aFacebookProfileInfo['id'] . '?fields=picture&type=large')) {

                    $aFacebookProfileImage = $oFacebookProfileImageResponse -> getDecodedBody();
                    $aFacebookProfileInfo['picture'] = isset($aFacebookProfileImage['data']['url']) && $aFacebookProfileImage['data']['is_silhouette'] 
                        ? $aFacebookProfileImage['data']['url'] // 'https://graph.facebook.com/' . $aFacebookProfileInfo['id'] . '/picture?type=large&redirect=false'
                        : ''; 
                }

                // mobile app doesn't support redirect to join form (or any other redirects)
                if ('join' == $this -> _oConfig -> sRedirectPage)
                    $this -> _oConfig -> sRedirectPage = 'pedit';

                //create new profile
                $mixed = $this -> _createProfileRaw($aFacebookProfileInfo, $sAlternativeNickName, false, true);
                
                if (is_string($mixed)) { // known error occured

                    return array(
                        'error' => $mixed,
                        'protocol_ver' => BX_XMLRPC_PROTOCOL_VER,
                    );

                } elseif (is_array($mixed) && isset($mixed['profile_id'])) { // everything is good

                    $iProfileId = $mixed['profile_id'];
                    $aDolphinProfileInfo = getProfileInfo($iProfileId);
                    $sMemberAvatar = !empty($mixed['profile_info_fb']['picture']) ? $mixed['profile_info_fb']['picture'] : '';

                    //assign avatar
                    if ($sMemberAvatar && !$mixed['existing_profile'])
                        $this -> _assignAvatar($sMemberAvatar, $iProfileId);

                    return array(
                        'member_id' => $iProfileId,
                        'member_pwd_hash' => $aDolphinProfileInfo['Password'],
                        'member_username' => getUsername($iProfileId),
                        'protocol_ver' => BX_XMLRPC_PROTOCOL_VER,
                        'existing_profile' => isset($mixed['existing_profile']) && $mixed['existing_profile'],
                    );

                } else { // unknown error

                    return array(
                        'error' => _t('_Error Occured'),
                        'protocol_ver' => BX_XMLRPC_PROTOCOL_VER,
                    );

                }

            }          
        }

        /**
         * Logged profile
         *
         * @param $iProfileId integer
         * @param $sPassword string
         * @param $sCallbackUrl
         * @param $bRedirect boolean
         * @return void
         */
        function setLogged($iProfileId, $sPassword, $sCallbackUrl = '', $bRedirect = true)
        {
            bx_login($iProfileId);
            $GLOBALS['logged']['member'] = true;

            if($bRedirect) {
                $sCallbackUrl = $sCallbackUrl
                    ? $sCallbackUrl
                    : $this -> _oConfig -> sDefaultRedirectUrl;

                header('location: ' . $sCallbackUrl);
            }
        }

        /**
         * get profile's alternative nickname
         *
         * @param $sNickName string
         * @return string
         */
        function getAlternativeName($sNickName)
        {
            $sRetNickName = '';
            $iIndex = 0;

            //-- get new allternative nickname --//
            do {
                $sPostfix = $iIndex
                    ? $this -> _oConfig -> sFaceBookAlternativePostfix . $iIndex
                    : $this -> _oConfig -> sFaceBookAlternativePostfix;

                if( !getID($sNickName . $sPostfix) ) {
                    $sRetNickName = $sPostfix;
                }

                $iIndex++;

            } while ($sRetNickName == '');

            //--

            return $sRetNickName;
        }

		/**
         * get 'join after payment' page
         *
         * @param $aProfileInfo array FaceBook profile info
         * @return void
         */
        function getJoinAfterPaymentPage($aProfileInfo)
		{
			if(!BxDolService::call('membership', 'is_disable_free_join')) 
				return;

			bx_import('BxDolSession');
			$oSession = BxDolSession::getInstance();
			$oSession->setValue($this->_oConfig->sFacebookSessionProfile, $aProfileInfo);

			header('Location: ' . BX_DOL_URL_ROOT . 'join.php');
			exit;
		}

        function processJoinAfterPayment(&$oAlert)
        {
        	bx_import('BxDolSession');
			$oSession = BxDolSession::getInstance();

			$aFacebookSessionProfile = $oSession->getValue($this->_oConfig->sFacebookSessionProfile);
			if(empty($aFacebookSessionProfile) || !is_array($aFacebookSessionProfile))
				return;

			$oSession->unsetValue($this->_oConfig->sFacebookSessionProfile);

			$aResult = $this->serviceLogin($aFacebookSessionProfile);
			if(!empty($aResult['error'])) {
				$oAlert->aExtras['override_error'] = $aResult['error'];
				return;
			}

			$oAlert->aExtras['override'] = true; // should be set to TRUE which means that JOIN will be processed using this module
			header('location: ' . $this->_getRedirectUrl((int)$aResult['member_id'], isset($aResult['existing_profile']) && $aResult['existing_profile']));
            exit;
        }

        /**
         * Assign avatar to user
         *
         * @param $sAvatarUrl string
         * @return void
         */
        function _assignAvatar($sAvatarUrl, $iProfileId = false)
        {
            if (!$iProfileId)
                $iProfileId = getLoggedId();

            if( BxDolInstallerUtils::isModuleInstalled('avatar') ) {
                BxDolService::call ('avatar', 'make_avatar_from_image_url', array($sAvatarUrl));
            }

            if (BxDolRequest::serviceExists('photos', 'perform_photo_upload', 'Uploader')) {
                $sTmpFile = tempnam($GLOBALS['dir']['tmp'], 'bxfb');
                if (false !== file_put_contents($sTmpFile, bx_file_get_contents($sAvatarUrl))) {
                    $aFileInfo = array (
                        'medTitle' => _t('_sys_member_thumb_avatar'),
                        'medDesc' => _t('_sys_member_thumb_avatar'),
                        'medTags' => _t('_ProfilePhotos'),
                        'Categories' => array(_t('_ProfilePhotos')),
                        'album' => str_replace('{nickname}', getUsername($iProfileId), getParam('bx_photos_profile_album_name')),
                        'albumPrivacy' => BX_DOL_PG_ALL,
                    );
                    BxDolService::call('photos', 'perform_photo_upload', array($sTmpFile, $aFileInfo, false), 'Uploader');
                    @unlink($sTmpFile);
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
            if(!$this -> _oConfig -> bAutoFriends) {
                return;
            }

            try {
                //get friends from facebook
                $oFriendsResponse = $this -> oFacebook -> get('/me/friends?limit=50');
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                return;
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                return;
            }

            // paginate through the result
            $oPagesEdge = $oFriendsResponse->getGraphEdge();
            do {
                foreach ($oPagesEdge as $oPage) {
                    $aFriend = $oPage->asArray();

                    $iFriendId = $this -> _oDb -> getProfileId($aFriend['id']);
                    if($iFriendId && !is_friends($iProfileId, $iFriendId) ) {
                        //add to friends list
                        $this -> _oDb -> makeFriend($iProfileId, $iFriendId);

                        //create system alert
                        $oZ = new BxDolAlerts('friend', 'accept', $iProfileId, $iFriendId);
                        $oZ -> alert();
                    }
                }
            } while ($oPagesEdge = $this -> oFacebook -> next($oPagesEdge));

        }

        /**
         * Create new profile;
         *
         * @param  : $aProfileInfo (array) - some profile's information;
         *          @see : $this -> aFacebookProfileFields;
         *
         * @param  : $sAlternativeName (string) - profiles alternative nickname;
         */
        function _createProfile($aProfileInfo, $sAlternativeName = '')
        {
            $mixed = $this->_createProfileRaw($aProfileInfo, $sAlternativeName);

            //display error
            if(is_string($mixed)) {
				$this -> _oTemplate -> getPage( _t('_bx_facebook'), MsgBox($mixed) );
                exit;                
            } 

            //display join page
            if(is_array($mixed) && isset($mixed['join_page_redirect'])) {
				$this -> _getJoinPage($mixed['profile_fields'], $mixed['profile_info_fb']['id']);
				exit;
            } 

            //continue profile creation
            if(is_array($mixed) && isset($mixed['profile_id'])) {
                $iProfileId = (int)$mixed['profile_id'];
                $sMemberAvatar = !empty($mixed['profile_info_fb']['picture']) ? $mixed['profile_info_fb']['picture'] : '';

                //redirect to avatar page
                if ($this->_oConfig->sRedirectPage == 'avatar' && !$mixed['existing_profile'] && BxDolInstallerUtils::isModuleInstalled('avatar')) {
                    // check profile's logo;
					if($sMemberAvatar)
                    	BxDolService::call('avatar', 'set_image_for_cropping', array($iProfileId, $sMemberAvatar));

					if(BxDolService::call('avatar', 'join', array($iProfileId, '_Join complete')))
						exit;
                } 
                else {
                    if($sMemberAvatar && !$mixed['existing_profile'])
						$this->_assignAvatar($sMemberAvatar);

                    //redirect to other page
                    header('location:' . $this->_getRedirectUrl($iProfileId, $mixed['existing_profile']));
                    exit;
                }
            }

			$this -> _oTemplate -> getPage( _t('_bx_facebook'), MsgBox(_t('_Error Occured')) );
			exit;
        }

        /**
         * Create new profile;
         *
         * @param  : $aProfileInfo (array) - some profile's information;
         *          @see : $this -> aFacebookProfileFields;
         *
         * @param  : $sAlternativeName (string) - profiles alternative nickname;
         * @return : error string or error or profile info array on success
         */
        function _createProfileRaw($aProfileInfo, $sAlternativeName = '', $isAutoFriends = true, $isSetLoggedIn = true)
        {
            $sCountry = '';
            $sCity = '';

            //-- join by invite only --//
            if ( getParam('reg_by_inv_only') == 'on' && (!isset($_COOKIE['idFriend']) ||  getID($_COOKIE['idFriend']) == 0) )
                return _t('_registration by invitation only');
            //--

            // process the date of birth;
            if( isset($aProfileInfo['birthday']) ) {
                $aProfileInfo['birthday'] = isset($aProfileInfo['birthday'])
                    ?  date('Y-m-d', strtotime($aProfileInfo['birthday']) )
                    :  '';
            }

            // generate new password for profile;
            $sNewPassword = genRndPwd();
            $sPasswordSalt =  genRndSalt();

            $aProfileInfo['password'] = encryptUserPwd($sNewPassword,$sPasswordSalt);

            //-- define user's country and city --//

            $aLocation = array();

            if( isset($aProfileInfo['location']['name']) ) {
                $aLocation = $aProfileInfo['location']['name'];
            } else if( isset($aProfileInfo['hometown']['name']) ) {
                $aLocation = $aProfileInfo['hometown']['name'];
            }

            if($aLocation) {
                $aCountryInfo = explode(',', $aLocation);
                $sCountry = $this -> _oDb -> getCountryCode( trim($aCountryInfo[1]) );
                $sCity = trim($aCountryInfo[0]);

                //set default country name, especially for American brothers
                if($sCity && !$sCountry) {
                    $sCountry = $this -> _oConfig -> sDefaultCountryCode;
               }
            }

            //--

            //try define the user's email
            $sEmail = !empty($aProfileInfo['email'])
                ? $aProfileInfo['email']
                : $aProfileInfo['proxied_email'];

            //-- fill array with all needed values --//
            $aProfileFields = array(
                'NickName'      		=> $aProfileInfo['nick_name'] . $sAlternativeName,
                'Email'         		=> $sEmail,
                'Sex'           		=> isset($aProfileInfo['gender']) ? $aProfileInfo['gender'] : '',
                'DateOfBirth'   		=> $aProfileInfo['birthday'],

                'Password'      		=> $aProfileInfo['password'],

                'FullName'				=> (isset($aProfileInfo['first_name']) ? $aProfileInfo['first_name'] : '') . (isset($aProfileInfo['last_name']) ? ' ' . $aProfileInfo['last_name'] : ''),

                'DescriptionMe' 		=> clear_xss(isset($aProfileInfo['bio']) ? $aProfileInfo['bio'] : ''),
                'Interests'     		=> isset($aProfileInfo['interests']) ? $aProfileInfo['interests'] : '',

                'Religion'      		=> isset($aProfileInfo['religion']) ? $aProfileInfo['religion'] : '',
                'Country'       		=> $sCountry,
                'City'       			=> $sCity,
            );
            //--


            bx_import('BxDolStopForumSpam');
            $oBxDolStopForumSpam = new BxDolStopForumSpam();
            if (2 == getParam('ipBlacklistMode') && bx_is_ip_blocked())
                return _t('_Sorry, your IP been banned');
            elseif (('on' == getParam('sys_dnsbl_enable') && 'block' == getParam('sys_dnsbl_behaviour') && bx_is_ip_dns_blacklisted('', 'join facebook')) || $oBxDolStopForumSpam->isSpammer(array('email' => $aProfileFields['Email'], 'ip' => getVisitorIP(false)), 'join facebook'))
                return sprintf(_t('_sys_spam_detected'), BX_DOL_URL_ROOT . 'contact.php');


            // check fields existence;
            foreach($aProfileFields as $sKey => $mValue) {
                if( !$this -> _oDb -> isFieldExist($sKey) ) {
                    // (field not existence) remove from array;
                    unset($aProfileFields[$sKey]);
                }
            }

            //-- add some system values --//
            $aProfileFields['Role'] 	  = BX_DOL_ROLE_MEMBER;
            $aProfileFields['DateReg'] 	  = date( 'Y-m-d H:i:s' ); // set current date;
            $aProfileFields['Salt'] 	  = $sPasswordSalt;
            //--

            $iExistingProfileId = $this -> _oDb -> isEmailExisting($sEmail);

            //check redirect page
            if ('join' == $this -> _oConfig -> sRedirectPage && !$iExistingProfileId)
                return array('profile_info_fb' => $aProfileInfo, 'profile_fields' => $aProfileFields, 'join_page_redirect' => true);

            // create new profile;
            if ($iExistingProfileId)
                $iProfileId = $iExistingProfileId;
            else
                $iProfileId = $this -> _oDb -> createProfile($aProfileFields);

            $oProfileFields = new BxDolProfilesController();

            //remember FB uid for created member
            $this -> _oDb -> saveFbUid($iProfileId, $aProfileInfo['id']);

            // check profile status;
            if (!$iExistingProfileId) {
                if ( getParam('autoApproval_ifNoConfEmail') == 'on' ) {
                    if ( getParam('autoApproval_ifJoin') == 'on' ) {
                        $sProfileStatus = 'Active';
                        if( !empty($aProfileInfo['email']) ) {
                            $oProfileFields -> sendActivationMail($iProfileId);
                        }
                    } else {
                        $sProfileStatus = 'Approval';
                        if( !empty($aProfileInfo['email']) ) {
                            $oProfileFields -> sendApprovalMail($iProfileId);
                        }
                    }
                } else {
                    if( !empty($aProfileInfo['email']) ) {
                        $oProfileFields -> sendConfMail($iProfileId);
                        $sProfileStatus = 'Unconfirmed';
                    } else {
                        if ( getParam('autoApproval_ifJoin') == 'on' ) {
                            $sProfileStatus = 'Active';
                        } else {
                            $sProfileStatus = 'Approval';
                        }
                    }
                }

                // update profile's status;
                $this -> _oDb -> updateProfileStatus($iProfileId, $sProfileStatus);            
                $oProfileFields -> createProfileCache($iProfileId);

                if( !empty($aProfileInfo['email']) ) {
                    //-- send email notification --//
                    $oEmailTemplate = new BxDolEmailTemplates();
                    $aTemplate = $oEmailTemplate->getTemplate('t_fb_connect_password_generated', $iProfileId);
                    $aNewProfileInfo = getProfileInfo($iProfileId);

                    $aPlus = array(
                        'NickName' 	  => getNickName($aNewProfileInfo['ID']),
                        'NewPassword' => $sNewPassword,
                    );

                    sendMail( $aNewProfileInfo['Email'], $aTemplate['Subject']
                        , $aTemplate['Body'], '', $aPlus );
                }
                //--

                if (BxDolModule::getInstance('BxWmapModule'))
                    BxDolService::call('wmap', 'response_entry_add', array('profiles', $iProfileId));

                // create system event
                $oZ = new BxDolAlerts('profile', 'join', $iProfileId);
                $oZ -> alert();

            }

            bx_member_ip_store($iProfileId);

            // auto-friend members if they are already friends on Facebook
            if ($isAutoFriends)
                $this -> _makeFriends($iProfileId);

            // set logged
            if ($isSetLoggedIn) {
                $aProfile = getProfileInfo($iProfileId);
                $this -> setLogged($iProfileId, $aProfile['Password'], '', false);
            }

            return array('profile_info_fb' => $aProfileInfo, 'profile_id' => $iProfileId, 'existing_profile' => $iExistingProfileId ? true : false);
        }

         /**
         * get join page
         *
         * @param $aProfileFields array
         * @param $iFacebookUserId integer
         * @return void
         */
        function _getJoinPage($aProfileFields, $iFacebookUserId)
        {
            bx_import('BxDolSession');
            $oSession = BxDolSession::getInstance();
            $oSession -> setValue($this -> _oConfig -> sFacebookSessionUid, $iFacebookUserId);

            bx_import("BxDolJoinProcessor");

            $GLOBALS['oSysTemplate']->addJsTranslation('_Errors in join form');
            $GLOBALS['oSysTemplate']->addJs(array('join.js', 'jquery.form.min.js'));

            $oJoin = new BxDolJoinProcessor();

            //process recived fields
            foreach($aProfileFields as $sFieldName => $sValue) {
                $oJoin -> aValues[0][$sFieldName] = $sValue;
            }

            $this -> _oTemplate -> getPage( _t( '_JOIN_H' ), $oJoin->process());
            exit;
        }

        /**
         * get redirect URL
         * 
         * @param $iProfileId integer - profile ID
         * @return string redirect URL
         */
    	function _getRedirectUrl($iProfileId, $isExistingProfile = false)
        {
            if ($isExistingProfile)
                return 'index' == $this->_oConfig->sRedirectPage ? BX_DOL_URL_ROOT : BX_DOL_URL_ROOT . 'member.php';

        	$sRedirectUrl = $this->_oConfig->sDefaultRedirectUrl;

			switch($this->_oConfig->sRedirectPage) {
				case 'join':
            	case 'pedit':
                	$sRedirectUrl = BX_DOL_URL_ROOT . 'pedit.php?ID=' . (int)$iProfileId;
                    break;

				case 'avatar':
					if(BxDolInstallerUtils::isModuleInstalled('avatar') && BxDolService::call('avatar', 'join', array($iProfileId, '_Join complete')))
						exit;
					break;

				case 'index':
					$sRedirectUrl = BX_DOL_URL_ROOT;
					break;

				case 'member':
				default:
					$sRedirectUrl = BX_DOL_URL_ROOT . 'member.php';
					break;
                }

			return $sRedirectUrl;
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

        function _setAccessToken()
        {
            $oFacebookRedirectLoginHelper = $this -> oFacebook -> getRedirectLoginHelper();

            try {
                $sAccessToken = $oFacebookRedirectLoginHelper->getAccessToken();
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                return $e->getMessage();
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                return $e->getMessage();
            }

            if (!isset($sAccessToken))
                return $oFacebookRedirectLoginHelper->getError() ? $oFacebookRedirectLoginHelper->getErrorDescription() : _t('_Error occured');

            $this -> oFacebook -> setDefaultAccessToken($sAccessToken);

            return '';
        }

    }
