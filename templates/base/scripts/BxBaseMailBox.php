<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolMailBox');
    bx_import('BxDolSubscription');

    class BxBaseMailBox extends BxDolMailBox
    {
        // contain all needed templates for Html rendering ;
        var $aUsedTemplates;

        // icon type and extension for messages types ;
        var $sMessageIconPrefix      = 'icon_';
        var $sMessageIconExtension   = '.png';

        var $sMembersFlagExtension   = '.gif';

        // parameters for the displayed message's data into mailbox section;
        var $iMessageSubjectLength   = 30;
        var $iMessageDescrLength     = 40;

        // parameters for the displayed subject data into archives section;
        var $iArchivesSubjectLength  = 22;

        /**
         * Class constructor ;
         *
         * @param        : $sPageName (string)  - page name (need for page builder);
         * @param        : $aMailBoxSettings (array)       - contain some necessary data ;
         *                     [] member_id    (integer)   - member's ID;
         *                     [] recipient_id (integer)   - message recipient's ID ;
         *                     [] mailbox_mode (string)    - inbox, outbox or trash switcher mode ;
         *                     [] sort_mode (string)       - message sort mode;
         *                     [] page (integer)           - number of current page ;
         *                     [] per_page (integer)       - number of messages for per page ;
         *                     [] messages_types (string)  - all needed types of messages
         *                     [] contacts_mode (string)   - type of contacts (friends, faves, contacted) ;
         *                     [] contacts_page (integer)  - number of current contact's page ;
         *                     [] message_id     (integer) - number of needed message ;
         */
        function __construct( $sPageName, &$aMailBoxSettings )
        {
            // call the parent constructor ;
            parent::__construct($sPageName, $aMailBoxSettings);

            // fill array with used template name ;
            $this -> aUsedTemplates = array (
                'messages_init_box'           => 'mail_init_box.html',
                'messages_box'                => 'mail_box.html',
                'messages_types_list'         => 'mail_box_messages_types.html',
                'messages_top_section'        => 'mail_box_top_section.html',
                'contacts_section'            => 'mail_box_contacts_list.html',
                'archives_section'            => 'mail_box_archives_list.html',
                'archives_init_section'       => 'mail_box_init_archives_list.html',
                'archives_pagina_section'     => 'mail_box_archives_pagination.html',
                'archives_pagina_items'       => 'mail_box_archives_pagination_items.html',
                'view_message_box'            => 'mail_box_view_message.html',
                'message_replay'              => 'mail_box_replay_message.html',
                'message_compose'             => 'mail_box_compose_message.html',
            );
        }

        function getCss()
        {
            return array('mail.css');
        }

        function getJs()
        {
            return array('mail_box.js');
        }

        /**
         * Function will generate compose message block ;
         *
         * @return        : Html presentation data ;
         */
        function getBlockCode_ComposeMessage()
        {
            global $oSysTemplate, $site, $oTemplConfig, $_page;

            $_page['js_name']  = array(
                'mail_box.js', 
                'plugins/jquery/|jquery.ui.autocomplete.min.js',
                'plugins/jquery/|jquery.ui.menu.min.js',
            );

            // init some needed variables ;
            $sOutputHtml = null;

            // check bloked;
            if(!isAdmin($this -> aMailBoxSettings['member_id']) && isBlocked($this -> aMailBoxSettings['recipient_id'], $this -> aMailBoxSettings['member_id']) ) {
                return MsgBox( _t('_FAILED_TO_SEND_MESSAGE_BLOCK') );
            }

            // if isset recipient ID ;
            $aMemberInfo = ($this -> aMailBoxSettings['recipient_id']) ? getProfileInfo($this -> aMailBoxSettings['recipient_id']) : null;

            // check if send message is allowed
            $aComposeSettings = array();
            $sErrMsg = $this->sendMessage('', '', $aMemberInfo ? $aMemberInfo['ID'] : 0, $aComposeSettings, true);
            if($sErrMsg)
                return array($sErrMsg);

            $aLanguageKeys = array (
                'cancel'        => _t( '_Cancel' ),
                'send'          => _t( '_Send' ),
                'send_copy'     => _t( '_Send copy to personal email', isset($aMemberInfo['NickName']) ? $aMemberInfo['NickName'] : null ),
                'send_copy_my'  => _t( '_Send copy to my personal email' ),
                'notify'        => _t( '_Notify by e-mail', isset($aMemberInfo['NickName']) ? $aMemberInfo['NickName'] : null ),
                'error_message' => _t( '_please_fill_next_fields_first' ),
                'subject'       => _t( '_Subject' ),
                'message_to'    => _t( '_SEND_MSG_TO' ),
            );

            // ** generate recipient's information ;

            $sMemberIcon         = get_member_thumbnail($this -> aMailBoxSettings['recipient_id'], 'none');
            $sRecipientName      = ( !empty($aMemberInfo) ) ? getNickName($aMemberInfo['ID']) : null;
            $sMemberLocation     = ( !empty($aMemberInfo) ) ? getProfileLink($aMemberInfo['ID']) : null;

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
                            'value'     => $aLanguageKeys['send'],
                            'attrs'     => array('onclick' => 'if(typeof oMailBoxComposeMessage != \'undefined\') oMailBoxComposeMessage.sendMessage()'),
                        ),
                        1 => array (
                            'type'      => 'button',
                            'value'     => $aLanguageKeys['cancel'],
                            'attrs'     => array('onclick' => 'if(typeof oMailBoxComposeMessage != \'undefined\') oMailBoxComposeMessage.cancelCompose()'),
                        ),
                    )
                )
            );

            $oForm = new BxTemplFormView($aForm);
            $sMessageBoxActions =  $oForm -> getCode();

            $aTemplateKeys = array
            (
                'plugins_dir'        => BX_DOL_URL_PLUGINS,
                'error_message'      => $aLanguageKeys['error_message'],
                'current_page'       => 'mail.php',
                'recipient_id'       => $aMemberInfo['ID'],

                'member_thumbnail'  => $sMemberIcon,
                'member_location'   => $sMemberLocation,

                'recipient_name'    => $sRecipientName,
                'subject'           => $aLanguageKeys['subject'],

                'send_copy_my'      => $aLanguageKeys['send_copy_my'],
                'send_copy_to'      => $aLanguageKeys['send_copy'],
                'notify'            => $aLanguageKeys['notify'],
                'message_to'        => $aLanguageKeys['message_to'],

                'compose_actions_buttons'   => $sMessageBoxActions,
            );

            $sOutputHtml  = $oSysTemplate
                -> parseHtmlByName( $this -> aUsedTemplates['message_compose'], $aTemplateKeys );

            // generate the page toggle ellements ;
            $aToggleItems = array
            (
                'inbox'   =>  _t( '_Inbox' ),
                'outbox'  =>  _t( '_Outbox' ),
                'trash'   =>  _t( '_Trash' ),
                'compose' =>  _t( '_Compose' ),
            );

            $sRequest = 'mail.php?';
            foreach( $aToggleItems AS $sKey => $sValue ) {
                $aTopToggleEllements[$sValue] = array
                (
                    'href' => $sRequest . 'mode=' . $sKey,
                    'dynamic' => false,
                    'active' => ($this -> aMailBoxSettings['mailbox_mode'] == $sKey ),
                );
            }

            bx_import('BxDolEditor');
            $oEditor = BxDolEditor::getObjectInstance();
            $sEditor = '';
            if ($oEditor) {
                $oEditor->setCustomConf ("remove_script_host: false,\nrelative_urls: false,\n");
                $sEditor = $oEditor->attachEditor ('#compose_message', BX_EDITOR_FULL);
            }

            return array($sEditor . $sOutputHtml, $aTopToggleEllements);
        }

        /**
         * Function will generate block with users message archive's list ;
         *
         * @return : Html presentation data ;
         */
        function getBlockCode_Archives()
        {
            global  $oSysTemplate;

            // init some needed variables;

            $sOutputHtml = null;
            $iSenderID     = 0;
            $iMessageOwner = 0;

            // define message's owner ;
            $sQuery =
            "
                SELECT
                    `Sender`, `Recipient`
                FROM
                    `sys_messages`
                WHERE
                    `ID` = {$this -> aMailBoxSettings['messageID']}
                AND
                (
                    `Sender` = {$this -> aMailBoxSettings['member_id']}
                        OR
                    `Recipient` = {$this -> aMailBoxSettings['member_id']}
                )
            ";

            $rResult = db_res($sQuery);
            while(true == ($aRow = $rResult->fetch())) {
                $iMessageOwner = ( $aRow['Sender'] == $this -> aMailBoxSettings['member_id'] )
                    ? $aRow['Recipient']
                    : $aRow['Sender'];

                $iSenderID = $aRow['Sender'];
            }

            $sSenderNickName = getNickName($iMessageOwner);

            // set default selected tab ;
            if ( !$this -> aMailBoxSettings['contacts_mode']) {
                if ($iSenderID != $this -> aMailBoxSettings['member_id'])
                    $this -> aMailBoxSettings['contacts_mode'] = 'From';
                else
                    $this -> aMailBoxSettings['contacts_mode'] = 'To';
            }

            // contain all found messages from member ;
            $sMessagesList          = $this -> genArchiveMessages();
            $aTopToggleEllements    = array();
            $aBottomToggleEllements = array();

            // generate the top toggle ellements ;
            $sRequest = 'mail.php?mode=view_message';
            foreach( $this -> aRegisteredArchivesTypes AS $sKey => $sValue ) {
                $aTopToggleEllements[ _t('_' . $sKey) . ' ' . $sSenderNickName ] = array
                (
                    'href'       => $sRequest . '&contacts_mode=' . $sKey . '&messageID=' . $this -> aMailBoxSettings['messageID'],
                    'dynamic'    => true,
                    'active'     => ($this -> aMailBoxSettings['contacts_mode'] == $sKey ),
                );
            }

            $aLanguageKeys = array
            (
                'delete_messages' => _t( '_Delete'),
                'select_all'      => _t( '_Select all'),
                'spam_messages'   => _t( '_Spam report' ),
            );

            // return builded data ;
            if ( empty($sMessagesList) )
                $sMessagesList = MsgBox(_t( '_Empty' ));

            $sTopControls = $oSysTemplate->parseHtmlByName('mail_box_init_archives_list_top_controls.html', array(
                'select_all' => $aLanguageKeys['select_all'],
                'delete_messages' => $aLanguageKeys['delete_messages'],
                'spam_messages' => $aLanguageKeys['spam_messages'],
            ));

            $aTemplateKeys = array (
                'top_controls' => $sTopControls,
                'select_messages' => _t('_Please, select at least one message'),
                'are_you_sure' => _t('_Are_you_sure'),
                'messages_rows' => $sMessagesList,
                'current_page' => 'mail.php',

            );

            $sOutputHtml = $oSysTemplate -> parseHtmlByName( $this -> aUsedTemplates['archives_init_section'], $aTemplateKeys );
            return array($sOutputHtml, $aTopToggleEllements, array(), true);
        }

        /**
         * Function will generate the view message block ;
         *
         * @return   : Html presentation data ;
         */
        function getBlockCode_ViewMessage()
        {
            global $oSysTemplate;
            global $oFunctions;
            global $site;
            global $oTemplConfig;

            // init some needed variables;
            $sOutputHtml  = null;
            $sActionsList = null;

            // generate page toggle ellements ;
            $aToggleItems = array
            (
                'inbox'   =>  _t( '_Inbox' ),
                'outbox'  =>  _t( '_Outbox' ),
                'trash'   =>  _t( '_Trash' ),
                'compose' =>  _t( '_Compose' ),
            );

            $sRequest = 'mail.php' . '?';
            foreach( $aToggleItems AS $sKey => $sValue ) {
                $aTopToggleEllements[$sValue] = array
                (
                    'href'      => $sRequest . '&mode=' . $sKey,
                    'dynamic'   => false,
                    'active'    => false,
                );
            }

            // language keys ;
            $aLanguageKeys = array
            (
                'back_inbox'       => _t('_sys_mailbox_back_inbox'),
                'back_outbox'      => _t('_sys_mailbox_back_outbox'),
                'back_trash'       => _t('_sys_mailbox_back_trash'),
                'spam_message'     => _t( '_Report' ),
                'delete_message'   => _t( '_Delete' ),
                'reply_message'    => _t( '_Reply' ),
                'restore_message'  => _t( '_Restore' ),
                'are_you_sure'     => _t( '_Are_you_sure' ),
                'more'             => _t( '_More actions' ),
                'mark_read'        => _t( '_Mark as old' ),
                'mark_unread'      => _t( '_Mark as New' ),
            );

            $sQuery =
            "
                SELECT
                    *,
                    DATE_FORMAT(`Date`, '" . getLocaleFormat(BX_DOL_LOCALE_DATE, BX_DOL_LOCALE_DB) . "') AS `Date`
                FROM
                    `sys_messages`
                WHERE
                    `ID` = {$this -> aMailBoxSettings['messageID']}
                AND
                    (
                        `Sender` = {$this -> aMailBoxSettings['member_id']}
                            OR
                        `Recipient` = {$this -> aMailBoxSettings['member_id']}
                    )
            ";

            $rResult = db_res($sQuery);
            while( true == ($aRow = $rResult->fetch()) ) {
                // ** generate member's information ;

                $sMemberIcon  = get_member_thumbnail($aRow['Sender'], 'none');
                $aMemberInfo  = getProfileInfo($aRow['Sender']);

                // define the back link;
                if($aRow['Trash'] == 'recipient') {
                    $sBackCaption = $aLanguageKeys['back_trash'];
                    $sBackUrl     = 'mail.php' . '?mode=trash';
                } else if ( $aRow['Sender'] == $this -> aMailBoxSettings['member_id'] ) {
                    $sBackCaption = $aLanguageKeys['back_outbox'];
                    $sBackUrl     = 'mail.php' . '?mode=outbox';
                } else {
                    $sBackCaption = $aLanguageKeys['back_inbox'];
                    $sBackUrl     = 'mail.php' . '?mode=inbox';
                }

                $sMemberNickName = getNickName($aMemberInfo['ID']);
                $sMemberLocation = getProfileLink($aMemberInfo['ID']);
                $sMemberSexImage = isset($aMemberInfo['Sex']) ? $oFunctions -> genSexIcon($aMemberInfo['Sex']) : null;

                $sMemberAge = ( isset($aMemberInfo['DateOfBirth']) && $aMemberInfo['DateOfBirth'] != "0000-00-00" )
                    ? _t( "_y/o", age($aMemberInfo['DateOfBirth']) )
                    : null;

                $sMemberCountry = ( isset($aMemberInfo['Country']) && $aMemberInfo['Country'])
                    ? $aMemberInfo['Country']
                    : null;

                $sMemberFlag =  ($sMemberCountry)
                    ? $site['flags'] . strtolower($sMemberCountry) . $this -> sMembersFlagExtension
                    : null;

                if ( $sMemberCountry )
                    $sMemberCountryFlag = '<img src="' . $sMemberFlag . '" alt="' . $sMemberCountry . '" />';

                $sCity = ( isset($aMemberInfo['City']) && $aMemberInfo['City'] )
                    ? $aMemberInfo['City'] . ', '
                    : null;

                // member's city and country;
                $sMemberCountry = $sCity . $sMemberCountry;

                // generate the member's actions list;
                if ( $aRow['Sender'] != $this -> aMailBoxSettings['member_id'] ) {
                    $sActionsList = $oFunctions->getProfileViewActions($aRow['Sender']);
                }

                // try to define the message status ;
                if ( $aRow['Sender'] == $this -> aMailBoxSettings['member_id'] ) {
                    if ( strstr($aRow['Trash'], 'sender') ) {
                        $this -> aMailBoxSettings['mailbox_mode'] = 'trash';
                    } else {
                        $this -> aMailBoxSettings['mailbox_mode'] = 'outbox';
                    }
                    $sRelocateParameter = 'outbox';
                } else if ( $aRow['Sender'] != $this -> aMailBoxSettings['member_id'] ) {
                    if ( strstr($aRow['Trash'], 'recipient') ) {
                        $this -> aMailBoxSettings['mailbox_mode'] = 'trash';
                    } else {
                        $this -> aMailBoxSettings['mailbox_mode'] = 'inbox';
                    }
                    $sRelocateParameter = 'inbox';
                }

                // generate extended mailbox actions
                switch($this -> aMailBoxSettings['mailbox_mode']) {
                    case 'inbox' :
                        // generate actions for inbox mode ;
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
                                    array(),
                                    array (
                                        'type'      => 'button',
                                        'value'     => $aLanguageKeys['delete_message'],
                                        'attrs'     => array('onclick' => 'if ( typeof oMailBoxViewMessage != \'undefined\') oMailBoxViewMessage.deleteMessages(' . $aRow['ID'] . ')', 'class' => 'bx-btn-small'),
                                    ),
                                    array (
                                        'type'      => 'button',
                                        'value'     => $aLanguageKeys['spam_message'],
                                        'attrs'     => array('onclick' => 'if ( typeof oMailBoxViewMessage != \'undefined\') oMailBoxViewMessage.spamMessages(\'\', ' . $aRow['Sender'] . ')', 'class' => 'bx-btn-small'),
                                    ),
                                    array (
                                        'type'      => 'button',
                                        'value'     => $aLanguageKeys['mark_read'],
                                        'attrs'     => array('onclick' => 'if ( typeof oMailBoxViewMessage != \'undefined\') oMailBoxViewMessage.markMessages(\'read\', ' . $aRow['ID'] . ')', 'class' => 'bx-btn-small'),
                                    ),
                                    array (
                                        'type'      => 'button',
                                        'value'     => $aLanguageKeys['mark_unread'],
                                        'attrs'     => array('onclick' => 'if ( typeof oMailBoxViewMessage != \'undefined\') oMailBoxViewMessage.markMessages(\'unread\', ' . $aRow['ID'] . ')', 'class' => 'bx-btn-small'),
                                    )
                                )
                            )
                        );

                        if($aRow['Sender'] != $this -> aMailBoxSettings['member_id']){
                            $aForm['inputs']['actions'][0] = array(
                                'type'      => 'button',
                                'value'     => $aLanguageKeys['reply_message'],
                                'attrs'     => array('onclick' => 'oMailBoxViewMessage.replyMessage(' . $this -> aMailBoxSettings['messageID'] . ', ' . $aRow['Sender'] . ');', 'class' => 'bx-btn-small'),
                            );
                        }

                        $oForm = new BxTemplFormView($aForm);
                        $sMessageBoxActions =  $oForm -> getCode();
                        break;

                    case 'outbox' :
                        // generate actions for outbox mode ;
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
                                        'value'     => $aLanguageKeys['delete_message'],
                                        'attrs'     => array('onclick' => 'if (typeof oMailBoxViewMessage != \'undefined\') oMailBoxViewMessage.deleteMessages(' . $aRow['ID'] . ')', 'class' => 'bx-btn-small'),
                                    ),
                                )
                            )
                        );

                        $oForm = new BxTemplFormView($aForm);
                        $sMessageBoxActions =  $oForm -> getCode();
                        break;

                    case 'trash' :
                        // generate actions for outbox mode ;
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
                                0 => null,
                                1 => array (
                                    'type'      => 'button',
                                    'value'     => $aLanguageKeys['restore_message'],
                                    'attrs'     => array('onclick' => 'if (typeof oMailBoxViewMessage != \'undefined\') oMailBoxViewMessage.restoreMessages(' . $aRow['ID'] . ');', 'class' => 'bx-btn-small'),
                                ),
                            )
                        )
                    );

                    if($aRow['Sender'] != $this -> aMailBoxSettings['member_id']){
                        $aForm['inputs']['actions'][0] = array(
                            'type'      => 'button',
                            'value'     => $aLanguageKeys['reply_message'],
                            'attrs'     => array('onclick' => 'oMailBoxViewMessage.replyMessage(' . $this -> aMailBoxSettings['messageID'] . ', ' . $aRow['Sender'] . ');', 'class' => 'bx-btn-small'),
                        );
                    }

                    $oForm = new BxTemplFormView($aForm);
                    $sMessageBoxActions =  $oForm -> getCode() ;
                    break;
                }

                // prepare for output ;
                $sTopcontrols = $oSysTemplate->parseHtmlByName('mail_box_view_message_top_controls.html', array(
                    'inbox_mode'        => $sBackUrl,
                    'back_to_inbox'     => $sBackCaption,
                    'back_img_src'      => getTemplateIcon('back_to_inbox.png'),
                ));

                $aTemplateKeys = array (
                    'top_controls'      => $sTopcontrols,

                    'member_thumbnail'  => $sMemberIcon,
                    'member_nick_name'  => $sMemberNickName,
                    'member_location'   => $sMemberLocation,

                    'member_sex_img'    => $sMemberSexImage,
                    'member_sex'        => $aMemberInfo['Sex'],

                    'member_age'        => $sMemberAge,
                    'member_flag'       => $sMemberCountryFlag,
                    'city_country'      => $sMemberCountry,

                    'date_create'       => $aRow['Date'],

                    'message_subject'   => $aRow['Subject'],
                    'member_actions_list'    => $sActionsList,

                    'message_text'    => $aRow['Text'],

                    'are_you_sure'    => $aLanguageKeys['are_you_sure'],
                    'current_page'    => 'mail.php',
                    'spam_message'    => $aLanguageKeys['spam_message'],
                    'delete_message'  => $aLanguageKeys['delete_message'],

                    'message_id'      => $aRow['ID'],
                    'message_actions' => $sMessageBoxActions,
                    'page_mode'       => $sRelocateParameter,
                );

                // build and return final template ;
                $sOutputHtml = $oSysTemplate->parseHtmlByName( $this -> aUsedTemplates['view_message_box'], $aTemplateKeys );
            }

            // if message nothing found ;
            if ( !$rResult->rowCount() )
                $sOutputHtml = MsgBox(_t( '_Empty' ) );

            return array($sOutputHtml, $aTopToggleEllements, array(), true);
        }

        /**
         * Function will generate block with NEW messages.
         * NOTE. The block is displayed on "My Account" page.
         *
         * @return: Html presentation data
         */
        function getBlockCode_NewMessages($bShowEmpty = true)
        {
            global $oSysTemplate;

            $aMessages = $this->getMessages();
            if(empty($aMessages))
                return $bShowEmpty ? MsgBox(_t('_Empty')) : '';

            $aTmplVars = array(
                'bx_repeat:messages' => array()
            );

            foreach($aMessages as $aMessage) {
                $aTmplVars['bx_repeat:messages'][] = array(
                    'message_page' => 'mail.php',
                    'message_link' => $aMessage['ID'],
                    'member_icon' => get_member_icon($aMessage['Sender'], 'left'),
                    'member_nickname' => getNickName($aMessage['Sender']),
                    'message_ago' => defineTimeInterval($aMessage['DateTS']),
                    'message_subject' => $aMessage['Subject']
                );
            }

            return $oSysTemplate->parseHtmlByName('mail_box_short.html', $aTmplVars);
        }

        /**
         * Function will generate block with messages ;
         *
         * @return         : Html presentation data ;
         */
        function getBlockCode_MailBox()
        {
            global $oSysTemplate;

            // init some nedded variables ;

            $sOutputHtml   = null;

            // return all builded messages ;
            $sMessageRows  = $this -> genMessagesRows();

            // prepare for output ;
            $aTemplateKeys = array
            (
                'current_page'        => 'mail.php',
                'mail_rows'           => ( $sMessageRows ) ?  $sMessageRows : MsgBox(_t( '_Empty' )),
                'select_messages'     => _t('_Please, select at least one message'),
                'are_you_sure'        => _t('_Are_you_sure'),
            );

            // build and return final template ;
            $sOutputHtml = $oSysTemplate -> parseHtmlByName( $this -> aUsedTemplates['messages_init_box'], $aTemplateKeys );

            // generate page toggle ellements ;
            $aToggleItems = array
            (
                'inbox'   =>  _t( '_Inbox' ),
                'outbox'  =>  _t( '_Outbox' ),
                'trash'   =>  _t( '_Trash' ),
                'compose' =>  _t( '_Compose' ),
            );

            $sRequest = 'mail.php' . '?';
            foreach( $aToggleItems AS $sKey => $sValue ) {
                $aTopToggleEllements[$sValue] = array
                (
                    'href' => $sRequest . 'mode=' . $sKey,
                    'dynamic' => false,
                    'active' => ($this -> aMailBoxSettings['mailbox_mode'] == $sKey ),
                );
            }

            return array($sOutputHtml, $aTopToggleEllements, array(), true);
        }

        /**
         * Function will generate block with member's contacts ;
         *
         * @return         : html presentation data ;
         */
        function getBlockCode_Contacts()
        {
            global  $oSysTemplate,
                    $oFunctions,
                    $site;

            // init some needed variables;
            $sFunctionNam   = null;
            $sOutputHtml    = null;

            // set default top toggle menu item
            $aRegisteredContacts_keys = array_keys($this -> aRegisteredContactTypes);
            $sDefaultItem     = $aRegisteredContacts_keys[0];

            if ( !$this -> aMailBoxSettings['contacts_mode'] )
                $this -> aMailBoxSettings['contacts_mode'] = $sDefaultItem;

            // contain all found members ;
            $aMembersList           = array();
            $aTopToggleEllements    = array();
            $aBottomToggleEllements = array();

            // define number of maximum members list for per page ;
            if( $this -> aMailBoxSettings['contacts_page'] < 1 )
                $this -> aMailBoxSettings['contacts_page'] = 1;

            $sLimitFrom = ( $this -> aMailBoxSettings['contacts_page'] - 1 ) * $this -> iContactsPerPage;
            $sSqlLimit = "LIMIT {$sLimitFrom}, {$this -> iContactsPerPage}";

            if($this -> aMailBoxSettings['contacts_mode'] && array_key_exists($this -> aMailBoxSettings['contacts_mode'], $this -> aRegisteredContactTypes))
                $sFunctionName  = $this -> aRegisteredContactTypes[$this -> aMailBoxSettings['contacts_mode']];

            // default function name ;
            if (!$sFunctionName )
                $sFunctionName = $this -> aRegisteredContactTypes[$sDefaultItem];

            // generate the block of contacts;
            if (method_exists($this, $sFunctionName)) {
                // call registered method;
                $aMembersList = $this -> $sFunctionName( $sSqlLimit );
                if ( is_array($aMembersList) and !empty($aMembersList) ) {
                        $sComposeImg = getTemplateIcon('action_send.png');

                        // generate list of members ;
                        foreach($aMembersList AS $sKey => $aProfileInfo ) {
                            $sMemberFlag    = null;
                            $sMemberSexImg  = null;

                            $sMemberIcon        = get_member_thumbnail($aProfileInfo['ID'], 'left');
                            $sMemberLocation    = getProfileLink($aProfileInfo['ID']);

                            $sMemberSexImg      = ( isset($aProfileInfo['Sex']) ) ? $oFunctions -> genSexIcon($aProfileInfo['Sex']) : null;
                            $sMemberAge         = ( isset($aProfileInfo['DateOfBirth']) && $aProfileInfo['DateOfBirth'] != "0000-00-00" )
                                ? _t( "_y/o", age($aProfileInfo['DateOfBirth']) )
                                : null;


                            if( isset($aProfileInfo['Country']) && $aProfileInfo['Country'] ) {
                                $sMemberFlag = $site['flags'] . strtolower($aProfileInfo['Country']) . $this -> sMembersFlagExtension;
                            }

                            $sMemberCountryFlag = ($sMemberFlag)
                                    ? '<img src="' . $sMemberFlag . '" alt="' . $aProfileInfo['Country'] . '" />'
                                    : null;

                            $aMemberKeys[] = array
                            (
                                'member_icon'          => $sMemberIcon,
                                'member_location'      => $sMemberLocation,
                                'member_nick_name'     => getNickName($aProfileInfo['ID']),

                                'member_sex_img'       => $sMemberSexImg,
                                'member_sex_img_alt'   => $aProfileInfo['Sex'],
                                'member_age'           => $sMemberAge,
                                'member_flag'          => $sMemberCountryFlag,
                                'member_country'       => $aProfileInfo['Country'],

                                'compose_img'          => $sComposeImg,
                                'member_action_date'   => $aProfileInfo['When'],
                                'member_id'            => $aProfileInfo['ID'],
                                'current_page'         => 'mail.php',
                            );
                        }
                    }

                    // build the template ;
                    $aTemplateKeys = array
                    (
                        'bx_repeat:members' => $aMemberKeys,
                    );

                    $sOutputHtml .= $oSysTemplate
                        -> parseHtmlByName( $this -> aUsedTemplates['contacts_section'], $aTemplateKeys );

                }


            // generate the top toggle ellements ;
            $sRequest = 'mail.php?';
            $sSelectedItem = ( $this -> aMailBoxSettings['contacts_mode'] )
                ? $this -> aMailBoxSettings['contacts_mode']
                : $sDefaultItem;

            // conatin recipient's ID;
            $sRecipientParam = ($this -> aMailBoxSettings['recipient_id'])
                ? '&recipient_id=' . $this -> aMailBoxSettings['recipient_id']
                : null;

            foreach( $this -> aRegisteredContactTypes AS $sKey => $sValue ) {
                $aTopToggleEllements[ _t('_' . $sKey) ] = array
                (
                    'href'      => $sRequest . '&mode=' . $this -> aMailBoxSettings['mailbox_mode'] . '&contacts_mode=' . $sKey . $sRecipientParam,
                    'dynamic'   => true,
                    'active'    => ($sSelectedItem == $sKey ),
                );
            }

            // generate bottom pagination section ;
            $iAllContactPages = ceil( $this -> iTotalContactsCount / $this -> iContactsPerPage );
            $aNeededParameters = array
            (
                'contacts_mode', 'recipient_id'
            );

            // generate needed `GET` parameters ;
            $sRequest = 'mail.php?';
            foreach( $aNeededParameters AS $sKey ) {
                if ( $this -> aMailBoxSettings[$sKey] )
                    $sRequest .= $sKey . '=' . $this -> aMailBoxSettings[$sKey] . '&';
            }

            // next button ;
            if ( $this -> aMailBoxSettings['contacts_page'] <  $iAllContactPages ) {
                $aBottomToggleEllements[ _t('_Next') ] = array
                (
                    'href'       => $sRequest . 'mode=' . $this -> aMailBoxSettings['mailbox_mode'] . '&contacts_page=' . ($this -> aMailBoxSettings['contacts_page'] + 1),
                    'dynamic'    => true,
                    'class'      => 'moreMembers',
                );
            }

            // prev button;
            if ( $this -> aMailBoxSettings['contacts_page'] >  1 ) {
                $aBottomToggleEllements[ _t('_Back') ] = array
                (
                    'href'       => $sRequest . 'mode=' . $this -> aMailBoxSettings['mailbox_mode'] . '&contacts_page=' . ($this -> aMailBoxSettings['contacts_page'] - 1),
                    'dynamic'    => true,
                    'class'      => 'backMembers',
                );
            }

            if ( empty($aMembersList) )
                $sOutputHtml = MsgBox(_t( '_Empty' ));

            return array(  $sOutputHtml, $aTopToggleEllements, $aBottomToggleEllements, false);
        }

        /**
         * Function will generate archive messages rows;
         *
         * @return        : Html presentation data;
         */
        function genArchiveMessages()
        {
            global  $oSysTemplate,
                    $oFunctions,
                    $site;

            // ** INTERNAL FUNCTIONS ;

            /**
             * Function will generate the pagination's item ;
             *
             * @param        : $sItemType (string)     - item's name;
             * @param        : $oObject (object)     - link on current created object;
             * @return        : Html presentation data;
             */
            function _genPaginationItem( $sItemType, $oObject )
            {
                global  $oSysTemplate;

                // init some needed variables ;
                $sOutputHtml = null;

                switch($sItemType) {
                    case 'next' :

                        $sLinkValue  = _t('_Next');
                        $iNeededPage = $oObject -> aMailBoxSettings['contacts_page'] + 1;

                        $aTemplateKeys = array
                        (
                            'item_img_src'           => getTemplateIcon('next.png'),
                            'item_img_alt'           => $sLinkValue,
                            'item_img_css_class'     => 'bot_icon_right',
                            'item_link_action'       => "oMailBoxArchive.getPaginatePage('{$iNeededPage}');return false",
                            'item_link_css_class'    => 'moreMembers',
                            'item_link_href'         => 'mail.php',
                            'item_link_value'        => $sLinkValue,
                        );
                    break;
                    case 'prev' :
                        $sLinkValue  = _t('_Back');
                        $iNeededPage = $oObject -> aMailBoxSettings['contacts_page'] - 1;

                        $aTemplateKeys = array
                        (
                            'item_img_src'            => getTemplateIcon('back.png'),
                            'item_img_alt'            => $sLinkValue,
                            'item_img_css_class'      => 'bot_icon_left',
                            'item_link_action'        => "oMailBoxArchive.getPaginatePage('{$iNeededPage}');return false",
                            'item_link_css_class'     => 'backMembers',
                            'item_link_href'          => 'mail.php',
                            'item_link_value'         => $sLinkValue,
                        );
                    break;
                }

                if ( is_array($aTemplateKeys) ) {
                    $sOutputHtml = $oSysTemplate
                        -> parseHtmlByName( $oObject -> aUsedTemplates['archives_pagina_items'], $aTemplateKeys );
                }

                return $sOutputHtml;
            }

            // init some needed variables;

            $sFunctionName     = null;
            $sOutputHtml     = null;
            $sPaginationHtml = null;

            // set default top toggle menu item
            $aRegisteredContacts_keys = array_keys($this -> aRegisteredArchivesTypes);
            $sDefaultItem     = $aRegisteredContacts_keys[0];

            if ( !$this -> aMailBoxSettings['contacts_mode'] )
                $this -> aMailBoxSettings['contacts_mode'] = $sDefaultItem;

            // contain all found messages from member ;
            $aMessagesList            = array();

            // define number of maximum members list for per page ;
            if( $this -> aMailBoxSettings['contacts_page'] < 1 )
                $this -> aMailBoxSettings['contacts_page'] = 1;

            $sLimitFrom = ( $this -> aMailBoxSettings['contacts_page'] - 1 ) * $this -> iContactsPerPage;
            $sSqlLimit = "LIMIT {$sLimitFrom}, {$this -> iContactsPerPage}";

            if($this->aMailBoxSettings['contacts_mode'] && array_key_exists($this -> aMailBoxSettings['contacts_mode'], $this -> aRegisteredArchivesTypes))
                $sFunctionName  = $this -> aRegisteredArchivesTypes[$this -> aMailBoxSettings['contacts_mode']];

            // default function name ;
            if (!$sFunctionName )
                $sFunctionName = $this -> aRegisteredArchivesTypes[$sDefaultItem];

            // generate the block of contacts archive;
            if ( method_exists($this, $sFunctionName) ) {
                // call registered method;
                $aMessagesList = $this -> $sFunctionName( $sSqlLimit );
                if ( is_array($aMessagesList) and !empty($aMessagesList) ) {
                    foreach($aMessagesList AS $iKey => $aItems ) {
                        $sMemberIcon         = get_member_thumbnail($aItems['Sender'], 'left');
                        $sMessageSubject     = (mb_strlen($aItems['Subject']) > $this -> iArchivesSubjectLength)
                            ? mb_substr($aItems['Subject'], 0, $this -> iArchivesSubjectLength) . '...'
                            : $aItems['Subject'];

                        $sMessageLink = ( $aItems['ID'] == $this -> aMailBoxSettings['messageID'] )
                            ? $sMessageSubject
                            : '<a href="mail.php?mode=view_message&messageID=' . $aItems['ID']. '">' . $sMessageSubject . '</a>';

                        $aMessageKeys[] = array
                        (
                            'member_icon'       => $sMemberIcon,

                            'current_page'      => 'mail.php',
                            'message_id'        => $aItems['ID'],
                            'message_owner'     => $aItems['Sender'],
                            'message_subject'   => $sMessageLink,

                            'message_date'      => $aItems['When'],
                            'message_new_src'   => ( $aItems['New'] ) ? getTemplateIcon('new_message.png')  : getTemplateIcon(null),
                        );
                    }

                    // build final template ;
                    $aTemplateKeys = array
                    (
                        'contacts_mode'      => $this -> aMailBoxSettings['contacts_mode'],
                        'message_id'         => $this -> aMailBoxSettings['messageID'],
                        'bx_repeat:messages' => $aMessageKeys,
                    );

                    $sOutputHtml .= $oSysTemplate -> parseHtmlByName( $this -> aUsedTemplates['archives_section'], $aTemplateKeys );
                }
            }

            // generate the bottom pagination section ;
            $sPaginationHtml = '';
            $iAllContactPages = ceil( $this -> iTotalContactsCount / $this -> iContactsPerPage );
            if($iAllContactPages > 1) {
                $aNeededParameters = array (
                    'contacts_mode', 'messageID',
                );

                // generate needed `GET` parameters ;
                $sRequest = 'mail.php?';
                foreach($aNeededParameters AS $sKey)
                    $sRequest .= $sKey . '=' . $this -> aMailBoxSettings[$sKey] . '&';

                // build the pagination template;
                $aTemplateKeys = array (
                    'prev_link' => ( $this -> aMailBoxSettings['contacts_page'] >  1 ) ? _genPaginationItem('prev', $this) : null,
                    'next_link' => ( $this -> aMailBoxSettings['contacts_page'] <  $iAllContactPages ) ? _genPaginationItem('next', $this) : null,
                );

                $sPaginationHtml .= $oSysTemplate -> parseHtmlByName( $this -> aUsedTemplates['archives_pagina_section'], $aTemplateKeys );
            }

            //archives_pagina_section
            if ( empty($sOutputHtml) )
                $sOutputHtml = MsgBox(_t( '_Empty' ));

            return $sOutputHtml . $sPaginationHtml;
        }

        /**
         * Function will generate the messages rows ;
         *
         * @return         : Html presentation data ;
         */
        function genMessagesRows()
        {
            global $oSysTemplate;
            global $oFunctions;
            global $site;

            // init some needed variables ;

            $sOutputHtml         = null;
            $sMessageBoxActions  = null;
            $sMessagesTypesList  = null;
            $sPerPageBlock       = null;

            $aSortToglleElements = array
            (
                'date_sort_toggle', 'subject_sort_toggle',
                'type_sort_toggle', 'author_sort_toggle',
            );

             $aMessageRows  = array();

            // language keys ;
            $aLanguageKeys = array
            (
                'author'     => _t( '_Author' ),
                'type'       => _t( '_Type' ),
                'subject'    => _t( '_Subject' ),
                'date'       => _t( '_Date' ),
                'new'        => _t( '_new' ),
                'contact'        => _t( '_Contact' ),
                'select'     => _t( '_Select' ),
                'all'        => _t( '_All' ),
                'none'       => _t( '_None' ),
                'read'       => _t( '_Read' ),
                'unread'     => _t( '_Unread' ),
                'delete'     => _t( '_Delete' ),
                'spam'       => _t( '_Report' ),
                'more'       => _t( '_More actions' ),
                'mark_read'  => _t( '_Mark as old' ),
                'mark_unread'=> _t( '_Mark as New' ),
                'restore'    => _t( '_Restore' ),
                'click_sort' => _t( '_Click to sort' ),
                'recipient'	 => _t( '_Recipient' ),
            );

            // get messages array ;
            $aMessages = &$this -> getMessages();

            // generate list of messages types
            if ( is_array($this -> aRegisteredMessageTypes) and !empty($this -> aRegisteredMessageTypes) ) {
                foreach( $this -> aRegisteredMessageTypes AS $iKey => $sRegisteredType ) {
                    $sChecked = null;
                    if ( !empty($this -> aReceivedMessagesTypes) and in_array($sRegisteredType, $this -> aReceivedMessagesTypes) ) {
                        $sChecked = ' checked="checked" ';
                    }

                    $aTemplateKeys = array
                    (
                        'letters_type'         => $sRegisteredType,
                        'letters_type_caption' => _t( '_' . $sRegisteredType ),
                        'checked'              => $sChecked,
                    );

                    $sMessagesTypesList .= $oSysTemplate
                         -> parseHtmlByName( $this -> aUsedTemplates['messages_types_list'], $aTemplateKeys );
                }
                unset($aTemplateKeys);
            }

            // processing all messages ;
            if ( is_array($aMessages) and !empty($aMessages) ) {
                    // need for row devide ;
                    $iIndex = 1;
                    foreach( $aMessages as $iKey => $aItems ) {
                        // generate image and keyword for type of message ;
                        $sTypeIcon = getTemplateIcon( $this -> sMessageIconPrefix . $aItems['Type'] . $this -> sMessageIconExtension );
                        $sTypeLang = _t( '_' . $aItems['Type'] );

                        // get message's subject ;
                        $sSubject = ( mb_strlen($aItems['Subject']) > $this -> iMessageSubjectLength )
                            ? mb_substr($aItems['Subject'], 0, $this -> iMessageSubjectLength) . '...'
                            : $aItems['Subject'];

                        // get message's description ;
                        $sDescription = strip_tags($aItems['Text']);
                        ( mb_strlen($sDescription) > $this -> iMessageDescrLength )
                            ? $sDescription = mb_substr($sDescription, 0, $this -> iMessageDescrLength) . '...'
                            : null;

                        // generate the `new` message's icon ;
                        $sNewMessageImg = ($aItems['New']) ? getTemplateIcon('new_message.png') : getTemplateIcon(null);

                        // color devider ;
                        $sFiledCss = !( $iIndex % 2 ) ? 'filled' : 'not_filled';

                        $aProfileInfo = $this -> aMailBoxSettings['mailbox_mode'] != 'outbox'
                            ? getProfileInfo($aItems['Sender'])
                            : getProfileInfo($aItems['Recipient']);

                        $sMemberIcon     = $this -> aMailBoxSettings['mailbox_mode'] != 'outbox'
                            ? get_member_thumbnail($aItems['Sender'], 'left')
                            : get_member_thumbnail($aItems['Recipient'], 'left');

                        $sMemberLocation = $this -> aMailBoxSettings['mailbox_mode'] != 'outbox'
                            ? getProfileLink($aItems['Sender'])
                            : getProfileLink($aItems['Recipient']);

                        $sMemberNickName  = getNickName($aProfileInfo['ID']);
                        $sMemberAge = ( $aProfileInfo['DateOfBirth'] != "0000-00-00" )
                            ? _t( "_y/o", age($aProfileInfo['DateOfBirth']) )
                            : null;

                        $sMemberCountry =  $aProfileInfo['Country'];
                        $sMemberFlag    =  $site['flags'] . strtolower($sMemberCountry) . $this -> sMembersFlagExtension;
                        $sMemberSexImg  =  $oFunctions -> genSexIcon($aProfileInfo['Sex']);

                        if ( $sMemberCountry )
                            $sMemberCountryFlag = '<img src="' . $sMemberFlag . '" alt="' . $sMemberCountry . '" />';

                        // generate the message status ;
                        $sMessageStatus = ($aItems['New']) ? 'unread' : 'read';

                        $aMessageRows[] = array
                         (
                            'message_id' => $aItems['ID'],
                            'message_status' => $sMessageStatus,
                            'message_owner' => $aItems['Sender'],
                            'message_link' => $aItems['ID'],
                            'message_page' => 'mail.php',

                            'member_icon' => $sMemberIcon,
                            'member_location' => $sMemberLocation,
                            'member_nickname' => $sMemberNickName,
                            'member_sex_img' => $sMemberSexImg,
                            'member_sex' => $aProfileInfo['Sex'],
                            'member_age' => $sMemberAge,
                            'member_flag' => $sMemberCountryFlag,
                            'member_country' => $sMemberCountry,

                            'message_type' => $sTypeLang,
                            'message_type_icon' => $sTypeIcon,

                            'message_subject' => $sSubject,
                            'bx_if:show_new' => array(
                                'condition' => $aItems['New'],
                                'content' => array()
                            ),
                             'message_descr' => $sDescription,

                            'message_new_img' => $sNewMessageImg,
                            'message_new' => $aLanguageKeys['new'],
                            'message_contact_url' => BX_DOL_URL_ROOT . "mail.php?mode=compose&recipient_id=" . $aProfileInfo['ID'],
                            'message_contact' => $aLanguageKeys['contact'],

                            'message_date' => $aItems['Date'],
                             'message_ago' => defineTimeInterval($aItems['DateTS']),
                            'filled_class' => $sFiledCss,
                         );
                        $iIndex++;
                    }
                }

                // init sort toggle ellements ;
                switch ( $this -> aMailBoxSettings['sort_mode'] ) {
                    case 'date' :
                        $aSortToglleElements['date_sort_toggle'] = 'toggle_up';
                    break;
                    case 'date_desc' :
                       $aSortToglleElements['date_sort_toggle'] = 'toggle_down';
                    break;
                    case 'subject' :
                       $aSortToglleElements['subject_sort_toggle'] = 'toggle_up';
                    break;
                    case 'subject_desc' :
                        $aSortToglleElements['subject_sort_toggle'] = 'toggle_down';
                    break;
                    case 'type' :
                        $aSortToglleElements['type_sort_toggle'] = 'toggle_up';
                    break;
                    case 'type_desc' :
                        $aSortToglleElements['type_sort_toggle'] = 'toggle_down';
                    break;
                    case 'author' :
                        $aSortToglleElements['author_sort_toggle'] = 'toggle_up';
                    break;
                    case 'author_desc' :
                        $aSortToglleElements['author_sort_toggle'] = 'toggle_down';
                    break;
                }

                // generate the pagination ;
                $sRequest = BX_DOL_URL_ROOT . 'mail.php?';

                // need for additional parameters ;
                $aGetParams = array('mode', 'sorting', 'messages_types');
                if ( is_array($aGetParams) and !empty($aGetParams) )
                    foreach($aGetParams AS $sValue )
                        if ( isset($_GET[$sValue]) ) {
                            $sRequest .= '&amp;' . $sValue . '=' . $_GET[$sValue];
                        }

                $sCuttedUrl = $sRequest;
                $sRequest = $sRequest . '&amp;page={page}&amp;per_page={per_page}';
                $oPaginate = new BxDolPaginate
                (
                    array
                    (
                        'page_url'   => $sRequest,
                        'count'      => $this -> iTotalMessageCount,
                        'per_page'   => $this -> aMailBoxSettings['per_page'],
                        'sorting'    => $this -> aMailBoxSettings['sort_mode'],
                        'page'                 => $this -> aMailBoxSettings['page'],

                        'on_change_page'     => "oMailBoxMessages.getPaginatePage('{$sRequest}')",
                        'on_change_per_page' => "oMailBoxMessages.getPage(this.value, '{$sCuttedUrl}')",
                    )
                );
                $sPagination = $oPaginate -> getPaginate();

                // generate messages section
                if ( !empty($aMessageRows) ) {
                        $aTemplateKeys = array
                        (
                            'per_page'        => $this -> aMailBoxSettings['per_page'],
                            'page_number'     => $this -> aMailBoxSettings['page'],
                            'page_mode'       => $this -> aMailBoxSettings['mailbox_mode'],
                            'messages_types'  => $this -> aMailBoxSettings['messages_types'],

                            'messages_types_list' => $sMessagesTypesList,
                            'per_page_block'      =>  $oPaginate -> getPages(),

                            'author'             => $this -> aMailBoxSettings['mailbox_mode'] != 'outbox'
                                ? $aLanguageKeys['author']
                                : $aLanguageKeys['recipient'],

                            'type'               => $aLanguageKeys['type'],
                            'subject'            => $aLanguageKeys['subject'],
                            'date'               => $aLanguageKeys['date'],
                            'click_sort'         => $aLanguageKeys['click_sort'],
                            'bx_repeat:messages' => $aMessageRows,

                            'sort_date'       => ( $this -> aMailBoxSettings['sort_mode'] == 'date' )     ? 'date_desc'     : 'date',
                            'sort_subject'    => ( $this -> aMailBoxSettings['sort_mode'] == 'subject' )  ? 'subject_desc'  : 'subject',
                            'sort_type'       => ( $this -> aMailBoxSettings['sort_mode'] == 'type' )     ? 'type_desc'     : 'type',
                            'sort_author'     => ( $this -> aMailBoxSettings['sort_mode'] == 'author' )   ? 'author_desc'   : 'author',

                            'date_sort_toggle_ellement'      => $aSortToglleElements['date_sort_toggle'],
                            'subject_sort_toggle_ellement'   => $aSortToglleElements['subject_sort_toggle'],
                            'type_sort_toggle_ellement'      => $aSortToglleElements['type_sort_toggle'],
                            'author_sort_toggle_ellement'    => $aSortToglleElements['author_sort_toggle'],

                            'current_page'      => 'mail.php',
                            'select'            => $aLanguageKeys['select'],
                            'all_messages'      => $aLanguageKeys['all'],
                            'none_messages'     => $aLanguageKeys['none'],
                            'read_messages'     => $aLanguageKeys['read'],
                            'unread_messages'   => $aLanguageKeys['unread'],

                            'pagination_block' => $sPagination,
                        );

                        // generate extended mailbox actions
                        switch($this -> aMailBoxSettings['mailbox_mode']) {
                            case 'inbox' :
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
                                            array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['delete'],
                                                'attrs'     => array('onclick' => 'if ( typeof oMailBoxMessages != \'undefined\' ) oMailBoxMessages.deleteMessages(\'messages_container\', \'genMessagesRows\')', 'class' => 'bx-btn-small'),
                                            ),
                                            array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['spam'],
                                                'attrs'     => array('onclick' => 'if (typeof oMailBoxMessages != \'undefined\') oMailBoxMessages.spamMessages(\'messages_container\')', 'class' => 'bx-btn-small'),
                                            ),
                                            array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['mark_read'],
                                                'attrs'     => array('onclick' => 'if ( typeof oMailBoxMessages != \'undefined\' ) oMailBoxMessages.markMessages(\'read\', \'genMessagesRows\')', 'class' => 'bx-btn-small'),
                                            ),
                                            array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['mark_unread'],
                                                'attrs'     => array('onclick' => 'if ( typeof oMailBoxMessages != \'undefined\' ) oMailBoxMessages.markMessages(\'unread\', \'genMessagesRows\')', 'class' => 'bx-btn-small'),
                                            )
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sMessageBoxActions =  $oForm -> getCode();
                                break;

                            case 'outbox' :
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
                                                'attrs'     => array('onclick' => 'if ( typeof oMailBoxMessages != \'undefined\' ) oMailBoxMessages.deleteMessages(\'messages_container\', \'genMessagesRows\')', 'class' => 'bx-btn-small'),
                                            ),
                                        )
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sMessageBoxActions =  $oForm -> getCode();
                                break;

                            case 'trash' :
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
                                                'value'     => $aLanguageKeys['restore'],
                                                'attrs'     => array('onclick' => 'if ( typeof oMailBoxMessages != \'undefined\' ) oMailBoxMessages.restoreMessages(\'messages_container\', \'genMessagesRows\')', 'class' => 'bx-btn-small'),
                                            ),
                                            1 => array (
                                                'type'      => 'button',
                                                'value'     => $aLanguageKeys['delete'],
                                                'attrs'     => array('onclick' => 'if ( typeof oMailBoxMessages != \'undefined\' ) oMailBoxMessages.hideDeletedMessages(\'messages_container\', \'genMessagesRows\')', 'class' => 'bx-btn-small'),
                                            ),
                                        ),
                                    )
                                );

                                $oForm = new BxTemplFormView($aForm);
                                $sMessageBoxActions =  $oForm -> getCode();
                                break;
                        }

                        $aTemplateKeys['messages_actions_block'] = $sMessageBoxActions;

                        //return builded rows ;
                        $sMessagesSection  = $oSysTemplate -> parseHtmlByName( $this -> aUsedTemplates['messages_box'], $aTemplateKeys );
                        $sPerPageBlock = $oPaginate -> getPages();
                } else
                    $sMessagesSection = MsgBox( _t('_Empty') );

                // generate mailboxe's top section ;
                $sTopControls = $oSysTemplate -> parseHtmlByName('mail_box_messages_list_top_controls.html', array(
                    'messages_types_list' => $sMessagesTypesList,
                    'per_page_block' => $sPerPageBlock,
                ));

                $aTemplateKeys = array (
                    'per_page' => $this -> aMailBoxSettings['per_page'],
                    'page_number' => $this -> aMailBoxSettings['page'],
                    'page_mode' => $this -> aMailBoxSettings['mailbox_mode'],
                    'messages_types' => $this -> aMailBoxSettings['messages_types'],
                    'messages_sort' => $this -> aMailBoxSettings['sort_mode'],
                    'top_controls' => $sTopControls,
                    'messages_section' => $sMessagesSection
                );

                $sOutputHtml = $oSysTemplate -> parseHtmlByName('mail_box_messages_list.html', $aTemplateKeys);

                // return all builded data ;
                return $sOutputHtml;
        }

        /**
         * Function will generate window with reply message or new compose message;
         *
         * @param        : $iRecipientID (integer) - recipient's ID ;
          * @param        : $iMessageID (integer) - message ID (optional parameter);
         * @return        : Html presentation data;
         */
        function genReplayMessage($iRecipientID, $iMessageID = 0)
        {
            global $oSysTemplate;

            $iMessageID = (int) $iMessageID;
            $iRecipientID = (int) $iRecipientID;

            // check if send message is allowed
            $aComposeSettings = array();
            $sErrMsg = $this->sendMessage('', '', $iRecipientID, $aComposeSettings, true);
            if ($sErrMsg)
                return $sErrMsg;

            // init some needed variables ;
            $sOutputHtml     = '';
            $aMemberInfo     = getProfileInfo($this->aMailBoxSettings['member_id']);
            $aRecipientInfo  = getProfileInfo($iRecipientID);

            $aLanguageKeys = array
            (
                'information'   => ( $iMessageID ) ? _t( '_Reply' ) : _t( '_COMPOSE_H1' ),
                'cancel'        => bx_js_string( _t('_Cancel') ),
                'send'          => bx_js_string( _t('_Send') ),
                'send_copy'     => _t( '_Send copy to personal email', $aRecipientInfo['NickName'] ),
                'send_copy_my'  => _t( '_Send copy to my personal email' ),
                'notify'        => _t( '_Notify by e-mail', $aRecipientInfo['NickName'] ),
                'error_message' => bx_js_string( _t('_please_fill_next_fields_first') ),
            );

            if ( !empty($aMemberInfo) && !empty($aRecipientInfo) ) {
                // ** generate recipient's information ;

                $sMemberIcon         = get_member_thumbnail($this->aMailBoxSettings['member_id'], 'none');
                $sMemberNickName     = getNickName($aMemberInfo['ID']);
                $sMemberLocation     = getProfileLink($aMemberInfo['ID']);

                $sCurrentDate        = date('d.m.Y G:i');

                $sMessageSubject = ( $iMessageID )
                    ? $this->addReToSubject(db_value
                        (
                            "
                            SELECT
                                `Subject`
                            FROM
                                `sys_messages`
                            WHERE
                                `ID` = {$iMessageID}
                                    AND
                                (
                                `Sender` = {$this -> aMailBoxSettings['member_id']}
                                    OR
                                `Recipient` = {$this -> aMailBoxSettings['member_id']}
                                )
                            "
                        ))
                    : null;

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
                                    'value'     => $aLanguageKeys['send'],
                                    'attrs'     => array('onclick' => 'if(typeof oMailBoxReplayMessage != \'undefined\') oMailBoxReplayMessage.sendMessage(' . $iRecipientID . ')'),
                                ),
                                1 => array (
                                    'type'      => 'button',
                                    'value'     => $aLanguageKeys['cancel'],
                                    'attrs'     => array('onclick' => 'if(typeof oMailBoxReplayMessage != \'undefined\') oMailBoxReplayMessage.cancelReplay()'),
                                ),
                            )
                        )
                    );

                $oForm = new BxTemplFormView($aForm);
                $sMessageBoxActions =  $oForm -> getCode();

                $aTemplateKeys = array
                (
                    'error_message'       => $aLanguageKeys['error_message'],
                    'current_page'        => 'mail.php',

                    'information'         => $aLanguageKeys['information'],

                    'member_thumbnail'    => $sMemberIcon,
                    'member_nick_name'    => $sMemberNickName,
                    'member_location'     => $sMemberLocation,

                    'date_create'         => $sCurrentDate,

                    'message_subject'     => $sMessageSubject,

                    'send_copy_my'        => $aLanguageKeys['send_copy_my'],
                    'send_copy_to'        => $aLanguageKeys['send_copy'],
                    'notify'              => $aLanguageKeys['notify'],

                    'action_buttons'      => $sMessageBoxActions,
                );

                $sOutputHtml  = $oSysTemplate
                    -> parseHtmlByName( $this -> aUsedTemplates['message_replay'], $aTemplateKeys );
            }

            bx_import('BxDolEditor');
            $oEditor = BxDolEditor::getObjectInstance();
            $sEditor = '';
            if ($oEditor) {
                $oEditor->setCustomConf ("remove_script_host: false,\nrelative_urls: false,\n");
                $sEditor = $oEditor->attachEditor ('#compose_message', BX_EDITOR_FULL, $this->bAjaxMode);
            }

            return $sEditor . $sOutputHtml;
        }
        /**
         * Adds 'Re: ' or 'Re[n]: ' to the beginning of message subject
         * @param $sSubject Message subject
         * @return string
         */
        function addReToSubject($sSubject)
        {
            if (preg_match('/^(Re)(\[(\d+)\])?\:\s?(.*)$/i', $sSubject, $aMatches)) {
                //echoDbg($aMatches);
                $iRe = empty($aMatches[3]) ? 1 : (int)$aMatches[3];

                $sSubject = $aMatches[1] . '[' . ($iRe + 1) . ']: ' . $aMatches[4];
            } else {
                $sSubject = 'Re: ' . $sSubject;
            }

            return $sSubject;
        }

        /**
         * Function will get list with users nicknames ;
         *
         * @param        : $sQuery (string)  - any part of needed nickname ;
         * @param        : $iLimit (integer) - limit of returned rows (optional parameter);
         * @return        : Html presentation data ;
        */

        function getAutoCompleteList($sQuery, $iLimit = 10 )
        {
            $aFields = array ('NickName');
            bx_import('BxDolMemberInfo');
            $o = BxDolMemberInfo::getObjectInstance(getParam('sys_member_info_name'));
            if ($o)
                $aFields = $o->getMemberNameFields();

            // init some needed variables ;
            $iLimit = (int) $iLimit;
            $sQuery = process_db_input($sQuery, BX_TAGS_STRIP);

            $sWhere = '';
            foreach ($aFields as $sField)
                $sWhere .= "`{$sField}` LIKE '%{$sQuery}%' OR ";
            $sWhere .= '0';

            $sQuery  = "SELECT `ID`, `NickName` FROM `Profiles` WHERE $sWhere LIMIT {$iLimit}";
            $rResult = db_res($sQuery);
            $aOutput = array();
            while( true == ($aRow = $rResult->fetch()) )
                $aOutput[] = array('id' => $aRow['ID'], 'value' => getNickName($aRow['ID']));

            return json_encode($aOutput);
        }

        /**
         * Function will send count of new messages with notifications;
         *
         * @param  : $iMemberId (integer) - logged member's Id;
         * @param  : $iOldCount (integer) - received old count of messages (if will difference will generate message)
         * @return : (array)
                [count]     - (integer) number of new messages;
                [message]   - (string) text message ( if will have a new messages );
         */
        public static function get_member_menu_bubble_new_messages($iMemberId, $iOldCount = 0)
        {
            global $oSysTemplate, $oFunctions, $site;

            $iMemberId		 = (int) $iMemberId;
            $iOldCount		 = (int) $iOldCount;

            $iNewMessages    = 0;
            $aNotifyMessages = array();

            if ( $iMemberId ) {

                $sQuery =
                "
                    SELECT
                        `ID`, `Sender`, `Type`
                    FROM
                        `sys_messages`
                    WHERE
                    (
                        `Recipient` = {$iMemberId}
                            AND
                        NOT FIND_IN_SET('Recipient', `Trash`)
                    )
                        AND
                            `New` = '1'
                    ORDER BY
                        `Date`
                ";

                $rResult = db_res($sQuery);
                $aMessages = array();
                while( true == ($aRow = $rResult->fetch()) ) {
                    $aMessages[] = array($aRow['ID'], $aRow['Sender'], $aRow['Type']);
                }

                $iNewMessages  = count($aMessages);

                // if have some difference;
                if ( $iNewMessages > $iOldCount) {
                    // generate notify messages;
                    for( $i = $iOldCount; $i < $iNewMessages; $i++) {
                        $aKeys = array (
                            'sender_thumb'    => $oFunctions -> getMemberIcon($aMessages[$i][1], 'left'),
                            'sender_nickname' => getNickName($aMessages[$i][1]),
                            'message_id'      => $aMessages[$i][0],

                            'sent_key'        => _t( '_Sent you a' ),
                            'letter_key'      => _t( '_' .  $aMessages[$i][2] ),
                        );
                        $sMessage = $oSysTemplate -> parseHtmlByName('mail_box_notify_window.html', $aKeys);

                        $aNotifyMessages[] = array(
                            'message' => $oSysTemplate
                                    -> parseHtmlByName('member_menu_notify_window.html', array('message' => $sMessage))
                        );
                    }
                }
            }

            $aRetEval = array(
                'count'     => $iNewMessages,
                'messages'  => $aNotifyMessages,
            );

            return $aRetEval;
        }

        /**
         * Function will get messages list for member's extra menu;
         *
         * @param  : $iMemberId (integer) - member's Id ;
         * @return : Html presentation data ;
        */
        static function get_member_menu_messages_list( $iMemberId = 0 )
        {
            global $oSysTemplate, $oFunctions;

            $iMemberId = (int) $iMemberId;

            // define the member's menu position ;
            $sExtraMenuPosition = ( isset($_COOKIE['menu_position']) )
                ? $_COOKIE['menu_position']
                : getParam( 'ext_nav_menu_top_position' );

            $aNewMessages  = array();
            $aLanguageKeys = array (
                'sent'       => _t('_Sent'),
                'compose'    => _t('_Compose new letter'),
                'trash'      => _t('_Trash'),
                'inbox'      => _t('_Inbox'),
            );

            // ** Get statistics ;

            // get count of inbox messages;
            $iInboxCount = BxDolMailBox::getCountInboxMessages($iMemberId);

            // get count of sent messages ;
            $iSentCount = BxDolMailBox::getCountSentMessages($iMemberId);

            // get count of trashed messages ;
            $iTrashCount = BxDolMailBox::getCountTrashedMessages($iMemberId);

            //  generate member's new messages list ;
            if ( $iMemberId ) {

                // generate list with unread messages list ;
                $sQuery =
                "
                   SELECT
                        `ID`, `Sender`, `Subject`
                    FROM
                        `sys_messages`
                    WHERE
                        `Recipient`={$iMemberId}
                            AND
                        `New`='1'
                            AND
                        NOT FIND_IN_SET('Recipient', `Trash`)
                    ORDER BY
                        `Date` DESC
                    LIMIT 5
                ";

                $rResult = db_res($sQuery);
                while( true == ($aRow = $rResult->fetch()) ) {
                    $aMemberInfo = getProfileInfo($aRow['Sender']);
                    $sThumb = $oFunctions -> getMemberIcon($aMemberInfo['ID'], 'none');

                    $sSubject = ( mb_strlen($aRow['Subject']) > 40 )
                        ? mb_substr($aRow['Subject'], 0, 40) . '...'
                        : $aRow['Subject'];

                    $aNewMessages[] = array(
                        'sender_link' => getProfileLink($aMemberInfo['ID']),
                        'sender_nick' => getNickName($aMemberInfo['ID']),
                        'msg_caption' => $sSubject,
                        'thumbnail'   => $sThumb,
                        'message_id'  => $aRow['ID'],
                        'site_url'    => $GLOBALS['site']['url'],
                    );
                }
            }

            $aExtraSection = array(
                'go_inbox'       => $aLanguageKeys['inbox'],
                'inbox_count'    => $iInboxCount,

                'go_outbox'      => $aLanguageKeys['sent'],
                'outbox_count'   => $iSentCount,

                'go_trash'       => $aLanguageKeys['trash'],
                'trash_count'    => $iTrashCount,

                'go_compose'     => $aLanguageKeys['compose'],
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

                'bx_repeat:new_message' => $aNewMessages,
            );

            $sOutputCode = $oSysTemplate
                -> parseHtmlByName( 'mail_box_member_menu_messages.html', $aTemplateKeys );

            return $sOutputCode;
        }
    }
