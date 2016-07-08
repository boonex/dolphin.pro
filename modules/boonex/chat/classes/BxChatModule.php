<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModule.php');

class BxChatModule extends BxDolModule
{
    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        //--- Define Membership Actions ---//
        $aActions = $this->_oDb->getMembershipActions();
        foreach($aActions as $aAction) {
            $sName = 'ACTION_ID_' . strtoupper(str_replace(' ', '_', $aAction['name']));
            if(!defined($sName))
                define($sName, $aAction['id']);
        }
    }
    function getContent($iId)
    {
        $sPassword = $iId > 0 ? $_COOKIE['memberPassword'] : "";

        $aResult = checkAction($iId, ACTION_ID_USE_CHAT, true);
        if($aResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED)
            $sResult = getApplicationContent('chat', 'user', array('id' => $iId, 'password' => $sPassword), true);
        else
            $sResult = MsgBox($aResult[CHECK_ACTION_MESSAGE]);

        $sResult = DesignBoxContent(_t('_chat_box_caption'), $sResult, 11);

        return $sResult;
    }
}
