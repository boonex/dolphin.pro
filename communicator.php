<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( 'inc/header.inc.php' );
    require_once( BX_DIRECTORY_PATH_INC  . 'design.inc.php' );
    require_once( BX_DIRECTORY_PATH_ROOT . 'templates/tmpl_' . $tmpl . '/scripts/BxTemplCommunicator.php');

    // ** init some needed variables ;

    $sOutputHtml = '';

    // contain all receivied members ID separeted by comma ;
    $sMembersList = ( isset($_POST['rows']) )
        ? $_POST['rows']
        : '';

    // array : contain all received member id ;
    $aMembersList  = array();

    // try to segregate received members list;
    if ( $sMembersList ) {
        $aMembersList  = explode(',', $sMembersList);
    }

    $iProfileId = getLoggedId();

    // contain some needed settings for the Communicator's object ;
    $aCommunicatorSettings = array (
        // logged member's ID;
        'member_id' => $iProfileId,

        // page mode ;
        'communicator_mode' => ( false !== bx_get('communicator_mode') )
            ? bx_get('communicator_mode')
            : '',

        // switch the person mode - from me or to me ;
        'person_switcher' => ( false !== bx_get('person_switcher') )
            ? bx_get('person_switcher')
            : 'to',

        // type of message's sort ;
        'sorting' => ( false !== bx_get('sorting'))
            ?  bx_get('sorting')
            : 'date_desc',

        // contain number of current page ;
        'page'	=> ( false !== bx_get('page'))
            ? (int) bx_get('page')
            : 1,

        // contain per page number for current page ;
        'per_page' => ( false !== bx_get('per_page'))
            ? (int) bx_get('per_page')
            : 10,

        // contain number of current alert's page ;
        'alert_page'  => ( false !== bx_get('alert_page'))
            ? (int)  bx_get('alert_page')
            : 1,
    );

    // create BxTemplCommunicator object ;
    $oCommunicator = new BxTemplCommunicator($aCommunicatorSettings);

    //-- ajax request processing --//
    if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] )
        && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
        && false !== bx_get('action') ) {

        // contain all the available callback functions ;
        $aCallbackFunctions = array( 'getProcessingRows' );

        switch( bx_get('action') ) {
            // just return the processed requests;
            case 'get_page'               :
               $sOutputHtml = $oCommunicator -> getProcessingRows();
            break;

            // function will set 'accept' mode for received members list ;
            case 'accept_friends_request' :
                if($aMembersList) {
                    $oCommunicator -> execFunction( '_acceptFriendInvite', 'sys_friend_list', $aMembersList );
                }
            break;

            // function will set 'reject' mode for received members list ;
            case 'reject_friends_request' :
                if($aMembersList) {
                    if ( $aCommunicatorSettings['person_switcher'] == 'from' ) {
                        $oCommunicator -> execFunction( '_deleteRequest', 'sys_friend_list', $aMembersList, array(1) );
                    } else {
                        $oCommunicator -> execFunction('_deleteRequest', 'sys_friend_list', $aMembersList, array(0, 1));
                    }
                }
            break;

            // function will delete friends list ;
            case 'delete_friends_request' :
                if($aMembersList) {
                    $oCommunicator -> execFunction( '_deleteRequest', 'sys_friend_list', $aMembersList, array(1, 1) );
                }
            break;

            // function will delete the received members from 'sys_fave_list' ;
            case 'delete_hotlisted' :
                if($aMembersList) {
                    $oCommunicator -> execFunction('_deleteRequest', 'sys_fave_list', $aMembersList, array(1));
                }
            break;

            // function will add the received members to 'sys_fave_list' ;
            case 'add_hotlist' :
                if($aMembersList) {
                    $oCommunicator -> execFunction( '_addRequest', 'sys_fave_list', $aMembersList );
                }
            break;

            // function will delete the received members from 'sys_greetings' ;
            case 'delete_greetings' :
                if($aMembersList) {
                    if ( $aCommunicatorSettings['person_switcher'] == 'from' ) {
                        $oCommunicator -> execFunction( '_deleteRequest', 'sys_greetings', $aMembersList, array(1) );
                    } else {
                        $oCommunicator -> execFunction('_deleteRequest', 'sys_greetings', $aMembersList);
                    }
                }
            break;

            // function will unblock the received members from 'sys_block_list' ;
            case 'unblock_blocked' :
                if($aMembersList) {
                    $oCommunicator -> execFunction('_deleteRequest', 'sys_block_list', $aMembersList, array(1));
                }
            break;

            // function will block the received members to 'sys_block_list' ;
            case 'block_unblocked' :
                if($aMembersList) {
                    $oCommunicator -> execFunction( '_addRequest', 'sys_block_list', $aMembersList );
                }
            break;
        }

        // try to define the callback function name ;
        if ( isset($_POST['callback_function']) and in_array($_POST['callback_function'], $aCallbackFunctions) ) {
            if ( method_exists($oCommunicator, $_POST['callback_function']) )
                $sOutputHtml = $oCommunicator->{$_POST['callback_function']}();
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $sOutputHtml ;
        exit;
    }

    // ** prepare to output page in normal mode ;
    $_ni = $_page['name_index'] = 7;
    $_page['header'] = _t( "_Activity" );
    $_page['header_text'] = _t( "_Activity" );

    $_page['css_name'] = $oCommunicator->getCss();
    $_page['js_name'] = $oCommunicator->getJs();

    if ($aCommunicatorSettings['member_id'])
        $sOutputHtml = $oCommunicator->getCode();
    else
        login_form(_t( "_LOGIN_OBSOLETE" ), 0, false);

    $_page_cont[$_ni]['page_main_code'] = $sOutputHtml;

    PageCode();
