<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolCommunicator.php');

    class BxBaseCommunicator extends BxDolCommunicator
    {
        // contain all needed templates for Html rendering ;
        var $aUsedTemplates;

        var $sMembersFlagExtension   = '.gif';

       /**
        * Class constructor ;
        *
        * @param	: $aCommunicatorSettings (array)  - contain some necessary data ;
        * 					[ member_id	] (integer) - logged member's ID;
        * 					[ communicator_mode ] (string) - page mode ;
        * 					[ person_switcher ] (string) - switch the person mode - from me or to me ;
        * 					[ sorting ] (string) - type of message's sort ;
        * 					[ page ] (integer) - contain number of current page ;
        * 					[ per_page ] (integer) - contain per page number for current page ;
        * 					[ alert_page ] (integer) - contain number of current alert's page
        */
        function __construct($aCommunicatorSettings)
        {
            parent::__construct($aCommunicatorSettings);

            //fill array with tamplates name;
            $this -> aUsedTemplates = array (
                'communicator_page' => 'communicator_page.html',
                'communicator_page_fr' => 'communicator_page_fr.html',
                'communicator_settings' => 'communicator_settings.html',
                'communicator_settings_page' => 'communicator_page_top_settings.html',
            );
        }

        function getCss()
        {
            return array('communicator_page.css');
        }

        function getJs()
        {
            return array('communicator_page.js');
        }

        /**
         * Function will draw the 'Connections' block;
         */
        function getBlockCode_Connections()
        {
            global $oSysTemplate;

            // set default mode ;
            if(!$this -> aCommunicatorSettings['communicator_mode'])
                $this -> aCommunicatorSettings['communicator_mode'] = 'friends_list';

            // generate the top page toggle ellements ;
            $aTopToggleItems = array (
                'friends_list' =>  _t('_sys_cnts_txt_frients'),
                'hotlist_requests' => _t('_sys_cnts_txt_favorites'),
                'blocks_requests' => _t('_sys_cnts_txt_blocked')
            );

            $sRequest = BX_DOL_URL_ROOT . 'communicator.php?';
            foreach( $aTopToggleItems AS $sKey => $sValue ) {
                $aTopToggleEllements[$sValue] = array (
                    'href' => $sRequest . '&communicator_mode=' . $sKey . ($sKey != 'friends_list' ? '&person_switcher=from' : ''),
                    'dynamic' => true,
                    'active' => ($this -> aCommunicatorSettings['communicator_mode'] == $sKey ),
                );
            }

            // return processed html data;
            $sOutputHtml = $this -> getProcessingRows();

            // return generated template ;
            return array($sOutputHtml, $aTopToggleEllements, array(), true);
        }

        /**
         * Function will draw the 'Friend Requests' block;
         */
        function getBlockCode_FriendRequests($bShowEmpty = true)
        {
            global $oSysTemplate;

            // set default mode ;
            $this -> aCommunicatorSettings['communicator_mode'] = 'friends_requests';
            $this -> aCommunicatorSettings['person_switcher'] = 'to';

            // return processed html data;
            $sOutputHtml = $this -> getProcessingRows($bShowEmpty);

            $this -> aCommunicatorSettings['communicator_mode'] = '';

            if(empty($sOutputHtml))
                return '';

            // return generated template ;
            return array($sOutputHtml, array(), array(), true);
        }

        /**
         * Function will generate received rows ;
         *
         * @return  : Html presentation data ;
         */
        function getProcessingRows($bShowEmpty = true)
        {
            global $oSysTemplate, $site, $oFunctions ;

            // ** init some needed variables ;
            $sPageContent = $sActionsList = $sSettings ='';
            $bShowSettings = false;
            $aRows = array();

            $sEmptyMessage = '_Empty';
            $sRowsTemplName = $this -> aUsedTemplates['communicator_page'];
            $sJsObject = $this->_getJsObject();

            // define the member's nickname;
            $sMemberNickName  = getNickName($this -> aCommunicatorSettings['member_id']);

            // all primary language's keys ;
            $aLanguageKeys = array (
                'author'      => _t( '_Author' ),
                'type'        => _t( '_Type' ),
                'date'        => _t( '_Date' ),
                'click_sort'  => _t( '_Click to sort' ),
                'from_me'     => _t( '_From' )   . ' ' . $sMemberNickName,
                'to_me'       => _t( '_To' )     . ' ' . $sMemberNickName,
                'accept'      => _t( '_sys_cnts_btn_fr_accept' ),
                'reject'      => _t( '_sys_cnts_btn_fr_reject' ),
                'delete'      => _t( '_Delete' ),
                'back_invite' => _t( '_Back Invite' ),
                'fave'        => _t( '_sys_cnts_btn_fave' ),
                'visitor'     => _t( '_Visitor' ),
                'unblock'     => _t( '_Unblock' ),
                'block'       => _t( '_Block' ),
                'select'      => _t( '_Select' ),
                'all'         => _t( '_All' ),
                'none'        => _t( '_None' ),
                'read'        => _t( '_Read' ),
                'unread'      => _t( '_Unread' ),
            );

            // get all requests from DB ;
            switch($this -> aCommunicatorSettings['communicator_mode']) {
                case 'friends_requests' :
                    $sEmptyMessage = '_sys_cnts_msg_fr_empty';
                    $sRowsTemplName = $this -> aUsedTemplates['communicator_page_fr'];

                    $aTypes = array (
                        'from'  => _t( '_MEMBERS_INVITE_YOU_FRIENDLIST' ),
                        'to'    => _t( '_MEMBERS_YOU_INVITED_FRIENDLIST' )
                    );
                    $aRows = $this -> getRequests( 'sys_friend_list', $aTypes, ' AND `sys_friend_list`.`Check` = 0 ');
                break;

                case 'hotlist_requests' :
                    $aTypes = array
                    (
                        'from'  => _t( '_MEMBERS_YOU_HOTLISTED' ),
                        'to'    => _t( '_MEMBERS_YOU_HOTLISTED_BY' )
                    );
                    $aRows = $this -> getRequests( 'sys_fave_list', $aTypes);
                break;

                case 'greeting_requests' :
                    $aTypes = array
                    (
                        'from'          => _t( '_MEMBERS_YOU_KISSED' ),
                        'to'            => _t( '_MEMBERS_YOU_KISSED_BY' ),
                        'specific_key'  => '_N times',
                    );
                    $aRows = $this -> getRequests( 'sys_greetings', $aTypes, null, 'Number' );
                break;

                case 'blocks_requests' :
                    $aTypes = array
                    (
                        'from'          => _t( '_MEMBERS_YOU_BLOCKLISTED' ),
                        'to'            => _t( '_MEMBERS_YOU_BLOCKLISTED_BY' ),
                    );
                    $aRows = $this -> getRequests( 'sys_block_list', $aTypes );
                break;

               case 'friends_list'  :
                    $aTypes = array
                    (
                        'from'  => _t( '_Friend list' ),
                        'to'	=> _t( '_Friend list' ),
                    );
                    $aRows = $this -> getRequests( 'sys_friend_list', $aTypes,
                        ' AND `sys_friend_list`.`Check` = 1 OR ( `sys_friend_list`.`ID` = ' . $this -> aCommunicatorSettings['member_id']
                            . ' AND `sys_friend_list`.`Check` = 1 )' );
                break;

                default :
                    $aTypes = array
                    (
                        'from'  => _t( '_MEMBERS_INVITE_YOU_FRIENDLIST' ),
                        'to'    => _t( '_MEMBERS_YOU_INVITED_FRIENDLIST' )
                    );
                    $aRows = $this -> getRequests( 'sys_friend_list', $aTypes, ' AND `sys_friend_list`.`Check` = 0 ' );
            }

            if(empty($aRows) && !$bShowEmpty)
                return '';

            // ** Generate the page's pagination ;

            // fill array with all necessary `get` parameters ;
            $aNeededParameters = array( 'communicator_mode', 'person_switcher', 'sorting' );

            // collect the page's URL ;
            $sRequest = BX_DOL_URL_ROOT . 'communicator.php?action=get_page' ;

            // add additional parameters ;
            foreach( $aNeededParameters AS $sKey ) {
                $sRequest .= ( array_key_exists($sKey, $this -> aCommunicatorSettings) and $this -> aCommunicatorSettings[$sKey] )
                    ? '&' . $sKey . '=' . $this -> aCommunicatorSettings[$sKey]
                    : null ;
            }

            $sCuttedUrl = $sRequest;
            $sRequest   .=  '&page={page}&per_page={per_page}';

            // create  the pagination object ;
            $oPaginate = new BxDolPaginate (
                array (
                    'page_url'   => $sRequest,
                    'count'      => $this -> iTotalRequestsCount,
                    'per_page'   => $this -> aCommunicatorSettings['per_page'],
                    'sorting'    => $this -> aCommunicatorSettings['sorting'],
                    'page'               => $this -> aCommunicatorSettings['page'],

                    'on_change_page'     => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".getPaginatePage('{$sRequest}')",
                    'on_change_per_page' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".getPage(this.value, '{$sCuttedUrl}')",
                )
            );

            $sPagination   = $oPaginate -> getPaginate();

            // process received requests;
            if ( $aRows ) {
                $iIndex = 1;
                foreach($aRows AS $iKey => $aItems ) {
                    // if member not a visitor ;
                    if ( $aItems['member_id'] ) {
                        // ** some member's information ;
                        $aProfileInfo    = getProfileInfo ($aItems['member_id']);

                        // member's Icon ;
                        $sMemberIcon = get_member_thumbnail($aProfileInfo['ID'], 'left', ($this -> aCommunicatorSettings['communicator_mode'] != 'friends_requests'));

                        // member's profile location ;
                        $sMemberLocation = getProfileLink ($aProfileInfo['ID']);

                        // member's nickname ;
                        $sMemberNickName  = getNickName($aProfileInfo['ID']);

                        // define the member's age ;
                        $sMemberAge = ( $aProfileInfo['DateOfBirth'] != "0000-00-00" )
                            ? _t( "_y/o", age($aProfileInfo['DateOfBirth']) )
                            : null;

                        // define the member's country, sex, etc ... ;
                        $sMemberCountry =  $aProfileInfo['Country'];
                        $sMemberFlag    =  $site['flags'] . strtolower($sMemberCountry) . $this -> sMembersFlagExtension;
                        $sMemberSexImg  =  $oFunctions -> genSexIcon($aProfileInfo['Sex']);

                        if ( $sMemberCountry )
                            $sMemberCountryFlag = '<img src="' . $sMemberFlag . '" alt="' . $sMemberCountry . '" />';

                        $iMemberMutualFriends = getMutualFriendsCount($aItems['member_id'], getLoggedId());
                    } else {
                        // ** if it's a visitor

                        // member's Icon ;
                        $sMemberIcon        = $aLanguageKeys['visitor'];

                        // member's profile location ;
                        $sMemberLocation    = null;
                        $sMemberSexImg      = null;
                        $sMemberAge         = null;
                        $sMemberCountryFlag = null;
                        $sMemberCountry     = null;
                    }

                    $aProcessedRows[] = array (
                        'js_object' 	 => $sJsObject,

                        'row_value' => $aItems['member_id'],
                        'member_icon' => $sMemberIcon,
                        'member_nick_name' => $sMemberNickName,

                        // define the profile page location ;
                        'member_location' => $sMemberLocation ? '<a href="' . $sMemberLocation . '">' . $sMemberNickName . '</a>' : '',

                        // define the member's sex ;
                        'member_sex_img' => $sMemberSexImg ? ' <img src="' . $sMemberSexImg . '" alt="' . $aProfileInfo['Sex'] . '" />' : '',

                        'member_age' => $sMemberAge,
                        'member_flag' => $sMemberCountryFlag,
                        'member_country' => $sMemberCountry,

                        'member_mutual_friends' => _t('_sys_cnts_txt_mutual_friends', ($iMemberMutualFriends > 0 ? $iMemberMutualFriends : _t('_sys_cnts_txt_mf_no'))),

                        'type' => $aItems['type'],
                        'message_date' => $aItems['date'],
                    );

                    $iIndex++;
                }

                // init the sort toggle ellements ;
                switch ( $this -> aCommunicatorSettings['sorting'] ) {
                    case 'date' :
                        $aSortToglleElements['date_sort_toggle'] = 'toggle_up';
                    break;
                    case 'date_desc' :
                       $aSortToglleElements['date_sort_toggle'] = 'toggle_down';
                    break;
                    case 'author' :
                        $aSortToglleElements['author_sort_toggle'] = 'toggle_up';
                    break;
                    case 'author_desc' :
                        $aSortToglleElements['author_sort_toggle'] = 'toggle_down';
                    break;
                }

                // define the actions list for type of requests;
                switch( $this -> aCommunicatorSettings['communicator_mode'] ) {
                    case 'friends_requests':
                        // define the person mode ;
                        switch ($this -> aCommunicatorSettings['person_switcher']) {
                            case 'to' :
                                $aForm = array (
                                    'form_attrs' => array (
                                        'action' =>  null,
                                        'method' => 'post',
                                    ),

                                    'params' => array (
                                        'remove_form' => true,
                                        'db' => array(
                                            'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                                        ),
                                    ),

                                    'inputs' => array(
                                        'actions' => array(
                                            'type' => 'input_set',
                                            'colspan' => 'true',
                                            0 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['accept'],
                                                'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'accept_friends_request', 'getProcessingRows')"),
                                            ),
                                            1 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['reject'],
                                                'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'reject_friends_request', 'getProcessingRows')"),
                                            ),
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sActionsList = $oForm -> getCode();
                                break;

                            case 'from' :
                                $aForm = array (
                                    'form_attrs' => array (
                                        'action' =>  null,
                                        'method' => 'post',
                                    ),

                                    'params' => array (
                                        'remove_form' => true,
                                        'db' => array(
                                            'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                                        ),
                                    ),

                                    'inputs' => array(
                                        'actions' => array(
                                            'type' => 'input_set',
                                            'colspan' => 'true',
                                            0 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['back_invite'],
                                                'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'delete_friends_request', 'getProcessingRows')"),
                                            ),
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sActionsList = $oForm -> getCode();
                            break;
                        }
                        break;

                    case 'hotlist_requests' :
                        // define the person mode ;
                        switch ($this -> aCommunicatorSettings['person_switcher']) {
                            case 'to' :
                                $aForm = array (
                                    'form_attrs' => array (
                                        'action' =>  null,
                                        'method' => 'post',
                                    ),

                                    'params' => array (
                                        'remove_form' => true,
                                        'db' => array(
                                            'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                                        ),
                                    ),

                                    'inputs' => array(
                                        'actions' => array(
                                            'type' => 'input_set',
                                            'colspan' => 'true',
                                            0 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['fave'],
                                                'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'add_hotlist', 'getProcessingRows')"),
                                            ),
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sActionsList = $oForm -> getCode();
                                break;

                            case 'from' :
                                $aForm = array (
                                    'form_attrs' => array (
                                        'action' =>  null,
                                        'method' => 'post',
                                    ),

                                    'params' => array (
                                        'remove_form' => true,
                                        'db' => array(
                                            'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                                        ),
                                    ),

                                    'inputs' => array(
                                        'actions' => array(
                                            'type' => 'input_set',
                                            'colspan' => 'true',
                                            0 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['delete'],
                                                'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'delete_hotlisted', 'getProcessingRows')"),
                                            ),
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sActionsList = $oForm -> getCode();
                                break;
                        }
                    break;

                    case 'greeting_requests' :
                        $aForm = array (
                            'form_attrs' => array (
                                'action' =>  null,
                                'method' => 'post',
                            ),

                            'params' => array (
                                'remove_form' => true,
                                'db' => array(
                                    'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                                ),
                            ),

                            'inputs' => array(
                                'actions' => array(
                                    'type' => 'input_set',
                                    'colspan' => 'true',
                                    0 => array (
                                        'type'      => 'button',
                                        'value'     => $aLanguageKeys['delete'],
                                        'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'delete_greetings', 'getProcessingRows')"),
                                    ),
                                )
                            )
                        );

                        $oForm = new BxTemplFormView($aForm);
                        $sActionsList = $oForm -> getCode();
                        break;

                    case 'blocks_requests' :
                        // define the person mode ;
                        switch ($this -> aCommunicatorSettings['person_switcher']) {
                            case 'to' :
                                $aForm = array (
                                    'form_attrs' => array (
                                        'action' =>  null,
                                        'method' => 'post',
                                    ),

                                    'params' => array (
                                        'remove_form' => true,
                                        'db' => array(
                                            'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                                        ),
                                    ),

                                    'inputs' => array(
                                        'actions' => array(
                                            'type' => 'input_set',
                                            'colspan' => 'true',
                                            0 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['block'],
                                                'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'block_unblocked', 'getProcessingRows')"),
                                            ),
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sActionsList = $oForm -> getCode();
                                break;

                            case 'from' :
                                $aForm = array (
                                    'form_attrs' => array (
                                        'action' =>  null,
                                        'method' => 'post',
                                    ),

                                    'params' => array (
                                        'remove_form' => true,
                                        'db' => array(
                                            'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                                        ),
                                    ),

                                    'inputs' => array(
                                        'actions' => array(
                                            'type' => 'input_set',
                                            'colspan' => 'true',
                                            0 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['unblock'],
                                                'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'unblock_blocked', 'getProcessingRows')"),
                                            ),
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sActionsList = $oForm -> getCode();
                                break;
                        }
                    break;

                    case 'friends_list'  :
                        $aForm = array (
                        'form_attrs' => array (
                            'action' =>  null,
                            'method' => 'post',
                        ),

                        'params' => array (
                            'remove_form' => true,
                            'db' => array(
                                'submit_name' => 'do_submit', // some filed name with non empty value to determine if the for was submitted,
                            ),
                        ),

                        'inputs' => array(
                            'actions' => array(
                                'type' => 'input_set',
                                'colspan' => 'true',
                                0 => array (
                                    'type'      => 'button',
                                    'value'     => $aLanguageKeys['delete'],
                                    'attrs'     => array('onclick' => "if ( typeof " . $sJsObject . " != 'undefined' ) " . $sJsObject . ".sendAction(this, 'communicator_container', 'reject_friends_request', 'getProcessingRows')"),
                                ),
                            )
                        )
                    );

                    $oForm = new BxTemplFormView($aForm);
                    $sActionsList = $oForm -> getCode();
                    break;
                }

                // processing the sort link ;
                $sSortLink = getClearedParam('sorting', $sCuttedUrl) . '&page=' . $this -> aCommunicatorSettings['page']
                                . '&per_page=' . $this -> aCommunicatorSettings['per_page'] ;

                // fill array with template keys ;
                $aTemplateKeys = array (
                    'js_object' 	 => $sJsObject,
                    'from_me'        => $aLanguageKeys['from_me'],
                    'to_me'          => $aLanguageKeys['to_me'],
                    'selected_from'  => ($this -> aCommunicatorSettings['person_switcher'] == 'from') ? 'checked="checked"' : null,
                    'selected_to'    => ($this -> aCommunicatorSettings['person_switcher'] == 'to') ? 'checked="checked"' : null,

                    'page_sort_url'  => $sSortLink,
                    'sort_date'      => ( $this -> aCommunicatorSettings['sorting'] == 'date' )     ? 'date_desc'     : 'date',
                    'sort_author'    => ( $this -> aCommunicatorSettings['sorting'] == 'author' )   ? 'author_desc'   : 'author',

                    'date_sort_toggle_ellement'   => $aSortToglleElements['date_sort_toggle'],
                    'author_sort_toggle_ellement' => $aSortToglleElements['author_sort_toggle'],

                    'author'     => $aLanguageKeys['author'],
                    'type'       => $aLanguageKeys['type'],
                    'date'       => $aLanguageKeys['date'],
                    'click_sort' => $aLanguageKeys['click_sort'],

                    // contain received processed rows ;
                    'bx_repeat:rows'  => $aProcessedRows,

                    // contain current actions ;
                    'actions_list'    =>  $sActionsList,

                    'current_page'      => 'communicator.php',
                    'select'            => $aLanguageKeys['select'],
                    'all_messages'      => $aLanguageKeys['all'],
                    'none_messages'     => $aLanguageKeys['none'],
                    'read_messages'     => $aLanguageKeys['read'],
                    'unread_messages'   => $aLanguageKeys['unread'],

                    'page_pagination' => $sPagination,
                );

                $sPageContent = $oSysTemplate -> parseHtmlByName($sRowsTemplName, $aTemplateKeys);
            } else
                $sPageContent = $oSysTemplate -> parseHtmlByName('default_margin.html', array(
                    'content' => MsgBox(_t($sEmptyMessage))
                ));

            // ** Process the final template ;

            // generate the page settings ;
            if ($bShowSettings) {
                 $aTemplateKeys = array (
                     'js_object' => $sJsObject,
                    'from_me'        => $aLanguageKeys['from_me'],
                    'to_me'          => $aLanguageKeys['to_me'],
                    'selected_from'  => ($this -> aCommunicatorSettings['person_switcher'] == 'from') ? 'checked="checked"' : null,
                    'selected_to'    => ($this -> aCommunicatorSettings['person_switcher'] == 'to') ? 'checked="checked"' : null,
                );

                $sSettings = $oSysTemplate -> parseHtmlByName( $this -> aUsedTemplates['communicator_settings'], $aTemplateKeys );
            }

            // fill array with template keys ;
            $aTemplateKeys = array (
                'js_object' => $sJsObject,
                'current_page'             => 'communicator.php',
                'communicator_mode'        => $this -> aCommunicatorSettings['communicator_mode'],
                'communicator_person_mode' => $this -> aCommunicatorSettings['person_switcher'],
                'error_message'            => bx_js_string(_t( '_Please, select at least one message' )),
                'sure_message'             => bx_js_string(_t( '_Are_you_sure' )),

                'settings'       => $sSettings,

                'page_content'   => $sPageContent,
            );

            return $oSysTemplate -> parseHtmlByName( $this -> aUsedTemplates['communicator_settings_page'], $aTemplateKeys );
        }
    }
