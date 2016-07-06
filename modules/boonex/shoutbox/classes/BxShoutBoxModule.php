<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');
bx_import('BxDolModule');

/**
 * Shoutbox module by BoonEx
 *
 * This module allow user to send messages that will show on site's home page.
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
 * use shoutbox - BX_USE_SHOUTBOX
 *
 *
 *
 * Service methods:
 *
 * Generate shoutbox window.
 *
 * @see BxShoutBoxModule::serviceGetShoutBox();
 *      BxDolService::call('shoutbox', 'get_shoutbox');
 *
 * Alerts:
 *
 * no alerts here;
 *
 */
class BxShoutBoxModule extends BxDolModule
{
	var $sModuleName;

    // contain some module information ;
    var $aModuleInfo;

    // contain path for current module;
    var $sPathToModule;

    // contain logged member's Id;
    var $iMemberId;

    // contain all used templates
    var $aUsedTemplates = array();

    // shoutbox objects
    var $_aObjects = array();

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

        // prepare the location link ;
        $this->sPathToModule = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri();
		$this->sModuleName   = 'bx_' . $aModule['uri'];
        $this->aModuleInfo   = $aModule;
        $this->iMemberId     = getLoggedId();
        $this->_aObjects     = $this->_oDb->getShoutboxObjects();
    }

    /**
     * Write new message;
     *
     * @param $sObject  object name
     * @param $iHandler handler id
     * @return text (error message if have some troubles)
     */
    function actionWriteMessage($sObject, $iHandler)
    {
        if ($this->_checkObjectAndHandler($sObject, $iHandler) && $this->isShoutBoxAllowed($sObject, $iHandler,
                $this->iMemberId, true)
        ) {

            $sMessage = isset($_POST['message'])
                ? htmlentities($_POST['message'], ENT_COMPAT, 'UTF-8', false)
                : '';

            if ($sMessage) {
                // create new message;
                $iMessage = $this->_oDb->writeMessage($sObject, $iHandler, $sMessage, $this->iMemberId, sprintf("%u", ip2long(getVisitorIP())));
				if($iMessage !== false) {
					$oAlert = new BxDolAlerts($this -> sModuleName, 'add', $iMessage, $this -> iMemberId, array('Object' => $sObject, 'Message' => $sMessage));
					$oAlert->alert();
				}

                if (1 == rand(1, 10) && $this->_oConfig->iAllowedMessagesCount) { // "sometimes" delete old messages
                    // delete superfluous messages;
                    $iMessagesCount = $this->_oDb->getMessagesCount($sObject, $iHandler);
                    if ($iMessagesCount > $this->_oConfig->iAllowedMessagesCount) {
                        $this->_oDb->deleteMessages($sObject, $iHandler,
                            $iMessagesCount - $this->_oConfig->iAllowedMessagesCount);
                    }
                }
            } else {
                echo _t('_bx_shoutbox_message_empty');
            }
        } else {
            echo _t('_bx_shoutbox_access_denied');
        }
    }

    /**
     * Block message
     *
     * @param $sObject    object name
     * @param $iHandler   handler id
     * @param $iMessageId integer
     * @return void
     */
    function actionBlockMessage($sObject, $iHandler, $iMessageId = 0)
    {
        $sCallBackMessage = '';
        $iMessageId       = (int)$iMessageId;

        //check membership level
        if ($this->_checkObjectAndHandler($sObject, $iHandler) && $this->isShoutBoxBlockIpAllowed($sObject, $iHandler,
                $this->iMemberId) && $iMessageId > 0
        ) {
            //get message info
            $aMessageInfo = $this->_oDb->getMessageInfo($sObject, $iHandler, $iMessageId);
            if (!$aMessageInfo) {
                $sCallBackMessage = _t('_Error Occured');
            } else {
                //block user IP
                bx_block_ip((int)$aMessageInfo['IP'], $this->_oConfig->iBlockExpirationSec,
                    _t('_bx_shoutbox_ip_blocked'));

                $this->_oDb->deleteMessagesByIp($sObject, $iHandler, $aMessageInfo['IP']);
            }
        } else {
            $sCallBackMessage = _t('_bx_shoutbox_access_denied');
        }

        echo $sCallBackMessage;
    }

    /**
     * Delete message
     *
     * @param $sObject    object name
     * @param $iHandler   handler id
     * @param $iMessageId integer
     * @return void
     */
    function actionDeleteMessage($sObject, $iHandler, $iMessageId = 0)
    {
        $sCallBackMessage = '';
        $iMessageId       = (int)$iMessageId;

        //check membership level
        if ($this->_checkObjectAndHandler($sObject, $iHandler) && $this->isShoutBoxDeleteAllowed($sObject, $iHandler,
                $this->iMemberId) && $iMessageId > 0
        ) {
            if ($this->_oDb->deleteMessage($sObject, $iHandler, $iMessageId)) {
                $this->isShoutBoxDeleteAllowed($sObject, $iHandler, $this->iMemberId, true);
            } else {
                $sCallBackMessage = _t('_Error Occured');
            }
        } else {
            $sCallBackMessage = _t('_bx_shoutbox_access_denied');
        }

        echo $sCallBackMessage;
    }

    /**
     * Get all latest messages;
     *
     * @param  : $sObject - object name
     * @param  : $iHandler - handler id
     * @param  : $iLastMessageId (integer) - last message's Id;
     * @return : (text) - in JSON format;
     */
    function actionGetMessages($sObject, $iHandler, $iLastMessageId = 0)
    {
        $iLastMessageId = (int)$iLastMessageId;
        $aRetArray      = array();

        if ($this->_checkObjectAndHandler($sObject, $iHandler)) {
            $sMessages      = $this->_getLastMessages($sObject, $iHandler, $iLastMessageId);
            $iLastMessageId = $this->_oDb->getLastMessageId($sObject, $iHandler);

            $aRetArray = array(
                'messages'        => $sMessages,
                'last_message_id' => $iLastMessageId,
            );
        }

        echo json_encode($aRetArray);
    }

    /**
     * Generate shoutbox's admin page ;
     *
     * @return : (text) - Html presentation data ;
     */
    function actionAdministration()
    {
        $GLOBALS['iAdminPage'] = 1;

        if (!isAdmin()) {
            header('location: ' . BX_DOL_URL_ROOT);
        }

        $aLanguageKeys = array(
            'settings' => _t('_bx_shoutbox_settings'),
        );

        // try to define globals category number;
        $iId = $this->_oDb->getSettingsCategory('shoutbox_update_time');
        if (!$iId) {
            $sContent = MsgBox(_t('_Empty'));
        } else {
            bx_import('BxDolAdminSettings');

            $mixedResult = '';
            if (isset($_POST['save']) && isset($_POST['cat'])) {
                $oSettings   = new BxDolAdminSettings($iId);
                $mixedResult = $oSettings->saveChanges($_POST);
            }

            $oSettings = new BxDolAdminSettings($iId);
            $sResult   = $oSettings->getForm();

            if ($mixedResult !== true && !empty($mixedResult)) {
                $sResult = $mixedResult . $sResult;
            }

            $sContent = $GLOBALS['oAdmTemplate']
                ->parseHtmlByName('design_box_content.html', array('content' => $sResult));
        }

        $this->_oTemplate->pageCodeAdminStart();
        echo $this->_oTemplate->adminBlock($sContent, $aLanguageKeys['settings']);
        $this->_oTemplate->pageCodeAdmin(_t('_bx_shoutbox_module'));
    }

    /**
     * Generate the shoutbox window
     *
     * @param $sObject  object name
     * @param $iHandler handler id
     */
    function serviceGetShoutBox($sObject = 'bx_shoutbox', $iHandler = 0)
    {
        if (!$this->_checkObjectAndHandler($sObject, $iHandler)) {
            $sObject = 'bx_shoutbox';
        }

        echo $this->_oTemplate->getShoutboxWindow($sObject, $iHandler, $this->sPathToModule
            , $this->_oDb->getLastMessageId($sObject, $iHandler), $this->_getLastMessages($sObject, $iHandler));
    }

    /**
     * Delete messages of removed profile
     *
     * @param $oAlert object
     * @return boolean
     */
    function serviceResponseProfileDelete($oAlert)
    {
        if (!($iProfileId = (int)$oAlert->iObject)) {
            return false;
        }

        $this->_oDb->deleteMessagesByProfile((int)$iProfileId);

        return true;
    }

    /**
     * Update shoutbox objects for a module(s)
     */
    function serviceUpdateObjects($sModuleUri = 'all', $bInstall = true)
    {
        $aModules = $sModuleUri == 'all' ? $this->_oDb->getModules() : array($this->_oDb->getModuleByUri($sModuleUri));

        foreach ($aModules as $aModule) {
            if (!BxDolRequest::serviceExists($aModule, 'get_shoutbox_data')) {
                continue;
            }

            if (!($aData = BxDolService::call($aModule['uri'], 'get_shoutbox_data'))) {
                continue;
            }

            if ($bInstall) {
                $this->_oDb->insertData($aData);
            } else {
                $this->_oDb->deleteData($aData);
            }
        }

        $this->_oDb->clearShoutboxObjectsCache();
    }

    /**
     * Get list of last messages;
     *
     * @param  : $sObject - object name
     * @param  : $iHandler - handler id
     * @param  : $iLastId (integer) - last message's Id;
     * @return : (text) - html presentation data;
     */
    function _getLastMessages($sObject, $iHandler, $iLastId = 0)
    {
        return $this->_oTemplate->getProcessedMessages($this->_oDb->getMessages($sObject, $iHandler, $iLastId)
            , $this->isShoutBoxDeleteAllowed($sObject, $iHandler, $this->iMemberId)
            , $this->isShoutBoxBlockIpAllowed($sObject, $iHandler, $this->iMemberId));
    }

    /**
     * Define all membership actions
     *
     * @return void
     */
    function _defineActions()
    {
        defineMembershipActions(
            array('shoutbox use', 'shoutbox delete messages', 'shoutbox block by ip')
        );
    }

    /**
     * Check membership level for current type if users (use shotbox);
     *
     * @param : $iMemberId (integer) - member's Id;
     * @param : $isPerformAction (boolean) - if isset this parameter that function will amplify the old action's value;
     * @return boolean
     */
    function isShoutBoxAllowed($sObject, $iHandler, $iMemberId, $isPerformAction = false)
    {
        if (isAdmin()) {
            return true;
        }

        if ($this->_aObjects[$sObject]['code_allow_use']) {
            return $this->_runCheckAllowedCustom($sObject, $iHandler, $iMemberId, $isPerformAction,
                $this->_aObjects[$sObject]['code_allow_use']);
        }

        if (!defined('BX_SHOUTBOX_USE')) {
            $this->_defineActions();
        }

        $aCheck = checkAction($iMemberId, BX_SHOUTBOX_USE, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    /**
     * Check membership level for current type if users (delete any of messages in shotbox);
     *
     * @param : $iMemberId (integer) - member's Id;
     * @param : $isPerformAction (boolean) - if isset this parameter that function will amplify the old action's value;
     * @return boolean
     */
    function isShoutBoxDeleteAllowed($sObject, $iHandler, $iMemberId, $isPerformAction = false)
    {
        if (isAdmin()) {
            return true;
        }

        if ($this->_aObjects[$sObject]['code_allow_delete']) {
            return $this->_runCheckAllowedCustom($sObject, $iHandler, $iMemberId, $isPerformAction,
                $this->_aObjects[$sObject]['code_allow_delete']);
        }

        if (!defined('BX_SHOUTBOX_DELETE_MESSAGES')) {
            $this->_defineActions();
        }

        $aCheck = checkAction($iMemberId, BX_SHOUTBOX_DELETE_MESSAGES, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    /**
     * Check membership level for current type if users (block by ip);
     *
     * @param : $iMemberId (integer) - member's Id;
     * @param : $isPerformAction (boolean) - if isset this parameter that function will amplify the old action's value;
     * @return boolean
     */
    function isShoutBoxBlockIpAllowed($sObject, $iHandler, $iMemberId, $isPerformAction = false)
    {
        if (isAdmin()) {
            return true;
        }

        if ($this->_aObjects[$sObject]['code_allow_block']) {
            return $this->_runCheckAllowedCustom($sObject, $iHandler, $iMemberId, $isPerformAction,
                $this->_aObjects[$sObject]['code_allow_block']);
        }


        if (!defined('BX_SHOUTBOX_BLOCK_BY_IP')) {
            $this->_defineActions();
        }

        $aCheck = checkAction($iMemberId, BX_SHOUTBOX_BLOCK_BY_IP, $isPerformAction);

        return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }

    function _checkObjectAndHandler($sObject, &$iHandler)
    {
        $iHandler = (int)$iHandler;

        return isset($this->_aObjects[$sObject]);
    }

    function _runCheckAllowedCustom($sObject, $iHandler, $iMemberId, $isPerformAction, $sCode)
    {
        return eval($sCode);
    }
}
