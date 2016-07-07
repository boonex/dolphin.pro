<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( 'inc/header.inc.php' );
    require_once( BX_DIRECTORY_PATH_INC  . 'design.inc.php' );
    require_once( BX_DIRECTORY_PATH_ROOT . 'templates/tmpl_' . $tmpl . '/scripts/BxTemplMailBox.php');

    $iProfileId = getLoggedId();

    $aProfile = getProfileInfo($iProfileId);

    // init some needed parameters ;

    $sOutputHtml    = '';
    $sComposedPage  = '';

    // define message id (for view mode) ;
    $iMessageID  = ( isset($_GET['messageID']) )
        ? (int) $_GET['messageID']
        : 0;

    // define the message status such as : read, unread ;
    $iMessageStatus = ( isset($_GET['status']) && $_GET['status'] == 'read' )
        ? 0
        : 1 ;

    // define the recipient ID ;
    $vRecipientID = ( isset($_GET['recipient_id']) )
        ? $_GET['recipient_id']
        : '';

    // contain message's subject ;
    $sMessageSubject = ( isset($_POST['subject']) )
        ? $_POST['subject']
         : null;

    // contain messages' body ;
    $sMessageBody = ( isset($_POST['message']) )
        ? $_POST['message']
        : '';

        //? process_db_input( nl2br( urldecode($_POST['message']) ), BX_TAGS_VALIDATE) : null;

    // contain all receivied messages id separeted by comma ;
    $sMessagesList = isset( $_GET['messages'] )
        ? $_GET['messages']
        : '';

    // contain query from js for autocomplete;
    $sAutoCompleteQ = ( isset($_GET['term']) )
        ? $_GET['term']
        : '';

    // try to segregate received messages list;
    if ( $sMessagesList ) {
        // array : contain all received messages id ;
        $aMessagesList  = array();
        $aMessagesList  = explode(',', $sMessagesList);
    }

    // contain some needed settings for  the MailBox's object ;
    $aMailBoxSettings = array
    (
        // logged member's ID ;
        'member_id'	 => ($iProfileId || $aProfile['Role'] & BX_DOL_ROLE_ADMIN)
            ? $iProfileId
            : 0,

        // message recipient's ID ;
        'recipient_id'	 => ($vRecipientID)
            ? (int) $vRecipientID
            : 0,

        // mailbox mode such as : inbox, outbox, trash ;
        'mailbox_mode'	 => (isset($_GET['mode']))
                ? $_GET['mode']
                : 'inbox',

        // type of message's sort ;
        'sort_mode' => (isset($_GET['sorting']))
            ? $_GET['sorting']
            : 'date_desc',

        // contain number of current page ;
        'page' => (isset($_GET['page']))
            ? (int) $_GET['page']
            : 0,

        // contain per page number for current page ;
        'per_page' => (isset($_GET['per_page']))
            ? (int) $_GET['per_page']
            : 0,

        // contain type of needed type of contacts (friends, faves ...)
        'contacts_mode'	=> (isset($_GET['contacts_mode']))
            ? $_GET['contacts_mode']
            : '',

        // contain number of current contacts page ;
        'contacts_page'	=> (isset($_GET['contacts_page']))
            ? (int) $_GET['contacts_page']
            : 0,

        // contain number of needed message ;
        'messageID'	=> (isset($_GET['messageID']))
            ? (int) $_GET['messageID']
            : 0,
    );

    // contain all needed settings for compose message ;
    $aComposeSettings = array
    (
        // allow to send message to phisical recipient's email ;
        'send_copy' => ( isset($_GET['copy_message']) )
            ? true
            : false ,

        // allow to send message to phisical sender's email;
        'send_copy_to_me' => ( isset($_GET['copy_message_to_me']) )
            ? true
            : false ,

        // allow to send notification to the recipient's email ;
        'notification' => ( isset($_GET['notify']) )
            ? true
            : false ,
    );

    // define the type of message (greet, mail ...) ;
    if ( isset($_GET['messages_types']) ) {
        $aMailBoxSettings['messages_types'] = $_GET['messages_types'];
    }

    // ** swith the compose page;
    switch($aMailBoxSettings['mailbox_mode']) {
        case 'inbox'	:
        case 'outbox'	:
        case 'trash'	:
            $sComposedPage = 'mail_page';
        break;

        case 'compose'	:
        case 'view_message' :
            $sComposedPage = $aMailBoxSettings['mailbox_mode'] == 'compose'
                ? 'mail_page_compose'
                : 'mail_page_view';

            //add some translation
            $GLOBALS['oSysTemplate']->addJsTranslation('_Mailbox title empty');
            $GLOBALS['oSysTemplate']->addJsTranslation('_Mailbox recipient empty');
            $GLOBALS['oSysTemplate']->addJsTranslation('_Mailbox description empty');
        break;

        default :
            $sComposedPage = 'mail_page';
    }

    // create BxTemplMailBox object
    $oMailBox = new BxTemplMailBox($sComposedPage, $aMailBoxSettings);

    if ( isset($_GET['ajax_mode']) and false !== bx_get('action') ) {
        // contain all the available callback functions ;
        $aCallbackFunctions = array( 'genMessagesRows', 'genArchiveMessages', 'getInboxMessagesCount' );

        switch( bx_get('action') ) {
            case 'sort'		:
            case 'paginate' :
            case 'get_page' :
                $sOutputHtml = $oMailBox -> genMessagesRows();
            break;

            // mark all the received messages ;
            case 'mark'		:
                // mark action only for post method
                if( isset($_POST['action']) && $_POST['action'] == 'mark') {
                    // mark message with received mode ;
                    if ( is_array($aMessagesList) and !empty($aMessagesList) ) {
                        foreach( $aMessagesList  AS $iKey ) {
                            $iMessageID = (int) $iKey;
                            if ( $iMessageID )
                                $oMailBox -> setMarkMessage($iMessageID, $iMessageStatus);
                        }

                        $sOutputHtml = 'ok';
                    }
                }
            break;

            // hide deleted messages ;
            case 'hide_deleted' :
                if ( is_array($aMessagesList ) and !empty($aMessagesList) ) {
                    foreach( $aMessagesList AS $iKey ) {
                       $iMessageID = (int) $iKey;
                       if ($iMessageID)
                          $oMailBox -> setTrashedMessage($iMessageID, 'TrashNotView');
                    }
                }
            break;

            // delete all the received messages ;
            case 'delete' :
                // mark action only for post method
                if( isset($_POST['action']) && $_POST['action'] == 'delete') {
                    if ( is_array($aMessagesList ) and !empty($aMessagesList) ) {
                        foreach( $aMessagesList AS $iKey ) {
                           $iMessageID = (int) $iKey;
                           if ( $iMessageID )
                              $oMailBox -> setTrashedMessage($iMessageID);
                        }
                    }
                }
            break;

            // restore all the deleted messages from trash;
            case 'restore' :
                if( isset($_POST['action']) && $_POST['action'] == 'restore') {
                    if ( is_array($aMessagesList) and !empty($aMessagesList) ) {
                        foreach( $aMessagesList AS $iKey ) {
                           $iMessageID = (int) $iKey;
                           if ( $iMessageID )
                               $oMailBox -> setRestoredMessage($iMessageID);
                        }
                    }
                }
            break;

            // will return count of inbox messages ;
            case 'get_messages_count' :
                $iMessageCount 	= $oMailBox -> getInboxMessagesCount();
                $sResponceText	= ( $iMessageCount ) ? ' (' . $iMessageCount . ') ' : null;
                $sOutputHtml 	= $sResponceText;
            break;

            // will return all the arhive's message list ;
            case 'archives_paginate' :
                $sOutputHtml = $oMailBox -> genArchiveMessages();
            break;

            // will return message's replay window;
            case 'reply_message' :
                if ( $iMessageID and $vRecipientID ) {
                    $vRecipientID = (int) $vRecipientID;
                    $sOutputHtml = $oMailBox -> genReplayMessage($vRecipientID, $iMessageID);
                }
            break;

            case 'compose_mail' :
                $sErrorMessage = '';

                //check message's options
                if(!$sMessageSubject) {
                    $sErrorMessage = '_Mailbox title empty';
                }

                if(!$sMessageBody) {
                    $sErrorMessage = '_Mailbox description empty';
                }

                if(!$vRecipientID) {
                    $sErrorMessage = '_Mailbox recipient empty';
                }

                $sOutputHtml = !$sErrorMessage
                    ? $oMailBox -> sendMessage($sMessageSubject, $sMessageBody, $vRecipientID, $aComposeSettings)
                    : _t_err($sErrorMessage);
            break;

            case 'auto_complete' :
                if ( $sAutoCompleteQ )
                    $sOutputHtml = $oMailBox -> getAutoCompleteList($sAutoCompleteQ);
            break;

            case 'get_thumbnail' :
                $iRecipientID = getId($vRecipientID);
                if ( $iRecipientID )
                    $sOutputHtml = get_member_thumbnail($iRecipientID, 'none');
            break;
        }

        // try to define the callback function name ;
        if ( isset($_GET['callback_function']) and in_array($_GET['callback_function'], $aCallbackFunctions) ) {
            if (method_exists($oMailBox, $_GET['callback_function']))
                $sOutputHtml = $oMailBox->{$_GET['callback_function']}();
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $sOutputHtml;
        exit;
    }

    // ** prepare to output page in normal mode ;
    $sPageTitle = _t('_Mailbox');

    $_page['name_index'] = 7;
    $_page['header'] = $sPageTitle;
    $_page['header_text'] = $sPageTitle;
    $_page['js_name'] = $oMailBox->getJs();
    $_page['css_name'] = $oMailBox->getCss();

    $aVars = array ('BaseUri' => BX_DOL_URL_ROOT);
    $GLOBALS['oTopMenu']->setCustomSubActions($aVars, 'Mailbox', false);

    if(!$aMailBoxSettings['member_id'])
        login_form(_t( "_LOGIN_OBSOLETE" ), 0, false);

    $_ni = $_page['name_index'];
    $_page_cont[$_ni]['page_main_code'] = $oMailBox->getCode();

    PageCode();
