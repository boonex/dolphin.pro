<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');

class BxDolConnectModule extends BxDolModule
{
    function __construct($aModule)
    {
        parent::__construct($aModule);
    }

    /**
     * Generate admin page;
     *
     * @return : (text) - html presentation data;
     */
    function _actionAdministration($sOptionApiKey, $sLangSettingsTitle, $sLangInfoTitle = '', $sLangInfoText = '', $sInfoTextParam = '')
    {
        $GLOBALS['iAdminPage'] = 1;

        if (!isAdmin())
            $this->_redirect(BX_DOL_URL_ROOT);

        // get sys_option's category id;
        $iCatId = $this-> _oDb -> getSettingsCategoryId($sOptionApiKey);
        if (!$iCatId) {
            $sOptions = MsgBox( _t('_Empty') );
        } 
        else {
            bx_import('BxDolAdminSettings');
            $oSettings = new BxDolAdminSettings($iCatId);

            $mixedResult = '';
            if(isset($_POST['save']) && isset($_POST['cat']))
                $mixedResult = $oSettings -> saveChanges($_POST);

            $sOptions = $oSettings->getForm();
            if ($mixedResult !== true && !empty($mixedResult))
                $sOptions = $mixedResult . $sOptions;
        }

        $sCssStyles = $this->_oTemplate->addCss('forms_adv.css', true);

        $this->_oTemplate->pageCodeAdminStart();

        if ($sLangInfoText) {
            echo DesignBoxAdmin(_t($sLangInfoTitle), $GLOBALS['oSysTemplate']->parseHtmlByName('default_padding.html', array(
                'content' => _t($sLangInfoText, BX_DOL_URL_ROOT, $sInfoTextParam)
            )));
        }

        echo DesignBoxAdmin(_t('_Settings'), $GLOBALS['oSysTemplate']->parseHtmlByName('default_padding.html', array(
            'content' => $sCssStyles . $sOptions
        )));

        $this -> _oTemplate->pageCodeAdmin(_t($sLangSettingsTitle));
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

        if ($bRedirect) {
            $sCallbackUrl = $sCallbackUrl
                ? $sCallbackUrl
                : $this -> _oConfig -> sDefaultRedirectUrl;

            header('Location: ' . $sCallbackUrl);
        }
    }

    /**
     * get profile's alternative nickname
     *
     * @param $sNickName string
     * @return suffix to add to nickname to make it unique
     */
    function getAlternativeName($sNickName)
    {
        $sRetNickName = '';
        $iIndex = 1;

        do {

            if (!getID($sNickName . $iIndex))
                $sRetNickName = $iIndex;

            $iIndex++;

        } while ($sRetNickName == '');

        return $sRetNickName;
    }

    /**
     * get 'join after payment' page
     *
     * @param $aProfileInfo array remote profile info
     * @return void
     */
    function getJoinAfterPaymentPage($aProfileInfo)
    {
        if(!BxDolService::call('membership', 'is_disable_free_join')) 
            return;

        bx_import('BxDolSession');
        $oSession = BxDolSession::getInstance();
        $oSession->setValue($this->_oConfig->sSessionProfile, $aProfileInfo);

        header('Location: ' . BX_DOL_URL_ROOT . 'join.php');
        exit;
    }

    function processJoinAfterPayment(&$oAlert)
    {
        bx_import('BxDolSession');
        $oSession = BxDolSession::getInstance();

        $aRemoteSessionProfile = $oSession->getValue($this->_oConfig->sSessionProfile);
        if(empty($aRemoteSessionProfile) || !is_array($aRemoteSessionProfile))
            return;

        $oSession->unsetValue($this->_oConfig->sSessionProfile);

        $aResult = $this->serviceLogin($aRemoteSessionProfile);
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
            bx_import('BxDolPrivacyQuery');
            $oPrivacy = new BxDolPrivacyQuery();

            $sTmpFile = tempnam($GLOBALS['dir']['tmp'], 'bxoauth');
            if (false !== file_put_contents($sTmpFile, bx_file_get_contents($sAvatarUrl))) {
                $aFileInfo = array (
                    'medTitle' => _t('_sys_member_thumb_avatar'),
                    'medDesc' => _t('_sys_member_thumb_avatar'),
                    'medTags' => _t('_ProfilePhotos'),
                    'Categories' => array(_t('_ProfilePhotos')),
                    'album' => str_replace('{nickname}', getUsername($iProfileId), getParam('bx_photos_profile_album_name')),
                    'albumPrivacy' => $oPrivacy->getDefaultValueModule('photos', 'album_view'),
                );
                BxDolService::call('photos', 'perform_photo_upload', array($sTmpFile, $aFileInfo, false), 'Uploader');
                @unlink($sTmpFile);
            }
        }
    }

    /**
     * Create new profile;
     *
     * @param  : $aProfileInfo (array) - remote profile's information;
     *
     * @param  : $sAlternativeName (string) - profiles alternative nickname;
     */
    function _createProfile($aProfileInfo, $sAlternativeName = '')
    {
        $mixed = $this->_createProfileRaw($aProfileInfo, $sAlternativeName);

        // display error
        if (is_string($mixed)) {
            $this->_oTemplate->getPage(_t($this->_oConfig->sDefaultTitleLangKey), MsgBox($mixed));
            exit;
        } 

        // display join page
        if (is_array($mixed) && isset($mixed['join_page_redirect'])) {
            $this->_getJoinPage($mixed['profile_fields'], $mixed['remote_profile_info']['id']);
            exit;
        } 

        // continue profile creation
        if (is_array($mixed) && isset($mixed['profile_id'])) {
            $iProfileId = (int)$mixed['profile_id'];
            $sMemberAvatar = !empty($mixed['remote_profile_info']['picture']) ? $mixed['remote_profile_info']['picture'] : '';

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

        $this->_oTemplate->getPage( _t($this->_oConfig->sDefaultTitleLangKey), MsgBox(_t('_Error Occured')) );
        exit;
    }

    /**
     * @param $aProfileInfo - remote profile info
     * @param $sAlternativeName - suffix to add to NickName to make it unique
     * @return profile array info, ready for the local database
     */
    protected function _convertRemoteFields($aProfileInfo, $sAlternativeName = '')
    {
    }

    /**
     * Create new profile;
     *
     * @param  : $aProfileInfo (array) - remote profile's information;
     *
     * @param  : $sAlternativeName (string) - profiles alternative nickname;
     * @return : error string or error or profile info array on success
     */
    function _createProfileRaw($aProfileInfo, $sAlternativeName = '', $isAutoFriends = true, $isSetLoggedIn = true)
    {
        $sCountry = '';
        $sCity = '';

        // join by invite only
        if ( getParam('reg_by_inv_only') == 'on' && (!isset($_COOKIE['idFriend']) ||  getID($_COOKIE['idFriend']) == 0) )
            return _t('_registration by invitation only');


        // convert fields
        $aProfileFields = $this->_convertRemoteFields($aProfileInfo, $sAlternativeName);
        if (empty($aProfileFields['Email']))
            return _t('_Incorrect Email');

        // antispam check
        bx_import('BxDolStopForumSpam');
        $oBxDolStopForumSpam = new BxDolStopForumSpam();
        if (2 == getParam('ipBlacklistMode') && bx_is_ip_blocked())
            return _t('_Sorry, your IP been banned');
        elseif (('on' == getParam('sys_dnsbl_enable') && 'block' == getParam('sys_dnsbl_behaviour') && bx_is_ip_dns_blacklisted('', 'join oauth')) || $oBxDolStopForumSpam->isSpammer(array('email' => $aProfileFields['Email'], 'ip' => getVisitorIP(false)), 'join oauth'))
            return sprintf(_t('_sys_spam_detected'), BX_DOL_URL_ROOT . 'contact.php');


        // check fields existence;
        foreach($aProfileFields as $sKey => $mValue) {
            if( !$this->_oDb->isFieldExist($sKey) ) {
                // (field not existence) remove from array;
                unset($aProfileFields[$sKey]);
            }
        }

        // add some system values
        $sNewPassword = genRndPwd();
        $sPasswordSalt =  genRndSalt();

        $aProfileFields['Password']   = encryptUserPwd($sNewPassword, $sPasswordSalt);
        $aProfileFields['Role'] 	  = BX_DOL_ROLE_MEMBER;
        $aProfileFields['DateReg'] 	  = date( 'Y-m-d H:i:s' ); // set current date;
        $aProfileFields['Salt'] 	  = $sPasswordSalt;

        // set default privacy
        bx_import('BxDolPrivacyQuery');
        $oPrivacy = new BxDolPrivacyQuery();
        $aProfileFields['allow_view_to'] = $oPrivacy->getDefaultValueModule('profile', 'view_block');

        // check if user with the same email already exists
        $iExistingProfileId = $this->_oDb->isEmailExisting($aProfileFields['Email']);

        // check redirect page
        if ('join' == $this->_oConfig->sRedirectPage && !$iExistingProfileId)
            return array('remote_profile_info' => $aProfileInfo, 'profile_fields' => $aProfileFields, 'join_page_redirect' => true);

        // create new profile
        if ($iExistingProfileId)
            $iProfileId = $iExistingProfileId;
        else
            $iProfileId = $this->_oDb->createProfile($aProfileFields);

        $oProfileFields = new BxDolProfilesController();

        // remember remote profile id for created member
        $this ->_oDb->saveRemoteId($iProfileId, $aProfileInfo['id']);

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
            $this->_oDb->updateProfileStatus($iProfileId, $sProfileStatus);            
            $oProfileFields->createProfileCache($iProfileId);

            // send email notification
            if( !empty($aProfileInfo['email']) ) {               
                $oEmailTemplate = new BxDolEmailTemplates();
                $aTemplate = $oEmailTemplate->getTemplate($this->_oConfig->sEmailTemplatePasswordGenerated, $iProfileId);
                $aNewProfileInfo = getProfileInfo($iProfileId);

                $aPlus = array(
                    'NickName' 	  => getNickName($aNewProfileInfo['ID']),
                    'NewPassword' => $sNewPassword,
                );

                sendMail($aNewProfileInfo['Email'], $aTemplate['Subject'], $aTemplate['Body'], '', $aPlus);
            }

            // update location
            if (BxDolModule::getInstance('BxWmapModule'))
                BxDolService::call('wmap', 'response_entry_add', array('profiles', $iProfileId));

            // create system event
            $oZ = new BxDolAlerts('profile', 'join', $iProfileId);
            $oZ -> alert();

        }

        // store IP
        bx_member_ip_store($iProfileId);

        // auto-friend members if they are already friends on remote site
        if ($isAutoFriends && method_exists($this, '_makeFriends'))
            $this->_makeFriends($iProfileId);

        // set logged
        if ($isSetLoggedIn) {
            $aProfile = getProfileInfo($iProfileId);
            $this->setLogged($iProfileId, $aProfile['Password'], '', false);
        }

        return array('remote_profile_info' => $aProfileInfo, 'profile_id' => $iProfileId, 'existing_profile' => $iExistingProfileId ? true : false);
    }

     /**
      * Get join page
      *
      * @param $aProfileFields array
      * @param $iRemoteProfileId remote profile id
      * @return void
      */
    function _getJoinPage($aProfileFields, $iRemoteProfileId)
    {
        bx_import('BxDolSession');
        $oSession = BxDolSession::getInstance();
        $oSession->setValue($this->_oConfig->sSessionUid, $iRemoteProfileId);

        bx_import("BxDolJoinProcessor");

        $GLOBALS['oSysTemplate']->addJs(array('join.js', 'jquery.form.min.js'));

        $oJoin = new BxDolJoinProcessor();

        // process received fields
        foreach($aProfileFields as $sFieldName => $sValue)
            $oJoin -> aValues[0][$sFieldName] = $sValue;

        $this->_oTemplate->getPage(_t('_JOIN_H'), $this->_oTemplate->parseHtmlByName('default_padding.html', array('content' => $oJoin->process())));
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

    protected function _redirect($sUrl, $iStatus = 302)
    {
        header("Location:{$sUrl}", true, $iStatus);
        exit;
    }
}
