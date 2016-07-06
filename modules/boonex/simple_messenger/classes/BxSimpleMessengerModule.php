<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');
bx_import('BxDolUserStatusView');
bx_import('BxDolModule');

require_once('BxSimpleMessengerPrivacy.php');

/**
 * Simple messenger module by BoonEx
 *
 * Simple messenger allows members to send messages, the message's windows are available in member's menu.
 * This is default module and Dolphin can not work properly without this module.
 *
 *
 *
 * Profile's Wall:
 * no wall events
 *
 *
 *
 * Spy:
 * no spy events
 *
 *
 *
 * Memberships/ACL:
 * use simple messenger - BX_USE_SIMPLE_MESSENGER
 *
 *
 *
 * Service methods:
 *
 * Generate messenger's input field into popup action window.
 *
 * @see BxSimpleMessengerModule::serviceGetMessengerField
 *      BxDolService::call('simple_messenger', 'get_messenger_field', array($iViewedMemberId));
 *
 * Generate messenger's core (javascript object);
 * @see BxSimpleMessengerModule::serviceGetMessengerCore
 * BxDolService::call('simple_messenger', 'get_messenger_core', array());
 *
 * Generate privacy form for the currently logged in member;
 * @see BxSimpleMessengerModule::serviceGetSettings
 * BxDolService::call('simple_messenger', 'get_settings', array());
 *
 *
 *
 * Alerts:
 * no alerts here.
 */
class BxSimpleMessengerModule extends BxDolModule
{
    var $sHomeUrl;

	var $sModuleName;

    // contain some module information ;
    var $aModuleInfo;

    // contain some of messenger's engine settings ;
    var $aCoreSettings = array();

    // contain current menu possition (allowed values : top, bottom, fixed)
    var $sMemberMenuPosition = null;

    // logged member's id;
    var $iLoggedMemberId = 0;

    // privacy object;
    var $oPrivacy = null;
    var $iMaxNickLength = 10;

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
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->sHomeUrl    = $this->_oConfig->_sHomeUrl;

		$this->sModuleName = 'bx_' . $aModule['uri'];

        $this->aModuleInfo = $aModule;

        $this->iLoggedMemberId = getLoggedId();

        $this->aCoreSettings = array(
            // time (in seconds) script checks for messages ;
            'update_time'                  => $this->_oConfig->iUpdateTime,

            // number of visible messages in chat box ;
            'number_visible_messages'      => $this->_oConfig->iVisibleMessages,

            // limit of returning messages in message box;
            'limit_returning_messages'     => $this->_oConfig->iCountRetMessages,

            // number of allowed chat boxes;
            'number_of_allowed_chat_boxes' => $this->_oConfig->iCountAllowedChatBoxes,

            // contains block's id where the list of messages will be generated ;
            'output_block'                 => $this->_oConfig->sOutputBlock,

            // page that will procces all ajax queries ;
            'page_receiver'                => BX_DOL_URL_ROOT . 'modules/?r=' . $this->aModuleInfo['uri'] . '/get_operation',

            // contain history block's prefix (need for defines the last message);
            'history_block_prefix'         => $this->_oConfig->sOutputBlockPrefix,

            // save messenger's chat history ;
            'save_chat_history'            => $this->_oConfig->bSaveChatHistory,

            // contain all needed language keys ;
            'language_keys'                => array(
                // This message will be shown when user is trying to send an empty message ;
                'empty_message' => _t('_simple_messenger_empty_message'),
            ),

            // flashing signals amount of the non-active window ;
            'blink_counter'                => $this->_oConfig->iBlinkCounter,

            'message_date_format' => $this->_oConfig->sMessageDateFormat,
        );

        $this->sMemberMenuPosition = (isset($_COOKIE['menu_position']))
            ? $_COOKIE['menu_position']
            : getParam('ext_nav_menu_top_position');

        $this->oPrivacy = new BxSimpleMessengerPrivacy($this);
    }

    /**
     * Function will return needed action result ;
     *
     * @param  : $sActionName (string)  - needed action ;
     * @param  : $iRecipientId(integer) - recipient's Id ;
     * @return : (text) Html presentation data;
     */
    function actionGetOperation($sActionName, $iRecipientId = 0)
    {
        $iRecipientId = (int)$iRecipientId;

        // ** INTERNAL FUNCTIONS;

        /**
         * Function will create new message;
         *
         * @param  : $oObject (object)        - current created object;
         * @param  : $iRecipientId (integer) - recipient's Id ;
         * @param  : $sMessage (string)      - sender's message ;
         */
        function _addMessage(&$oObject, $iRecipientId, $sMessage)
        {
            $iRecipientId = (int)$iRecipientId;
            $sMessage     = trim(strip_tags($sMessage));

            if (($iRecipientId && $sMessage)
                && (getProfileInfo($iRecipientId) && $iRecipientId != $oObject->iLoggedMemberId)
            ) {

                // write received message ;
                if (getProfileInfo($iRecipientId)) {

                    $iMessage = $oObject->_oDb->createMessage($oObject->iLoggedMemberId, $iRecipientId, $sMessage);
					if($iMessage !== false) {
						$oAlert = new BxDolAlerts($oObject->sModuleName, 'add', $iMessage, $oObject->iLoggedMemberId, array('RecipientId' => $iRecipientId, 'Message' => $sMessage));
						$oAlert->alert();
					}

                    // check save chat history ;
                    if (!$oObject->aCoreSettings['save_chat_history']) {
                        $oObject->_oDb->deleteMessagesHistory($oObject->iLoggedMemberId,
                            $iRecipientId, $oObject->aCoreSettings['number_visible_messages']);
                    }
                }
            }
        }

        /**
         * Function will the check active chat box;
         *
         * @param : $aChatBoxes (array)     -  registered chat boxes;
         * @param : $iMemberId  (integer)   -  recipient's Id;
         */
        function _checkAllowedAddMessage(&$aChatBoxes, $iMemberId)
        {
            $iMemberId = (int)$iMemberId;
            foreach ($aChatBoxes as $iKey => $iCurMemberId) {
                if ($iCurMemberId == $iMemberId) {
                    return true;
                }
            }

            return false;
        }

        if (!$this->iLoggedMemberId) {
            exit;
        }

        $iRecipientId = (int)$iRecipientId;

        switch ($sActionName) {

            // send message action ;
            case 'send_message' :

                $sMessage = (isset($_POST['message']))
                    ? urldecode($_POST['message'])
                    : '';

                if ($this->oPrivacy->check('contact', $iRecipientId, $this->iLoggedMemberId)) {
                    // get array with all sender's chat boxes;
                    $aActiveSenderChatBoxes = $this->_oDb->getChatBoxesCount($this->iLoggedMemberId);
                    $bSenderSendAllow       = false;

                    // check the sender's  allow rulles for send message ;
                    if (is_array($aActiveSenderChatBoxes) && $aActiveSenderChatBoxes) {
                        if (count($aActiveSenderChatBoxes) < $this->aCoreSettings['number_of_allowed_chat_boxes']) {
                            $bSenderSendAllow = true;
                        } else {
                            // check if sender's id already registered in active chat boxes;
                            if (_checkAllowedAddMessage($aActiveSenderChatBoxes, $iRecipientId)) {
                                $bSenderSendAllow = true;
                            }
                        }
                    } else {
                        $bSenderSendAllow = true;
                    }

                    // get array with all recipients's chat boxes;
                    $aActiveRecipientChatBoxes = $this->_oDb->getChatBoxesCount($iRecipientId);
                    $bRecipientSendAllow       = false;

                    // check the recipient's  allow rulles for reciving new message ;
                    if (is_array($aActiveRecipientChatBoxes) && $aActiveRecipientChatBoxes) {
                        if (count($aActiveRecipientChatBoxes) < $this->aCoreSettings['number_of_allowed_chat_boxes']) {
                            $bRecipientSendAllow = true;
                        } else {
                            // check if sender's id already registered in active chat boxes;
                            if (_checkAllowedAddMessage($aActiveRecipientChatBoxes, $this->iLoggedMemberId)) {
                                $bRecipientSendAllow = true;
                            }
                        }
                    } else {
                        $bRecipientSendAllow = true;
                    }

                    if ($bSenderSendAllow && $bRecipientSendAllow) {
                        // allow to add new message;
                        _addMessage($this, $iRecipientId, $sMessage);
                    } else {
                        header('Content-Type: text/html; charset=utf-8');
                        echo(!$bSenderSendAllow ? _t('_simple_messenger_max_allowed_windows') : _t('_simple_messenger_recipient_max_allowed_windows'));
                    }
                } else {
                    header('Content-Type: text/html; charset=utf-8');
                    echo _t('_simple_messenger_privacy_disallow');
                }
                break;

            case 'get_chat_box' :
                if ($iRecipientId) {

                    // get chat box;
                    $aChatBox = $this->getChatBox($iRecipientId);
                    $sChatBox = $aChatBox['chat_box'];
                    $aRet     = array();

                    // get some sender's info;
                    $aSenderInfo       = getProfileInfo($iRecipientId);
                    $aRet['senders'][] = array(
                        // contain sender id ;
                        'sender_id'          => $iRecipientId,

                        // message block will draw only ones ;
                        'chat_box'           => $sChatBox,

                        // contain messages block's messages list ;
                        'messages_list'      => '',

                        // contain the last update time of status text;
                        'status_update_time' => $aSenderInfo['UserStatusMessageWhen'],

                        // contain sender's status;
                        'sender_status'      => $aSenderInfo['UserStatus'],

                        // last sent message's Id;
                        'last_message'       => $aChatBox['last_message'],

                        // count of sent messages;
                        'count_messages'     => $aChatBox['count_messages'],
                    );

                    // return result in JSON format ;
                    if ($aRet) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode($aRet);
                    }
                }
                break;

            case 'new_messages' :
                $aRet = array();

                // defines all registered messages box and last message's id in it ;
                $sRegisteredBoxes = false != bx_get('registered_chat_boxes')
                    ? bx_get('registered_chat_boxes')
                    : '';

                $aAllBoxes = $sRegisteredBoxes ? explode(',', $sRegisteredBoxes) : '';

                if ($aAllBoxes && is_array($aAllBoxes)) {
                    $aRegBoxes = array();

                    // procces registered box ;
                    foreach ($aAllBoxes as $sValue) {
                        if ($sValue) {
                            $aTemp = explode(':', $sValue);

                            if (is_numeric($aTemp[0]) && $aTemp[0]) {
                                // get some sender's info;
                                $aSenderInfo          = getProfileInfo($aTemp[0]);
                                $aRegBoxes[$aTemp[0]] = array(
                                    'last_message'  => (int)$aTemp[1],
                                    'registered'    => 1,

                                    // sender's status text changed time;
                                    'status_change' => $aSenderInfo['UserStatusMessageWhen'],
                                    // sender's status;
                                    'sender_status' => (get_user_online_status($aSenderInfo['ID']))
                                        ? $aSenderInfo['UserStatus']
                                        : 'offline',
                                );
                            }
                        }
                    }
                }

                // try to define new messages except existing;
                $aNewSenders = $this->_oDb->getNewChatBoxes($this->iLoggedMemberId, $aRegBoxes);

                if ($aNewSenders) {
                    // procces new messages array ;
                    foreach ($aNewSenders as $iKey => $iSenderId) {
                        $iSenderId = (int)$iSenderId;

                        // get some sender's info;
                        $aSenderInfo           = getProfileInfo($iSenderId);
                        $aRegBoxes[$iSenderId] = array(
                            'last_message'  => 0,
                            'registered'    => 0,

                            // sender's status text changed time;
                            'status_change' => $aSenderInfo['UserStatusMessageWhen'],
                            // sender's status;
                            'sender_status' => (get_user_online_status($iSenderId))
                                ? $aSenderInfo['UserStatus']
                                : 'offline',
                        );
                    }
                }

                // preparing all boxes with messages ;
                if ($aRegBoxes) {

                    foreach ($aRegBoxes as $iSenderId => $aItems) {
                        $iSenderId = (int)$iSenderId;

                        $iLastMessagesId = 0;
                        $iCountMessages  = 0;

                        $sChatBox = null;

                        // check the chat box;
                        if (!$aItems['registered']) {
                            $aChatBox = $this->getChatBox($iSenderId);
                            $sChatBox = $aChatBox['chat_box'];

                            $iLastMessagesId = $aChatBox['last_message'];
                            $iCountMessages  = $aChatBox['count_messages'];
                        }

                        // procces all current chat box's messages;
                        if (!$sChatBox) {
                            $aMessagesList = $this->getMessagesHistory($this->iLoggedMemberId,
                                $iSenderId, $aItems['last_message']);

                            $iLastMessagesId = $aMessagesList['last_message'];
                            $iCountMessages  = $aMessagesList['count_messages'];
                            $sMessagesList   = $aMessagesList['messages_list'];
                        } else {
                            $sMessagesList = 'built-in';
                        }

                        $aRet['senders'][] = array(
                            // contain sender id ;
                            'sender_id'          => $iSenderId,

                            // message block will draw only ones ;
                            'chat_box'           => (!$aItems['registered']) ? $sChatBox : '',

                            // contain messages block's messages list ;
                            'messages_list'      => $sMessagesList,

                            // contain the last update time of status text;
                            'status_update_time' => $aItems['status_change'],

                            // contain sender's status;
                            'sender_status'      => $aItems['sender_status'],

                            // last sent message's Id;
                            'last_message'       => $iLastMessagesId,

                            // count of sent messages;
                            'count_messages'     => $iCountMessages,
                        );
                    }
                }

                // return result in JSON format ;
                if ($aRet) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($aRet);
                }
                break;

            case 'close_window' :
                if ($this->_oDb->closeChatWindow($this->iLoggedMemberId, $iRecipientId)) {
                    echo 'closed';
                }
                break;

            case 'get_status_text' :
                $aSenderInfo = getProfileInfo($iRecipientId);
                header('Content-Type: text/html; charset=utf-8');
                echo $aSenderInfo['UserStatusMessage'];
                break;

            case 'get_status' :
                $oUserStatus = new BxDolUserStatusView();
                echo getTemplateIcon($oUserStatus->getStatusIcon($iRecipientId));
                break;

            case 'get_sender_thumb' :
                header('Content-Type: text/html; charset=utf-8');
                echo $GLOBALS['oFunctions']->getMemberThumbnail($iRecipientId, 'none');
                break;
        }
    }

    /**
     * Function will generate member's privacy page;
     *
     * @return : (text) - html presentation data;
     */
    function getPrivacyPage()
    {
        global $site;

        $sOutputCode   = null;
        $sActionResult = null;

        if ($this->iLoggedMemberId) {

            // save changes;
            if (!empty($_POST['allow_contact_to'])) {
                $sActionResult = MsgBox(_t('_simple_messenger_privacy_page_group_created'), 3);
                $this->_oDb->createPrivacyGroup($this->iLoggedMemberId, (int)$_POST['allow_contact_to']);
            }

            $aPrivacyGroups = $this->oPrivacy->getGroupChooser($this->iLoggedMemberId, $this->aModuleInfo['uri'],
                'contact', array(), _t('_simple_messenger_privacy_page_select_group'));

            $aForm = array(
                'form_attrs' => array(
                    'action' => '',
                    'method' => 'post',
                    'name'   => 'simple_messenger',
                ),
                'inputs'     => array(

                    'allow_contact_to' => $aPrivacyGroups,

                    'submit' => array(
                        'type'  => 'submit',
                        'value' => _t('_simple_messenger_privacy_page_submit_value'),
                    ),
                ),
            );

            $mixedValue = $this->_oDb->getPrivacyGroupValue($this->iLoggedMemberId);
            if ((int)$mixedValue > 0) {
                $aForm['inputs']['allow_contact_to']['value'] = (int)$mixedValue;
            }

            $oForm       = new BxTemplFormView($aForm);
            $sOutputCode = $oForm->getCode();
        }

        return $sActionResult . $sOutputCode;
    }

    /**
     * Function will generate chat block for current member ;
     *
     * @param  : $iSender (integer)     - sender member's Id;
     * @return : (array);
     */
    function getChatBox($iSender)
    {
        global $oFunctions;

        $iSender = (int)$iSender;

        $oModuleDb   = new BxDolModuleDb();
        $oUserStatus = new BxDolUserStatusView();

        $sMemberThumb = $oFunctions->getMemberThumbnail($iSender, 'none');
        $sMemberIcon  = $oFunctions->getMemberIcon($iSender, 'none', false, '_plain');

        $aSenderInfo = getProfileInfo($iSender);
        $sSenderLink = getProfileLink($iSender);

        $sStatusIcon = $oUserStatus->getStatusIcon($iSender);

        // language keys;
        $aLanguageKeys = array(
            'minimize'   => _t('_simple_messenger_minimize_button'),
            'close'      => _t('_simple_messenger_close_button'),
            'video_mess' => _t('_simple_messenger_switch_to_video'),
        );

        // contain data for sender block;
        $aSenderBlock = array(
            'sender_thumb' => $sMemberThumb,
            'sender_link'  => $sSenderLink,
            'status_text'  => $aSenderInfo['UserStatusMessage'],
            'sender_nick'  => getNickName($aSenderInfo['ID']),

            'reduce_title' => $aLanguageKeys['minimize'],
            'close_title'  => $aLanguageKeys['close'],

            'history_window_id' => $this->aCoreSettings['history_block_prefix'] . $iSender,
            'sender_id'         => $iSender,

            'bx_if:video_messenger' => array(
                'condition' => ($oModuleDb->isModule('messenger')),
                'content'   => array(
                    'sender_id'       => $this->iLoggedMemberId,
                    'sender_passw'    => getPassword($this->iLoggedMemberId),
                    'recipient_id'    => $iSender,
                    'video_messenger' => $aLanguageKeys['video_mess'],
                ),
            ),
        );

        $aMessagesList = $this->getMessagesHistory($this->iLoggedMemberId, $iSender, 0, false);

        // process nick name;
        $sNickName = getNickName($aSenderInfo['ID']);
        if (mb_strlen($sNickName) > $this->iMaxNickLength) {
            $sNickName = mb_substr($sNickName, 0, $this->iMaxNickLength) . '...';
        }

        $aTemplateKeys = array(

            'block_indent'        => ($this->sMemberMenuPosition == 'bottom') ? 'bottom_indent' : 'top_indent',
            'chat_block_position' => ($this->sMemberMenuPosition == 'bottom') ? 'chat_block_bottom_position' : 'chat_block_top_position',
            'sender_nick'         => $sNickName,
            'sender_icon'         => $sMemberIcon,

            'member_status'     => $sStatusIcon,
            'history_window_id' => $this->aCoreSettings['history_block_prefix'] . $iSender,

            'history_block_position' => ($this->sMemberMenuPosition == 'bottom') ? 'history_bottom_position' : 'history_top_position',
            'recipient_id'           => $iSender,

            'bx_if:menu_pos_top' => array(
                'condition' => ($this->sMemberMenuPosition == 'bottom'),
                'content'   => $aSenderBlock,
            ),

            'bx_if:menu_pos_bottom' => array(
                'condition' => ($this->sMemberMenuPosition != 'bottom'),
                'content'   => $aSenderBlock,
            ),

            // generate all members' chat histories;
            'messages'              => $aMessagesList['messages_list'],
        );

        // generate the chat box's content;
        $sOutputCode = $this->_oTemplate->parseHtmlByName('chat_block.html', $aTemplateKeys);

        $aRetArray = array(
            'chat_box'       => $sOutputCode,
            'last_message'   => $aMessagesList['last_message'],
            'count_messages' => $aMessagesList['count_messages'],
        );

        return $aRetArray;
    }

    /**
     * Function will generate member's messages history ;
     *
     * @param  : $iSender (integer)         - sender member's Id;
     * @param  : $iRecipient (integer)      -  recipient member's Id;
     * @param  : $iLastMessageId (integer)  - last message's Id of current message Block ;
     * @param  : $bLimit (boolean)          - if isset this parameter function will return qualified messages;
     * @return : (array);
     * [messages_list]  - (text) messages list;
     * [last_message]   - (integer) last message's Id;
     * [count_messages] - (integer) count of generated messages;
     */
    function getMessagesHistory($iRecipient, $iSender, $iLastMessageId = 0, $bLimit = true)
    {
        $sOutputMessages = '';

        if ($bLimit) {
            $aMessages = $this->_oDb->getHistoryList($this->aCoreSettings, $iRecipient, $iSender,
                $iLastMessageId, $this->aCoreSettings['limit_returning_messages']);
        } else {
            $aMessages = $this->_oDb->getHistoryList($this->aCoreSettings, $iRecipient,
                $iSender, $iLastMessageId);
        }

        // procces received mesasges;
        $iLastMessageId = 0;
        $iCountMessages = 0;

        if ($aMessages && is_array($aMessages)) {
            foreach ($aMessages as $iKey => $aItems) {
                $iKey = (int)$iKey;

                $iCountMessages++;
                $iLastMessageId = $aMessages[$iKey]['ID'];

                $aTemplateKeys = array(
                    'message'         => $aMessages[$iKey]['Message'],
                    'sender_nickname' => $aMessages[$iKey]['SenderID'] == $this->iLoggedMemberId ? _t('_simple_messenger_me') : getNickName($aMessages[$iKey]['SenderID']),
                    'date_add'        => defineTimeInterval($aMessages[$iKey]['DateTS'], true, true),

                    'owner_nick_extra' => ($aMessages[$iKey]['SenderID'] == $this->iLoggedMemberId)
                        ? 'sender'
                        : 'recipient',
                );

                $sOutputMessages .= $this->_oTemplate
                    ->parseHtmlByName('message.html', $aTemplateKeys);
            }
        }

        if (!$iLastMessageId) {
            $iLastMessageId = $this->_oDb->getLastMessagesId($iRecipient, $iSender);
        }

        $aRetArray = array(
            'messages_list'  => $sOutputMessages,
            'last_message'   => $iLastMessageId,
            'count_messages' => $iCountMessages,
        );

        return $aRetArray;
    }

    /**
     * Function will generate messenger's js core ;
     *
     * @return : (text) - js code;
     */
    function getSimpleMessengerCore()
    {
        $sOutputCode = null;

        if (!$this->iLoggedMemberId) {
            return;
        }

        $this->_oTemplate->addJs(array(
            'emoji-picker/js/jquery.emojipicker.js',
            'emoji-picker/js/jquery.emojipicker.tw.js',
            'messenger_core.js',
        ));
        $this->_oTemplate->addCss(array(
            'plugins/emoji-picker/css/|jquery.emojipicker.css',
            'simple_messenger.css',
            'simple_messenger_phone.css',
        ));

        $this->_oTemplate->addCssAsync('plugins/emoji-picker/css|jquery.emojipicker.tw.css'); // it's toooooooo big, so include it separately

        $sEmptyMessage = bx_js_string(_t('_simple_messenger_empty_message'));
        $sWaitMessage  = bx_js_string(_t('_simple_messenger_wait'));

        $sOutputCode .=
            "
                <script type=\"text/javascript\">
                    $(document).ready(function () {
                        var sMemberMenuOutputBlock = '{$this -> aCoreSettings['output_block']}';

                        // if member menu was defined;
                        $('#' + sMemberMenuOutputBlock).each(
                            function(){
                                oSimpleMessenger.chatBoxSettings =
                                {
                                    // the page which will process all AJAX queries ;
                                    sPageReceiver           :   '{$this -> aCoreSettings['page_receiver']}',

                                    // contain block's id where the list of messages will be generated ;
                                    sOutputBlockId          :   sMemberMenuOutputBlock,

                                    // time (in seconds) script checks for new messages ;
                                    updateTime              :   {$this -> aCoreSettings['update_time']},

                                    // contain descriptor of the created timeout ;
                                    updateTimeNotifyHandler : '',

                                    // the number of visible messages into chat box ;
                                    iNumberVisibleMessages  :   {$this -> aCoreSettings['number_visible_messages']},

                                    // contains history block's prefix (block's name where will add the new messages);
                                    sHistoryBlockPrefix     :   '{$this -> aCoreSettings['history_block_prefix']}',

                                    iParentContainerHeight  : 0,

                                    // current member's menu position ;
                                    sMemberMenuPosition     :   '{$this -> sMemberMenuPosition}',

                                    // wrapper for chat boxes;
                                    sChatBox                :   'simple_messenger_chat_block',

                                    // flashing signals amount of the non-active window ;
                                    iMaxBlinkCounter        :   '{$this -> aCoreSettings['blink_counter']}'
                                };

                                oSimpleMessenger.systemMessages.emptyMessage = '{$sEmptyMessage}';
                                oSimpleMessenger.systemMessages.waitMessage  = '{$sWaitMessage}';

                                var oMenuContainer = $(this).parents('div:first');
                                var iContainerHeight =  parseInt( oMenuContainer.height() );

                                oSimpleMessenger.chatBoxSettings.iParentContainerHeight = (iContainerHeight) ? iContainerHeight + 5 : 0;
                                oSimpleMessenger.oDefinedChatBoxes.boxes  = Array();
                                oSimpleMessenger.messageNotification();
                            }
                        );
                    });
                </script>
            ";

        return $sOutputCode;
    }

    /**
     * Function will generate messenger's input field ;
     * Will generate messenger's part that allow logged member to send message ;
     *
     * @param  : $iViewedMemberId (integer) - Viewed member's Id ;
     * @return : (text) - Html presentation data ;
     */
    function serviceGetMessengerField($iViewedMemberId)
    {
        if (!$this->iLoggedMemberId || !get_user_online_status($iViewedMemberId)
            || $this->iLoggedMemberId == $iViewedMemberId
            || isBlocked($iViewedMemberId, $this->iLoggedMemberId)
        ) {
            return '';
        }

        $sOutputCode = '';
        if ($this->isMessengerAlowed($iViewedMemberId)
            && $this->isMessengerAlowed($this->iLoggedMemberId)
        ) {

            $aTemplateKeys = array(
                'message' => _t('_simple_messenger_chat_now') . '...',
                'res_id'  => $iViewedMemberId,
            );

            $sOutputCode = $this->_oTemplate->parseHtmlByName('send_message_field.html', $aTemplateKeys);
        }

        return $sOutputCode;
    }

    /**
     * Function will get messenger's core code;
     *
     * @return : (text) javascript code;
     */
    function serviceGetMessengerCore()
    {
        $sOutputCode = '';
        if ($this->isMessengerAlowed($this->iLoggedMemberId, true)) {
            $sOutputCode = $this->getSimpleMessengerCore();
        }

        return $sOutputCode;
    }

    /**
     * Function will generate link on member's privacy page;
     *
     * @return : (text) - html presentation data;
     */
    function serviceGetSettings()
    {
        return array($this->getPrivacyPage(), array(), array(), false);
    }

    /**
     * Function will check the current logged member membership level;
     *
     * @param : $iMemberId (integer) - member's Id;
     * @param : $isPerformAction (boolean) - if isset this parameter that function will amplify the old action's value;
     */
    function isMessengerAlowed($iMemberId, $isPerformAction = false)
    {
        //$this -> iLoggedMemberId
        $this->_defineActions();
        $aCheck = checkAction($iMemberId, BX_USE_SIMPLE_MESSENGER, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function _defineActions()
    {
        defineMembershipActions(array('use simple messenger'));
    }

}
