<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPermalinks');
bx_import('BxTemplFormView');
bx_import('BxDolUserStatusView');
bx_import('BxDolModule');

class BxBaseFunctions
{
    var	$aSpecialKeys;

    function __construct()
    {
        $this -> aSpecialKeys = array('rate' => '', 'rate_cnt' => '');
    }

    function getProfileMatch( $memberID, $profileID )
    {
        $match_n = getProfilesMatch($memberID, $profileID); // impl
        return DesignProgressPos ( _t("_XX match", $match_n), $GLOBALS['oTemplConfig']->iProfileViewProgressBar, 100, $match_n );
    }

    function getProfileZodiac( $profileDate )
    {
        return ShowZodiacSign( $profileDate );
    }
    
    function getProfileViewActions( $iProfileId, $bDynamic = false )
    {
        global $oTemplConfig;
        
        $iProfileId = (int)$iProfileId;
        if (!$iProfileId)
            return '';
        $aProfileInfo = getProfileInfo($iProfileId);
        if (empty($aProfileInfo))
            return '';
        
        $iViewerId = getLoggedId();
        
        // prepare all needed keys
        $aConfig = array(
            'url' => BX_DOL_URL_ROOT,
            'anonym_mode' => '',
            'member_id' => $iViewerId,
            'member_pass' => getPassword($iViewerId),            
        );
        $aMainKeys = array(
            'cpt_edit', 'cpt_send_letter', 'cpt_fave', 'cpt_befriend', 'cpt_remove_friend', 'cpt_get_mail', 'cpt_share',
            'cpt_report', 'cpt_block', 'cpt_unblock', 
            // moderation
            'cpt_activate', 'cpt_ban', 'cpt_delete', 'cpt_delete_spam', 'cpt_feature', 'act_activate', 'act_ban', 'act_feature'
        );
        
        $aMain = array_fill_keys($aMainKeys, '');
        
        if (isMember($iViewerId))
        {
            $aMain['cpt_edit'] = _t('_EditProfile');
            $aMain['cpt_send_letter'] = _t('_SendLetter');
            $aMain['cpt_fave'] = _t('_Fave');
            $aMain['cpt_remove_fave'] = _t('_Remove Fave');
            $aMain['cpt_befriend'] = _t('_Befriend');
            $aMain['cpt_remove_friend'] = _t('_Remove friend');
            $aMain['cpt_get_mail'] = _t('_Get E-mail');
            $aMain['cpt_share'] = $this->isAllowedShare($this->_aProfile) ? _t('_Share') : '';
            $aMain['cpt_report'] = _t('_Report Spam');
            $aMain['cpt_block'] = _t('_Block');
            $aMain['cpt_unblock'] = _t('_Unblock');
        }
        
        if ((isAdmin($iViewerId) || isModerator($iViewerId)) AND $iViewerId != $iProfileId)
        {
            $sMsgKeyStart = '_adm_btn_mp_';

            // delete
            $aMain['cpt_delete'] = _t($sMsgKeyStart . 'delete');
            
            // delete spam
            $aMain['cpt_delete_spam'] = _t($sMsgKeyStart . 'delete_spammer');
            
            // activate / deactivate
            $sTypeActiv = 'activate';
            if ($aProfileInfo['Status'] == 'Active')
            {
                $sTypeActiv = 'de' . $sTypeActiv;
            }
            $aMain['cpt_activate'] = _t($sMsgKeyStart . $sTypeActiv);
            $aMain['act_activate'] = $sTypeActiv;
            
            // ban / unban
            $sTypeBan = 'ban';
            if (isLoggedBanned($aProfileInfo['ID']))
            {
                $sTypeBan = 'un' . $sTypeBan;
            }
            $aMain['cpt_ban'] = _t($sMsgKeyStart . $sTypeBan);
            $aMain['act_ban'] = $sTypeBan;
            
            // feature / unfeature
            $sTypeFeat = 'featured';
            $aMain['cpt_feature'] = _t('_Feature it');
            if ((int)$aProfileInfo['Featured'])
            {
                $sTypeFeat = 'un' . $sTypeFeat;
                $aMain['cpt_feature'] = _t('_De-Feature it');
            }
            $aMain['act_feature'] = $sTypeFeat;
        }
        
        //--- Subscription integration ---//
        $oSubscription = BxDolSubscription::getInstance();
        $sAddon = $oSubscription->getData($bDynamic);

        $aButton = $oSubscription->getButton($iViewerId, 'profile', '', $iProfileId);
        $aMain['sbs_profile_title'] = $aButton['title'];
        $aMain['sbs_profile_script'] = $aButton['script'];
        //--- Subscription integration ---//
        
        $aCheckGreet = checkAction(getLoggedId(), ACTION_ID_SEND_VKISS);
        $aMain['cpt_greet'] = $aCheckGreet[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED ? _t('_Greet') : '';
        
        $aMain = array_merge($aProfileInfo, $aConfig, $aMain);

        return $sAddon . $this->genObjectsActions($aMain, 'Profile');
    }

    function TemplPageAddComponent($sKey)
    {
        switch( $sKey ) {
            case 'something':
                return false; // return here additional components
            default:
                return false; // if you have not such component, return false!
        }
    }

    /**
    * Function will generate object's action link ;
    *
    * @param  		: $aObjectParamaters (array) contain special markers ;
    * @param  		: $aRow (array) links's info ;
    * @param  		: $sCssClass (string) additional css style ;
    * @return 		: Html presentation data ;
    */
    function genActionLink( &$aObjectParamaters, $aRow, $sCssClass = null, $sTemplateIndexActionLink = 'action')
    {
        // ** init some needed variables ;
        $sOutputHtml = null;

        $aUsedTemplate = array (
            'action' => 'action_link.html',
            'action_symbol' => 'action_link_symbol.html',
            'action_submenu' => 'action_link_submenu.html',
            'action_submenu_symbol' => 'action_link_submenu_symbol.html',
        );

        // find and replace all special markers ;
        foreach( $aRow AS $sKeyName => $sKeyValue ) {
            if ( $sKeyName == 'Caption' ) {
                $aRow[$sKeyName] =  $this -> markerReplace($aObjectParamaters, $sKeyValue, $aRow['Eval'], true);
            } else {
                $aRow[$sKeyName] =  $this -> markerReplace($aObjectParamaters, $sKeyValue, $aRow['Eval']);
            }
        }

        $sKeyValue = trim($sKeyValue, '{}');

        if ( array_key_exists($sKeyValue, $this -> aSpecialKeys) ) {
            return $aRow['Eval'];
        } else {
            $sSiteUrl = (preg_match("/^(http|https|ftp|mailto)/", $aRow['Url'])) ? '' : BX_DOL_URL_ROOT;
            // build the link components ;
            //$sLinkSrc = (!$aRow['Script']) ? $aRow['Url'] : 'javascript:void(0)';

            $sScriptAction = ( $aRow['Script'] ) ? ' onclick="' . $aRow['Script'] . '"' : '';
            $sScriptAction = ($sScriptAction=='' && $aRow['Url']!='') ? " onclick=\"window.open ('{$sSiteUrl}{$aRow['Url']}','_self');\" " : $sScriptAction;

            if (false === strpos($aRow['Icon'], '.')) {
                $sIcon = $aRow['Icon'];
                $sTmpl = $sTemplateIndexActionLink . '_symbol';
            } else {
                $sIcon = getTemplateIcon($aRow['Icon']);
                $sTmpl = $sTemplateIndexActionLink;
            }

            if ( $aRow['Caption'] and ($aRow['Url'] or $aRow['Script'] ) ) {

                $sCssClass = ( $sCssClass ) ? 'class="' . $sCssClass . '"' :  null;

                $aTemplateKeys = array (
                    'action_img_alt' => $aRow['Caption'],
                    'action_img_src' => $sIcon,
                    'action_caption' => $aRow['Caption'],
                	'action_caption_attr' => bx_html_attribute($aRow['Caption']),
                    'extended_css' => $sCssClass,
                    'extended_action' => $sScriptAction,
                );

                $sOutputHtml .= $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplate[$sTmpl], $aTemplateKeys );
            }
        }

        return $sOutputHtml;
    }

    /**
     * Function will parse and replace all special markers ;
     *
     * @param $aMemberSettings (array) : all available member's information
     * @param $sTransformText (text) : string that will to parse
     * @param $bTranslate (boolean) : if isset this param - script will try to translate it used dolphin language file
     * @return (string) : parsed string
    */
    function markerReplace( &$aMemberSettings, $sTransformText, $sExecuteCode = null, $bTranslate = false )
    {
        $aMatches = array();
        preg_match_all( "/([a-z0-9\-\_ ]{1,})|({([^\}]+)\})/i", $sTransformText, $aMatches );
        if ( is_array($aMatches) and !empty($aMatches) ) {
            // replace all founded markers ;
            foreach( $aMatches[3] as $iMarker => $sMarkerValue ) {
                if( is_array($aMemberSettings) and array_key_exists($sMarkerValue, $aMemberSettings) and !array_key_exists($sMarkerValue, $this -> aSpecialKeys) ){
                    $sTransformText = str_replace( '{' . $sMarkerValue . '}', $aMemberSettings[$sMarkerValue],  $sTransformText);
                } 
                else if(($sMarkerValue == 'evalResult' || substr($sMarkerValue, 0, 10) == 'evalResult')) {
                    $sExecuteResult = '';
                    if(!empty($sExecuteCode)) {
                        $sExecuteCode = $this -> markerReplace($aMemberSettings, $sExecuteCode);
                        $sExecuteResult = eval($sExecuteCode);

                        /*
                         * Custom keys like 'evalResult...' must be taken from EvalResult array only. 
                         * It's needed to correctly serve old EvalResult strings.
                         */
                        if(is_array($sExecuteResult))
                            $sExecuteResult = isset($sExecuteResult[$sMarkerValue]) ? $sExecuteResult[$sMarkerValue] : '';
                        else if($sMarkerValue != 'evalResult')
                            $sExecuteResult = '';
                    }

                    $sTransformText =  str_replace( '{' . $sMarkerValue . '}', $sExecuteResult,  $sTransformText);
                } else {
                    //  if isset into special keys ;
                    if ( array_key_exists($sMarkerValue, $this -> aSpecialKeys) ) {
                        return $aMemberSettings[$sMarkerValue];
                    } else {
                        // undefined keys
                        switch ($sMarkerValue) {
                        }
                    }
                }
            }

            // try to translate item ;
            if ( $bTranslate ) {
                foreach( $aMatches[1] as $iMarker => $sMarkerValue ) 
                    if ( $sMarkerValue )
                        $sTransformText = str_replace( $sMarkerValue , _t( trim($sMarkerValue) ),  $sTransformText);
            }
        }

        return $sTransformText;
    }

    function msgBox($sText, $iTimer = 0, $sOnTimer = "")
    {
        $iId = time() . mt_rand(1, 1000);

        return $GLOBALS['oSysTemplate']->parseHtmlByName('messageBox.html', array(
            'id' => $iId,
            'msgText' => $sText,
            'bx_if:timer' => array(
                'condition' => $iTimer > 0,
                'content' => array(
                    'id' => $iId,
                    'time' => 1000 * $iTimer,
                    'on_timer' => bx_js_string($sOnTimer, BX_ESCAPE_STR_QUOTE),
                )
            )
        ));
    }
    
    function isAllowedShare(&$aDataEntry)
    {
    	if($aDataEntry['allow_view_to'] != BX_DOL_PG_ALL)
    		return false;
        return true;
    }

    function loadingBoxInline($sName = '')
    {
    	return $this->loadingBox($sName, 'sys-loading-inline');
    }
    
    function loadingBox($sName = '', $sClass =  '')
    {
        return $GLOBALS['oSysTemplate']->parseHtmlByName('loading.html', array(
        	'class' => !empty($sClass) ? ' ' . $sClass : '',
        	'bx_if:show_name' => array(
        		'condition' => !empty($sName),
        		'content' => array(
        			'name' => $sName
       			)
        	),
            
        ));
    }

    /**
     * Get standard popup box.
     *
     * @param  string $sTitle   - translated title
     * @param  string $sContent - content of the box
     * @param  array  $aActions - an array of actions. See an example below.
     * @return string HTML of Standard Popup Box
     *
     * @see Example of actions
     *      $aActions = array(
     *          'a1' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript: changeType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'post_text.png', 'title' => _t('_title_a1'), 'active' => 1),
     *          'a2' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript: changeType(this)', 'class' => 'wall-ptype-ctl', 'icon' => 'post_text.png', 'title' => _t('_title_a2'))
     *      );
     */
    function popupBox($sName, $sTitle, $sContent, $aActions = array())
    {
        $iId = !empty($sName) ? $sName : time();

        $aButtons = array();
        foreach($aActions as $sId => $aAction)
            $aButtons[] = array(
                'id' => $sId,
                'title' => htmlspecialchars_adv(_t($aAction['title'])),
                'class' => isset($aAction['class']) ? ' class="' . $aAction['class'] . '"' : '',
                'icon' => isset($aAction['icon']) ? '<img src="' . $aAction['icon'] . '" />' : '',
                'href' => isset($aAction['href']) ? ' href="' . htmlspecialchars_adv($aAction['href']) . '"' : '',
                'target' => isset($aAction['target'])  ? ' target="' . $aAction['target'] . '"' : '',
                'on_click' => isset($aAction['onclick']) ? ' onclick="' . $aAction['onclick'] . '"' : '',
                'bx_if:hide_active' => array(
                    'condition' => !isset($aAction['active']) || $aAction['active'] != 1,
                    'content' => array()
                ),
                'bx_if:hide_inactive' => array(
                    'condition' => isset($aAction['active']) && $aAction['active'] == 1,
                    'content' => array()
                )
            );

        return $GLOBALS['oSysTemplate']->parseHtmlByName('popup_box.html', array(
            'id' => $iId,
            'title' => $sTitle,
            'bx_repeat:actions' => $aButtons,
            'content' => $sContent
        ));
    }

    function transBox($content, $isPlaceInCenter = false)
    {
        return
            ($isPlaceInCenter ? '<div class="login_ajax_wrap">' : '') .
                $GLOBALS['oSysTemplate']->parseHtmlByName('transBox.html', array('content' => $content)) .
            ($isPlaceInCenter ? '</div>' : '');
    }

    /**
    * @description : function will generate the sex icon ;
    * @param 		: $sSex (string) - sex name ;
    * @return 		: (text) - path to image ;
    */
    function genSexIcon($sSex)
    {
        switch( $sSex ) {
            case 'male'	:
                return getTemplateIcon( 'male.png' );
            case 'female' :
                return getTemplateIcon( 'female.png' );
            case 'men'	:
                return getTemplateIcon( 'male.png' );
            default :
                return getTemplateIcon( 'tux.png' );
        }
    }

    function getSexPic($sSex, $sType = 'medium')
    {
        $aGenders = array (
            'female' => 'woman_',
            'Female' => 'woman_',
            'male' => 'man_',
            'Male' => 'man_',
        );
        return getTemplateIcon(isset($aGenders[$sSex]) ? $aGenders[$sSex] . $sType . '.gif' : 'visitor_' . $sType . '.gif');
    }

    function getMemberAvatar($iId, $sType = 'medium', $bRetina = false)
    {
    	$sThumbSetting = getParam($sType == 'small' ? 'sys_member_info_thumb_icon' : 'sys_member_info_thumb');

        $aProfile = getProfileInfo($iId);

        bx_import('BxDolMemberInfo');
        $o = BxDolMemberInfo::getObjectInstance($sThumbSetting . ($bRetina ? '_2x' : ''));
        return $o ? $o->get($aProfile) : '';
    }

    function getMemberThumbnail($iId, $sFloat = 'none', $bGenProfLink = false, $sForceSex = 'visitor', $isAutoCouple = true, $sType = 'medium', $aOnline = array(), $sTmplSfx = '')
    {
        $bForceSexSite = $bForceSexVacant = false;
        if(!$bGenProfLink) {
            if($sForceSex == 'site')
                $bForceSexSite = true;
            else if($sForceSex != 'visitor')
                $bForceSexVacant = true;
        }

        $bProfile = false;
        $aProfile = array();
        if(!$bForceSexSite) {
            $aProfile = getProfileInfo($iId);
            if(!$aProfile)
                return '';

            $bProfile = true;
        }

        $bCouple = $bThumb = $bThumbCouple = false;
        $sThumbUrl = $sThumbUrlTwice = '';
        $sThumbUrlCouple = $sThumbUrlCoupleTwice = '';
        $sUserTitle = $sUserTitleCouple = $sUserLink = $sUserInfo = $sUserStatusIcon = $sUserStatusTitle = '';

        if($bProfile) {
            $oUserStatusView = bx_instance('BxDolUserStatusView');
            $sUserStatusIcon = $oUserStatusView->getStatusIcon($iId, 'icon8');
            $sUserStatusTitle = $oUserStatusView->getStatus($iId);

            $sUserLink = getProfileLink($iId);
            $sUserTitle = $this->getUserTitle($iId);
            $sUserInfo = $this->getUserInfo($iId);

            $sThumbSetting = getParam($sType == 'small' ? 'sys_member_info_thumb_icon' : 'sys_member_info_thumb');       

            //--- get first person thumbs
            bx_import('BxDolMemberInfo');
            $o = BxDolMemberInfo::getObjectInstance($sThumbSetting);
            $sThumbUrl = $o ? $o->get($aProfile) : '';

            if(!empty($sThumbUrl)) {
                $o = BxDolMemberInfo::getObjectInstance($sThumbSetting . '_2x');
                $sThumbUrlTwice = $o ? $o->get($aProfile) : '';
                if(!$sThumbUrlTwice)
                    $sThumbUrlTwice = $sThumbUrl;
            }

            $bThumb = !empty($sThumbUrl) && !empty($sThumbUrlTwice);

            if((int)$aProfile['Couple'] > 0 && $isAutoCouple) {
                $bCouple = true;
                $aProfileCouple = getProfileInfo($aProfile['Couple']);

                $sUserTitleCouple = $this->getUserTitle($aProfile['Couple']);

                //--- get second person thumbs
                $o = BxDolMemberInfo::getObjectInstance($sThumbSetting);
                $sThumbUrlCouple = $o ? $o->get($aProfileCouple) : '';

                if(!empty($sThumbUrlCouple)) {
                    $o = BxDolMemberInfo::getObjectInstance($sThumbSetting . '_2x');
                    $sThumbUrlCoupleTwice = $o ? $o->get($aProfileCouple) : '';
                    if(!$sThumbUrlCoupleTwice)
                        $sThumbUrlCoupleTwice = $sThumbUrlCouple;
                }

                $bThumbCouple = !empty($sThumbUrlCouple) && !empty($sThumbUrlCoupleTwice);
            }
        }

        if($bForceSexSite) {
            $sUserTitle = getParam('site_title');
            $sUserLink = BX_DOL_URL_ROOT;
        }
        else if($bForceSexVacant) {
            $sUserTitle = _t('_Vacant');
            $sUserLink = 'javascript:void(0)';
        }

        return $GLOBALS['oSysTemplate']->parseHtmlByName($bCouple ? 'thumbnail_couple' . $sTmplSfx . '.html' : 'thumbnail_single' . $sTmplSfx . '.html', array(
            'iProfId' => $bProfile ? $iId : 0,
            'sys_thb_float' => 'tbf_' . $sFloat,
            'classes_add' => ($bGenProfLink ? ' thumbnail_block_with_info' : '') . ($sType != 'medium' ? ' thumbnail_block_icon' : ''),
            'sys_status_icon' => $sUserStatusIcon,
            'sys_status_title' => $sUserStatusTitle,
            'usr_profile_url' => $sUserLink,
        	'bx_if:show_thumbnail_image1' => array(
                    'condition' => $bThumb,
                    'content' => array(
                        'usr_thumb_url0' => $sThumbUrl,
                        'usr_thumb_url0_2x' => $sThumbUrlTwice,
                        'usr_thumb_alt0' => bx_html_attribute($sUserTitle),
                    )
        	),
        	'bx_if:show_thumbnail_image2' => array(
                    'condition' => $bThumbCouple,
                    'content' => array(
                        'usr_thumb_url1' => $sThumbUrlCouple,
                        'usr_thumb_url1_2x' => $sThumbUrlCoupleTwice,
                        'usr_thumb_alt1' => bx_html_attribute($sUserTitleCouple),
                    )
        	),
        	'bx_if:show_thumbnail_letter1' => array(
        		'condition' => !$bThumb,
        		'content' => array(
        			'letter' => mb_substr($sUserTitle, 0, 1)
        		)
        	),
        	'bx_if:show_thumbnail_letter2' => array(
        		'condition' => !$bThumbCouple,
        		'content' => array(
        			'letter' => mb_substr($sUserTitleCouple, 0, 1)
        		)
        	),
            'usr_thumb_title0' => $sUserTitle,
            'bx_if:profileLink' => array(
                'condition' => $bGenProfLink,
                'content' => array(
                    'user_title' => $sUserTitle,
                    'user_info' => $sUserInfo,
                    'usr_profile_url' => $sUserLink,
                ),
            ),
        ));
    }

    function getMemberIcon($iId, $sFloat = 'none', $bGenProfLink = false, $sTmplSfx = '')
    {
        return $this->getMemberThumbnail($iId, $sFloat, $bGenProfLink, 'visitor', false, 'small', array(), $sTmplSfx);
    }

    /**
     * Get image of the specified type by image id
     * @param $aImageInfo image info array with the following info
     *          $aImageInfo['Avatar'] - photo id, NOTE: it not relatyed to profiles avataras module at all
     * @param $sImgType image type
     */
    function _getImageShared($aImageInfo, $sType = 'thumb')
    {
        return BxDolService::call('photos', 'get_image', array($aImageInfo, $sType), 'Search');
    }

    function getTemplateIcon($sName)
    {
        $sUrl = $GLOBALS['oSysTemplate']->getIconUrl($sName);
        return !empty($sUrl) ? $sUrl : $GLOBALS['oSysTemplate']->getIconUrl('spacer.gif');
    }

    function getTemplateImage($sName)
    {
        $sUrl = $GLOBALS['oSysTemplate']->getImageUrl($sName);
        return !empty($sUrl) ? $sUrl : $GLOBALS['oSysTemplate']->getImageUrl('spacer.gif');
    }

    /**
     * @description : function will generate object's action lists;
     * @param : $aKeys        (array)  - array with all nedded keys;
     * @param : $sActionsType (string) - type of actions;
     * @param : $iDivider     (integer) - number of column;
     * @return:  HTML presentation data;
    */
    function genObjectsActions( &$aKeys,  $sActionsType, $bSubMenuMode = false, $sTemplateIndex = 'actions', $sTemplateIndexActionLink = 'action' )
    {
        // ** init some needed variables ;
        $sActionsList 	= null;
        $sResponceBlock = null;

        $aUsedTemplate	= array (
            'actions_submenu' => 'member_actions_list_submenu.html',
            'actions' => 'member_actions_list.html',
            'ajaxy_popup' => 'ajaxy_popup_result.html',
        );

        // read data from cache file ;
        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        $aActions = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_objects_actions'));

        // if cache file empty - will read from db ;
        if (null === $aActions || empty($aActions[$sActionsType]) ) {

            $sQuery  = 	"
                SELECT
                    `Caption`, `Icon`, `Url`, `Script`, `Eval`, `bDisplayInSubMenuHeader`
                FROM
                    `sys_objects_actions`
                WHERE
                    `Type` = '{$sActionsType}'
                ORDER BY
                    `Order`
            ";

            $rResult = db_res($sQuery);
            while ( $aRow = $rResult->fetch() ) {
                $aActions[$sActionsType][] = $aRow;
            }

            // write data into cache file ;
            if ( is_array($aActions[$sActionsType]) and !empty($aActions[$sActionsType]) ) {
                $oCache->setData ($GLOBALS['MySQL']->genDbCacheKey('sys_objects_actions'), $aActions);
            }
        }
        // ** generate actions block ;

        // contain all systems actions that will procces by self function ;
        $aCustomActions = array();
        if ( is_array($aActions[$sActionsType]) and !empty($aActions[$sActionsType]) ) {

            // need for table's divider ;
            $iIndex = 0;
            foreach( $aActions[$sActionsType] as  $aRow ) {
                if ($bSubMenuMode && $aRow['bDisplayInSubMenuHeader']==0)
                	continue;

                // generate action's link ;
                $sActionLink = $this -> genActionLink( $aKeys, $aRow, 'menuLink', $sTemplateIndexActionLink );

                if ( $sActionLink ) {
                    $sActionLinkClass = 'actionItem' . ($iIndex % 2 == 0 ? 'Even' : 'Odd') . ' {evalResultCssClassWrapper}';
                    $sActionLinkClass = $this -> markerReplace($aKeys, $sActionLinkClass, $aRow['Eval']);

                    $aActionsItem[] = array (
                    	'action_link_class' => $sActionLinkClass,
                        'action_link' => $sActionLink,
                    );

                    $iIndex++;
                }

                // it's system action ;
                if ( !$aRow['Url'] && !$aRow['Script'] ) {
                    $aCustomActions[] =  array (
                        'caption'   => $aRow['Caption'],
                        'code'      => $aRow['Eval'],
                    );
                }
            }
        }

        if ( !empty($aActionsItem) ) {

            // check what response window use ;
            // is there any value to having this template even if the ID is empty?
            if (!empty($aKeys['ID'])) {
                $sResponceBlock = $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplate['ajaxy_popup'], array('object_id' => $aKeys['ID']) );
            }

            $aTemplateKeys = array (
                'bx_repeat:actions' => $aActionsItem,
                'responce_block'    => $sResponceBlock,
            );

            $sActionsList = $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplate[$sTemplateIndex], $aTemplateKeys );
        }

        //procces all the custom actions ;
        if ($aCustomActions) {
            foreach($aCustomActions as $iIndex => $sValue ) {
                $sActionsList .= eval( $this -> markerReplace($aKeys, $aCustomActions[$iIndex]['code']) );
            }
        }

        return $sActionsList;
    }

    /**
     * alternative to GenFormWrap
     * easy to use but javascript based
     * $s - content to be centered
     * $sBlockStyle - block's style, jquery selector
     *
     * see also bx_center_content javascript function, if you need to call this function from javascript
     */
    function centerContent ($s, $sBlockStyle, $isClearBoth = true)
    {
        $sId = 'id' . time() . rand();
        return  '<div id="'.$sId.'">' . $s . ($isClearBoth ? '<div class="clear_both"></div>' : '') . '</div><script>
        	function center_' . $sId . '(sParent) {
        		var oParent = $(sParent);
        		var iAll = $(sParent + " ' . $sBlockStyle . '").size();
                var iWidthUnit = $(sParent + " ' . $sBlockStyle . ':first").outerWidth(true);
                var iWidthContainer = oParent.width();
                var iPerRow = parseInt(iWidthContainer/iWidthUnit);
                var iLeft = (iWidthContainer - (iAll > iPerRow ? iPerRow * iWidthUnit : iAll * iWidthUnit)) / 2;
                oParent.css("padding-left", iLeft);
    		}

            $(document).ready(function() {
            	var sParent = "#' . $sId . '";
                var oParent = $(sParent);
                var eImgFirst = oParent.find("img").filter(":not([src*=spacer])").first();
                if(eImgFirst.length > 0 && !eImgFirst.prop("complete"))
                	eImgFirst.load(function() {
    	    		    center_' . $sId . '(sParent);
    				});
    		    else
    				center_' . $sId . '(sParent);
            });
        </script>';
    }

    /**
     * Generates site's main logo.
     *
     * @return: HTML presentation data;
     */
    function genSiteLogo()
    {
        $sLogoUrl = $this->getLogoUrl();

        if (!$sLogoUrl)
            return '<a class="mainLogoText" href="' . BX_DOL_URL_ROOT . 'index.php' . '">' . getParam('site_title') . '</a>';

        $sLogoUrl2x = $this->getLogoUrl(true);
        $sLogoW = getParam('sys_main_logo_w');
        $sLogoH = getParam('sys_main_logo_h');

        $sStyle = $sLogoW ? " style=\"width:{$sLogoW}px; height:auto;\" " : '';
        $sLogoAttr2x = $sLogoUrl2x ? " src-2x=\"$sLogoUrl2x\" " : '';
        $sClass = 'mainLogo' . ($sLogoAttr2x ? ' bx-img-retina' : '');
        
        return '<a href="' . BX_DOL_URL_ROOT . '"><img ' . $sStyle . ' src="' . $sLogoUrl . '" ' . $sLogoAttr2x . ' class="' . $sClass . '" alt="' . bx_html_attribute(getParam('site_title')) . '" /></a>';
    }

    function getLogoUrl($bRetina = false) 
    {
        global $dir, $site;

        $sFileName = ($bRetina ? 'retina_' : '') . getParam('sys_main_logo');
        if (!$sFileName || !file_exists($dir['mediaImages'] . $sFileName))
            return '';

        return $site['mediaImages'] . $sFileName;
    }

    /**
     * Generates site's splash.
     *
     * @return: HTML presentation data;
     */
    function genSiteSplash()
    {
        $sVisibility = getParam('splash_visibility');
        $bLogged = getParam('splash_logged') == 'on';

        if($sVisibility == BX_DOL_SPLASH_VIS_DISABLE || ($sVisibility == BX_DOL_SPLASH_VIS_INDEX && !defined('BX_INDEX_PAGE')) || ($bLogged && isLogged()))
            return '';

		$sContent = $GLOBALS['oSysTemplate']->parseHtmlByContent(getParam('splash_code'), array());
		$sContent = $GLOBALS['oSysTemplate']->parseHtmlByName('splash.html', array(
			'content' => $sContent
		));

		$GLOBALS['oSysTemplate']->addJs(array('splash.js'));
		$GLOBALS['oSysTemplate']->addCss(array('splash.css'));
        return DesignBoxContent('', $sContent, 3);
    }

    /**
     * Function will generate site's search;
     *
     * @return : Html presentation data;
     */
    function genSiteSearch($sText = '')
    {
    	if(empty($sText))
    		$sText = process_line_output(_t('_Search...'));

        return $GLOBALS['oSysTemplate']->parseHtmlByName('search_header.html', array(
        	'text' => $sText
        ));
    }

    /**
     * Function will generate site's service menu;
     *
     * @return : Html presentation data;
     */
    function genSiteServiceMenu()
    {
        bx_import('BxTemplMenuService');
        $oMenu = new BxTemplMenuService();
        return '<div class="sys-service-menu-wrp bx-def-margin-sec-right bx-def-padding-right">' . $oMenu->getCode() . '</div>';
    }

    /**
     * Function will generate site's bottom menu;
     *
     * @return : Html presentation data;
     */
    function genSiteBottomMenu()
    {
        bx_import('BxTemplMenuBottom');
        $oMenu = new BxTemplMenuBottom();
        return $oMenu->getCode();
    }

    function genNotifyMessage($sMessage, $sDirection = 'left', $isButton = false, $sScript = '')
    {
        $sDirStyle = ($sDirection == 'left') ? '' : 'notify_message_none';
        switch ($sDirection) {
            case 'none': break;
            case 'left': break;
        }

        $sPossibleID = ($isButton) ? ' id="isButton" ' : '';
        $sOnClick = $sScript ? ' onclick="' . $sScript . '"' : '';

        return <<<EOF
<div class="notify_message {$sDirStyle}" {$sPossibleID} {$sOnClick}>
    <table class="notify" cellpadding=0 cellspacing=0><tr><td>{$sMessage}</td></tr></table>
    <div class="notify_wrapper_close"> </div>
</div>
EOF;
    }

    function getSiteStatBody($aVal, $sMode = '')
    {
        $sLink = strlen($aVal['link']) > 0 ? '<a href="'.BX_DOL_URL_ROOT.$aVal['link'].'">{iNum} '._t('_'.$aVal['capt']).'</a>' : '{iNum} '._t('_'.$aVal['capt']) ;
        if ( $sMode != 'admin' ) {
            $sBlockId = '';
            $iNum = strlen($aVal['query']) > 0 ? db_value($aVal['query']) : 0;
        } else {
            $sBlockId = "id='{$aVal['name']}'";
            $iNum  = strlen($aVal['adm_query']) > 0 ? db_value($aVal['adm_query']) : 0;
            if ( strlen($aVal['adm_link']) > 0 ) {
                if( substr( $aVal['adm_link'], 0, strlen( 'javascript:' ) ) == 'javascript:' ) {
                    $sHref = 'javascript:void(0);';
                    $sOnclick = 'onclick="' . $aVal['adm_link'] . '"';
                } else {
                    $sHref = $aVal['adm_link'];
                    $sOnclick = '';
                }
                $sLink = '<a href="'.$sHref.'" '.$sOnclick.'>{iNum} '._t('_'.$aVal['capt']).'</a>';
            } else {
                $sLink = '{iNum} '._t('_'.$aVal['capt']);
            }
        }

        $sLink = str_replace('{iNum}', $iNum, $sLink);
        $sImg = (false === strpos($aVal['icon'], '.') ? '<i class="sys-icon ' . $aVal['icon'] . '"></i>' : '<img src="' . getTemplateIcon($aVal['icon']) . '" alt="" />');
        $sCode =
        '
            <div class="siteStatUnit" '. $sBlockId .'>
                ' . $sImg . $sLink . '
            </div>
        ';

        return $sCode;
    }

    function genGalleryImages($aImages, $oTemplate = false)
    {
        if (!$aImages)
            return '';

        $aVars = array (
            'prefix' => $sPrefix ? $sPrefix : 'id'.time().'_'.rand(1, 999999),
            'bx_repeat:images_icons' => array (),
            'bx_repeat:icons' => array (),
        );

        $iId = 0;
        foreach ($aImages as $aImage) {
            $a = array (
                'id' => ++$iId,
                'icon_url' => $aImage['icon_url'],
                'image_url' => $aImage['image_url'],
                'title' => $aImage['title'],
            );
            $aVars['bx_repeat:images'][] = $a;
            $aVars['bx_repeat:icons'][] = $a;
        }

        if (!$oTemplate)
            $oTemplate = $GLOBALS['oSysTemplate'];

        $oTemplate->addJs('jquery.dolGalleryImages.js');
        $oTemplate->addCss('gallery_images.css');
        return $oTemplate->parseHtmlByName('gallery_images.html', $aVars);
    }

    /**
     * Generate code for system icon, depending on $sImg name it returns vector or pixel icon.
     * Vector icon is determined by missing dot sign in the name.
     *
     * @param $sImg - system icon filename, full path to custom icon, or vector icon name
     * @param $sClassAdd - add these classes to the icon
     * @param $sAlt - alt text for pixel icon or title text for vector icon
     * @param $sAttr - custom attributes string
     * @param $sImageType - pixel image type to automatically get full path to the icon: icon, image or empty string
     *
     * @return ready to use HTML code with icon, it is <img ... /> - in case of pixel icon; <i class="sys-icon ..." ...></i> - in cace of vector icon
     */
    function sysImage($sImg, $sClassAdd = '', $sAlt = '', $sAttr = '', $sImageType = false, $iSize = 16)
    {
        if (!$sImg)
            return '';
        if ($sClassAdd)
            $sClassAdd = ' ' . $sClassAdd;

        if (false === strpos($sImg, '.')) // return vector icon
            return '<i class="sys-icon ' . $sImg . $sClassAdd . '" alt="' . bx_html_attribute($sAlt) . '" ' . $sAttr . '></i>';

        // return pixel icon
        switch ($sImageType) {
            case 'icon':
                $sImg = $this->getTemplateIcon($sImg);
            break;
            case 'image':
                $sImg = $this->getTemplateImage($sImg);
            break;
        }

        return '<img src="' . $sImg . '" class="' . $sClassAdd . '" alt="' . bx_html_attribute($sAlt) . '" ' . $sAttr . ' border="0" width="' . $iSize . '" height="' . $iSize . '" />';
    }



    function getUserTitle ($iId)
    {
        $aProfile = getProfileInfo($iId);
        if (!$aProfile)
            return false;

        bx_import('BxDolMemberInfo');
        $o = BxDolMemberInfo::getObjectInstance(getParam('sys_member_info_name'));
        return $o ? $o->get($aProfile) : $aProfile['NickName'];
    }

    function getUserInfo ($iId)
    {
        $aProfile = getProfileInfo($iId);
        if (!$aProfile)
            return false;

        bx_import('BxDolMemberInfo');
        $o = BxDolMemberInfo::getObjectInstance(getParam('sys_member_info_info'));
        return $o ? $o->get($aProfile) : '';
    }

	function getLanguageSwitcher($sCurrent)
	{
		$sOutputCode = '';

		$aLangs = getLangsArrFull();
		if(count( $aLangs ) < 2)
			return $sOutputCode;

		$sGetTransfer = bx_encode_url_params($_GET, array('lang'));

		$aTmplVars = array();
		foreach( $aLangs as $sName => $aLang ) {
			$sFlag  = $GLOBALS['site']['flags'] . $aLang['Flag'] . '.gif';
			$aTmplVars[] = array (
				'bx_if:show_icon' => array (
					'condition' => $sFlag,
					'content' => array (
						'icon_src'      => $sFlag,
						'icon_alt'      => $sName,
						'icon_width'    => 18,
						'icon_height'   => 12,
					),
				),
				'class' => $sName == $sCurrent ? 'sys-bm-sub-item-selected' : '',
				'link'    => bx_html_attribute($_SERVER['PHP_SELF']) . '?' . $sGetTransfer . 'lang=' . $sName,
				'onclick' => '',
				'title'   => $aLang['Title'],
			);
		}

		$sOutputCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('popup_switcher.html', array(
			'name_method' => 'Language',
			'name_block' => 'language',
			'bx_repeat:items' => $aTmplVars
		));

		return PopupBox('sys-bm-switcher-language', _t('_sys_bm_popup_cpt_language'), $sOutputCode);
	}

	function getTemplateSwitcher($sCurrent)
	{
		$sOutputCode = "";

		$aTemplates = get_templates_array();
		if(count($aTemplates) < 2)
			return $sOutputCode;

		$sGetTransfer = bx_encode_url_params($_GET, array('skin'));

		$aTmplVars = array();
		foreach($aTemplates as $sName => $sTitle)
			$aTmplVars[] = array (
				'bx_if:show_icon' => array (
					'condition' => false,
					'content' => array(),
				),
				'class' => $sName == $sCurrent ? 'sys-bm-sub-item-selected' : '',
				'link' => bx_html_attribute($_SERVER['PHP_SELF']) . '?' . $sGetTransfer . 'skin=' . $sName,
				'onclick' => '',
				'title' => $sTitle
			);

		$sOutputCode .= $GLOBALS['oSysTemplate']->parseHtmlByName('popup_switcher.html', array(
			'name_method' => 'Template',
			'name_block' => 'template',
			'bx_repeat:items' => $aTmplVars
		));

		return PopupBox('sys-bm-switcher-template', _t('_sys_bm_popup_cpt_design'), $sOutputCode);
	}
}
