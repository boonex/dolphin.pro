<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModule.php');

class BxBoardModule extends BxDolModule
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
    function getContent($iId, $iSavedId = 0)
    {
        if ($iId > 0) {
            $sPassword = $_COOKIE['memberPassword'];

            $aResult = checkAction($iId, ACTION_ID_USE_BOARD, true);
            if($aResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED)
                $sResult = getApplicationContent('board', 'user', array('id' => $iId, 'password' => $sPassword, 'saved' => $iSavedId), true);
            else
                $sResult = MsgBox($aResult[CHECK_ACTION_MESSAGE]);

            $sResult = DesignBoxContent(_t('_board_box_caption'), $sResult, 11);
        } else
            $sResult = DesignBoxContent(_t('_board_box_caption'), MsgBox(_t('_board_err_not_logged_in')), 11);

        return $sResult;
    }
}
