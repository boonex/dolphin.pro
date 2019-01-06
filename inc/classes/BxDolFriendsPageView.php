<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import( 'BxDolPageView' );
bx_import( 'BxTemplProfileGenerator' );

class BxDolFriendsPageView extends BxDolPageView
{
    // consit all necessary data for display members list ;
    var $aDisplayParameters;

    var $iProfileID;
    var $oProfile;

    // link on search profile ;
    var $oSearchProfileTmpl;

    // contains the path to the current page ;
    var $sCurrentPage ;

    var $iMemberOnlineTime;

    /**
     * @description : class constructor ;
     * @param : $sPageName          (string) - name of build page ;
     * @param : $aDisplayParameters (array) ;
                        per_page (integer) - number of elements for per page ;
                        page (integer) - current page ;
                        mode (string)  - will swith member view mode ;
                        sort (string)		- sorting parameters ;
     * @param : $iProfileID (integer) - member ID ;
    */
    function __construct($sPageName, &$aDisplayParameters, $iProfileID)
    {
        parent::__construct($sPageName);
        $this -> aDisplayParameters = &$aDisplayParameters;
        $this -> oSearchProfileTmpl = new BxTemplSearchProfile();
        $this -> sCurrentPage = 'viewFriends.php';

        // check member on line time ;
        $this -> iMemberOnlineTime = (int)getParam('member_online_time');
        $this -> iProfileID = $iProfileID;
        $this -> oProfile = new BxTemplProfileGenerator($iProfileID);
    }

    /**
    * @description : function will generate friends list ;
    * @return		: array ;
    */
    function getBlockCode_Friends()
    {
        // init some variables ;
        $sOutputHtml 	= '';
        $sEmpty 	    = '';
        $iIndex 		= 0;

        $aUsedTemplates = array (
            'browse_searched_block.html'
        );

        // lang keys ;
        $sPhotoCaption  = _t( '_With photos only' );
        $sOnlineCaption = _t( '_online only' );

        // collect the SQL parameters ;

        $aWhereParam = array();
        if ( $this -> aDisplayParameters['photos'] )
            $aWhereParam[] = 'p.`Avatar` <> 0';

        if ( $this -> aDisplayParameters['online'] )
            $aWhereParam[] = "(p.`DateLastNav` > SUBDATE(NOW(), INTERVAL " . $this -> iMemberOnlineTime . " MINUTE)) ";

        $sWhereParam = null;
        foreach( $aWhereParam AS $sValue )
            if ( $sValue )
                $sWhereParam .= ' AND ' . $sValue;

        $iTotalNum = getFriendNumber($this->iProfileID, 1, 0, $sWhereParam);

        if( !$iTotalNum ) {
            $sEmpty = MsgBox( _t('_Empty') );
        }

        $iPerPage = $this -> aDisplayParameters['per_page'];
        $iCurPage = $this -> aDisplayParameters['page'];

        $sLimitFrom = ( $iCurPage - 1 ) * $iPerPage;
        $sqlLimit = "LIMIT {$sLimitFrom}, {$iPerPage}";

        // switch member's template ;
        $sTemplateName = ($this->aDisplayParameters['mode'] == 'extended') ? 'search_profiles_ext.html' : 'search_profiles_sim.html';

        // select the sorting parameters ;
        $sSortParam = 'activity_desc';
        if ( isset($this -> aDisplayParameters['sort']) ) {
            switch($this -> aDisplayParameters['sort']) {
                case 'activity' :
                    $sSortParam = 'activity_desc';
                break;
                case 'date_reg' :
                    $sSortParam = 'date_reg_desc';
                break;
                case 'rate' :
                    $sSortParam = 'rate';
                break;
                default :
                    $this -> aDisplayParameters['sort'] = 'activity';
                break;
            }
        } else
            $this -> aDisplayParameters['sort'] = 'activity';

        $aAllFriends = getMyFriendsEx($this->iProfileID, $sWhereParam, $sSortParam, $sqlLimit);

        $aExtendedCss = array(
            'ext_css_class' => $this->aDisplayParameters['mode'] == 'extended' ? 'search_filled_block' : ''
        );
        foreach ($aAllFriends as $iFriendID => $aFriendsPrm) {
            $aMemberInfo = getProfileInfo($iFriendID);
            if($aMemberInfo['Couple']) {
                $aCoupleInfo = getProfileInfo( $aMemberInfo['Couple'] );
                $sOutputHtml .= $this -> oSearchProfileTmpl -> PrintSearhResult($aMemberInfo, $aCoupleInfo, ($iIndex % 2 ? $aExtendedCss : array()), $sTemplateName);
            } else
                $sOutputHtml .= $this -> oSearchProfileTmpl -> PrintSearhResult($aMemberInfo, array(), ($iIndex % 2 ? $aExtendedCss : array()), $sTemplateName);
            $iIndex++;
        }

        $sOutputHtml .= '<div class="clear_both"></div>';

        // work with link pagination ;
        $aGetParams = array('mode', 'iUser', 'photos_only', 'online_only');
        $sRequest = BX_DOL_URL_ROOT . 'viewFriends.php?';
        $sRequest .= bx_encode_url_params($_GET, array(), $aGetParams) . 'page={page}&per_page={per_page}&sort={sorting}';

        // gen pagination block ;
        $oPaginate = new BxDolPaginate (
            array (
                'page_url'	 => $sRequest,
                'count'		 => $iTotalNum,
                'per_page'	 => $iPerPage,
                'page'		 => $iCurPage,
                'sorting'    =>  $this -> aDisplayParameters['sort'],
            )
        );

        $sPagination = $oPaginate -> getPaginate();

        // ** GENERATE HEADER PART ;

        // gen per page block ;

        $sPerPageBlock = $oPaginate -> getPages( $iPerPage );

        // fill array with sorting params
        $aSortingParam = array(
            'activity' => _t('_Latest activity'),
            'date_reg' => _t('_FieldCaption_DateReg_View'),
        );
        if (getParam('votes')) $aSortingParam['rate'] = _t('_Rate');

        // gen sorting block ( type of : drop down ) ;

        $sSortBlock = $oPaginate -> getSorting( $aSortingParam );

        $sRequest = str_replace('{page}', '1', $sRequest);
        $sRequest = str_replace('{per_page}', $iPerPage, $sRequest);
        $sRequest = str_replace('{sorting}', $this -> aDisplayParameters['sort'], $sRequest);

        // init some visible parameters ;

        $sPhotosChecked = ($this -> aDisplayParameters['photos'])
            ? 'checked="checked"'
            : null;

        $sOnlineChecked = ($this -> aDisplayParameters['online'])
            ? 'checked="checked"'
            : null;

        // link for photos section ;

        $sPhotoLocation = $this -> getCutParam( 'photos_only',  $sRequest);

        // link for online section ;

        $sOnlineLocation = $this -> getCutParam( 'online_only',  $sRequest);

        // link for `mode switcher` ;

        $sModeLocation = $this -> getCutParam( 'mode',  $sRequest);
        $sModeLocation = $this -> getCutParam( 'per_page',  $sModeLocation);

        bx_import('BxDolMemberInfo');
         $oMemberInfo = BxDolMemberInfo::getObjectInstance(getParam('sys_member_info_thumb'));

        $sTopControls = $GLOBALS['oSysTemplate']->parseHtmlByName('browse_sb_top_controls.html', array(
            'sort_block' => $sSortBlock,
            'bx_if:show_with_photos' => array(
                'condition' => $oMemberInfo->isAvatarSearchAllowed(),
                'content' => array(
                    'photo_checked' => $sPhotosChecked,
                    'photo_location' => $sPhotoLocation,
                    'photo_caption' => $sPhotoCaption,
                )
            ),
            'online_checked' => $sOnlineChecked,
            'online_location' => $sOnlineLocation,
            'online_caption' => $sOnlineCaption,
            'per_page_block' => $sPerPageBlock
        ));

        // build template ;
        $sOutputHtml = $GLOBALS['oSysTemplate'] -> parseHtmlByName( $aUsedTemplates[0], array (
            'top_controls' => $sTopControls,
            'bx_if:show_sim_css' => array (
                'condition' => $this->aDisplayParameters['mode'] != 'extended',
                'content' => array()
            ),
            'bx_if:show_ext_css' => array (
                'condition' => $this->aDisplayParameters['mode'] == 'extended',
                'content' => array()
            ),
            'searched_data'   => $sOutputHtml,
            'pagination'	  => $sPagination,
        ));

        // build the toggle block ;
        $aToggleItems = array
        (
            '' 			=>  _t( '_Simple' ),
            'extended' 	=>	_t( '_Extended' ),
        );

        foreach( $aToggleItems AS $sKey => $sValue ) {
            $aToggleEllements[$sValue] = array (
                'href' => $sModeLocation . '&mode=' . $sKey,
                'dynamic' => true,
                'active' => ($this -> aDisplayParameters['mode'] == $sKey ),
            );
        }

        return array($sOutputHtml . $sEmpty, $aToggleEllements, array(), true);
    }

    function getBlockCode_FriendsRequests()
    {
        global $oSysTemplate;

        if($this->iProfileID != getLoggedId())
            return '';

        bx_import('BxTemplCommunicator');
        $oCommunicator = new BxTemplCommunicator(array('member_id' => $this->iProfileID));

        $oSysTemplate->addCss($oCommunicator->getCss());
        $oSysTemplate->addJs($oCommunicator->getJs());
        return $oCommunicator->getBlockCode_FriendRequests();
    }

    function getBlockCode_FriendsMutual()
    {
        if($this->iProfileID == getLoggedId())
            return '';

        return $this->oProfile->showBlockMutualFriends('', true);
    }

    /**
    * @description : function will cute the parameter from received string;
    * @param		: $aExceptNames (string) - name of unnecessary paremeter;
    * @return		: cleared string;
    */
    function getCutParam( $sExceptParam, $sString )
    {
        return preg_replace( "/(&amp;|&){$sExceptParam}=([a-z0-9\_\-]{1,})/i",'', $sString);
    }

    /**
     * Function will send count of online member's friends;
     *
     * @param  : $iMemberId (integer) - logged member's Id;
     * @param  : $iOldCount (integer) - received old count of messages (if will difference will generate message)
     * @return : (array)
                [count]     - (integer) number of new messages;
                [message]   - (string) text message ( if will have a new messages );
     */
    public static function get_member_menu_bubble_online_friends($iMemberId, $iOldCount = 0)
    {
        global $oSysTemplate, $oFunctions, $site;

        $iMemberId 		  = (int) $iMemberId;
        $iOldCount		  = (int) $iOldCount;
        $iOnlineTime      = (int) getParam( "member_online_time" );
        $iOnlineFriends   = 0;

        $aNotifyMessages  = array();
        $aFriends         = array();

        if ( $iMemberId ) {
            $sWhereCondition = " AND (p.`DateLastNav` > SUBDATE(NOW(), INTERVAL " . $iOnlineTime . " MINUTE))";
            if( null != $aFoundFriends = getMyFriendsEx($iMemberId, $sWhereCondition) ) {
                foreach($aFoundFriends as $iFriendId => $aInfo) {
                    $aFriends[] = array($iFriendId);
                }
            }

            $iOnlineFriends  = count($aFriends);
           // $aFriends = array_reverse($aFriends);

            // if have some difference;
            if ( $iOnlineFriends > $iOldCount) {
                // generate notify messages;
                for( $i = $iOldCount; $i < $iOnlineFriends; $i++) {
                    $sFriendNickName  = getNickName($aFriends[$i][0]);
                    $sProfileLink     = getProfileLink($aFriends[$i][0]);

                    $aKeys = array (
                        'sender_thumb'    => $oFunctions -> getMemberIcon($aFriends[$i][0], 'left'),
                        'profile_link'    => $sProfileLink,
                        'friend_nickname' => $sFriendNickName,
                        'key_on_line'     => _t( '_Now online' ),
                    );
                    $sMessage = $oSysTemplate -> parseHtmlByName('view_friends_member_menu_notify_window.html', $aKeys);

                    $aNotifyMessages[] = array(
                        'message' => $oSysTemplate -> parseHtmlByName('member_menu_notify_window.html', array('message' => $sMessage))
                    );
                }
            }
        }

        $aRetEval = array(
           'count'     => $iOnlineFriends,
           'messages'  => $aNotifyMessages,
        );

        return $aRetEval;
    }

    /**
     * Function will send count of member's friend requests;
     *
     * @param  : $iMemberId (integer) - logged member's Id;
     * @param  : $iOldCount (integer) - received old count of messages (if will difference will generate message)
     * @return : (array)
                [count]     - (integer) number of new messages;
                [message]   - (string) text message ( if will have a new messages );
     */
    public static function get_member_menu_bubble_friend_requests($iMemberId, $iOldCount = 0)
    {
        global $oSysTemplate, $oFunctions, $site;

        $iMemberId 		  = (int) $iMemberId;
        $iOldCount		  = (int) $iOldCount;
        $iOnlineTime      = (int) getParam( "member_online_time" );
        $iOnlineFriends   = 0;

        $aNotifyMessages  = array();
        $aFriends         = array();

        if ( $iMemberId ) {
            $sSql = "SELECT `tp`.`ID` FROM `sys_friend_list` AS `tf` LEFT JOIN `Profiles` AS `tp` ON `tf`.`ID`=`tp`.`ID` WHERE `tf`.`Profile`={$iMemberId} AND `tf`.`Check`='0' AND `tp`.`Status`='Active' ORDER BY `tf`.`When`";
            $aFriends = $GLOBALS['MySQL']->getColumn($sSql);

            $iOnlineFriends = count($aFriends);
            if($iOnlineFriends > $iOldCount)
                for($i = $iOldCount; $i < $iOnlineFriends; $i++) {
                    $aKeys = array (
                        'sender_thumb'    => $oFunctions -> getMemberIcon($aFriends[$i], 'left'),
                        'profile_link'    => getProfileLink($aFriends[$i]),
                        'friend_nickname' => getNickName($aFriends[$i]),
                        'key_on_line'     => _t( '_Now online' ),
                    );
                    $sMessage = $oSysTemplate->parseHtmlByName('view_friends_member_menu_notify_window.html', $aKeys);

                    $aNotifyMessages[] = array(
                        'message' => $oSysTemplate -> parseHtmlByName('member_menu_notify_window.html', array('message' => $sMessage))
                    );
                }
        }

        $aRetEval = array(
           'count'     => $iOnlineFriends,
           'messages'  => $aNotifyMessages,
        );

        return $aRetEval;
    }

    /**
    * Function will generate list of member's friends ;
    *
    * @param  : $iMemberId (integer) - member's Id;
    * @return : Html presentation data;
    */
    public static function get_member_menu_friends_list($iMemberId = 0)
    {
        global $oFunctions;

        $iMemberId 	 = (int) $iMemberId;
        $iOnlineTime = (int)getParam('member_online_time');

        // define the member's menu position ;
        $sExtraMenuPosition = ( isset($_COOKIE['menu_position']) )
            ? $_COOKIE['menu_position']
            : getParam( 'ext_nav_menu_top_position' );

        $aLanguageKeys = array (
            'requests'    => _t( '_Friend Requests' ),
            'online'      => _t( '_Online Friends' ),
        );

        // get all friends requests ;
        $iFriendsRequests = getFriendRequests($iMemberId) ;
        $iOnlineFriends   = getFriendNumber($iMemberId, 1, $iOnlineTime) ;

        // try to generate member's messages list ;

        $sWhereParam = "AND p.`DateLastNav` > SUBDATE(NOW(), INTERVAL " . $iOnlineTime . " MINUTE)";
        $aAllFriends = getMyFriendsEx($iMemberId, $sWhereParam, 'last_nav_desc', "LIMIT 5");
        $oModuleDb   = new BxDolModuleDb();

        $sVideoMessengerImgPath  = $GLOBALS['oSysTemplate'] -> getIconUrl('video.png');
        $sMessengerTitle = _t('_Chat');

        foreach ($aAllFriends as $iFriendID => $aFriendsPrm) {
            $aMemberInfo = getProfileInfo($iFriendID);
            $sThumb = $oFunctions -> getMemberIcon($aMemberInfo['ID'], 'none');

            $sHeadline = ( mb_strlen($aMemberInfo['UserStatusMessage']) > 40 )
                ? mb_substr($aMemberInfo['UserStatusMessage'], 0, 40) . '...'
                : $aMemberInfo['UserStatusMessage'];

            $aFriends[] = array(
                'profile_link' => getProfileLink($iFriendID),
                'profile_nick' => $aMemberInfo['NickName'],
                'profile_id'   => $iFriendID,
                'thumbnail'    => $sThumb,
                'head_line'    => $sHeadline,

                'bx_if:video_messenger' => array (
                        'condition' =>  ( $oModuleDb -> isModule('messenger') ),
                        'content'   => array(
                            'sender_id'       => $iMemberId,
                            'sender_passw'    => getPassword($iMemberId),
                            'recipient_id'    => $iFriendID,
                            'video_img_src'   => $sVideoMessengerImgPath,
                            'messenger_title' => $sMessengerTitle,
                        ),
                ),
            );
        }

         $aExtraSection = array(
            'friends_request' => $aLanguageKeys['requests'],
            'request_count'   => $iFriendsRequests,

            'ID'              => $iMemberId,
            'online_friends'  => $aLanguageKeys['online'],
            'online_count'    => $iOnlineFriends,
        );

        // fill array with needed keys ;
        $aTemplateKeys = array (
            'bx_if:menu_position_bottom' => array (
                'condition' =>  ( $sExtraMenuPosition  == 'bottom' ),
                'content'   =>  $aExtraSection,
            ),

            'bx_if:menu_position_top' => array (
                'condition' =>  ( $sExtraMenuPosition  == 'top' || $sExtraMenuPosition  == 'static' ),
                'content'   =>  $aExtraSection,
            ),

            'bx_repeat:friend_list' => $aFriends,
        );

        $sOutputCode = $GLOBALS['oSysTemplate'] -> parseHtmlByName( 'view_friends_member_menu_friends_list.html', $aTemplateKeys );
        return $sOutputCode;
    }
}
