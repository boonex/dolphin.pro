<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

/***************************************************************************
 *
 *   This is a free software; you can modify it under the terms of BoonEx
 *   Product License Agreement published on BoonEx site at http://www.boonex.com/downloads/license.pdf
 *   You may not however distribute it for free or/and a fee.
 *   This notice may not be removed from the source code. You may not also remove any other visible
 *   reference and links to BoonEx Group as provided in source code.
 *
 ***************************************************************************/

require_once('inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'languages.inc.php');

bx_import('BxDolSubscription');

$oSubscription = BxDolSubscription::getInstance();

$aResult = array();
if(isset($_POST['direction'])) {
    $sUnit = process_db_input($_POST['unit']);
    $sAction = process_db_input($_POST['action']);
    $iObjectId = (int)$_POST['object_id'];

    switch($_POST['direction']) {
        case 'subscribe':
            if(isset($_POST['user_id']) && (int)$_POST['user_id'] != 0)
                $aResult = $oSubscription->subscribeMember((int)$_POST['user_id'], $sUnit, $sAction, $iObjectId);
            else if(isset($_POST['user_name']) && isset($_POST['user_email']))
                $aResult = $oSubscription->subscribeVisitor($_POST['user_name'], $_POST['user_email'], $sUnit, $sAction, $iObjectId);
            break;

        case 'unsubscribe':
            if(isset($_POST['user_id']) && (int)$_POST['user_id'] != 0)
                $aResult = $oSubscription->unsubscribeMember((int)$_POST['user_id'], $sUnit, $sAction, $iObjectId);
            else if(isset($_POST['user_name']) && isset($_POST['user_email']))
                $aResult = $oSubscription->unsubscribeVisitor($_POST['user_name'], $_POST['user_email'], $sUnit, $sAction, $iObjectId);
            break;
    }

    header('Content-Type:text/javascript; charset=utf-8');
    echo json_encode($aResult);
} 
else if(isset($_GET['sid'])) {
    $aResult = $oSubscription->unsubscribe(array('type' => 'sid', 'sid' => $_GET['sid']));
    if(isset($_GET['js']) && (int)$_GET['js'] == 1) {
    	header('Content-Type:text/javascript; charset=utf-8');
        echo json_encode($aResult);
        exit;
    }

    $_page['name_index'] = 0;
    $_page['header'] = $GLOBALS['site']['title'];
    $_page['header_text'] = $GLOBALS['site']['title'];
    $_page_cont[0]['page_main_code'] = MsgBox($aResult['message']);

    PageCode();
}
