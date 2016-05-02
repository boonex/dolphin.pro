<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );

// --------------- page variables and login

check_logged();

$_page['header'] = $_page['header_text'] = _t( "_EXPLANATION_H" ) . ": " . htmlspecialchars_adv(_t("_" . $_GET['explain']));
$_page['css_name'] = 'explanation.css';

$sCode = PageMainCode();

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    header('Content-type:text/html;charset=utf-8');
    echo $GLOBALS['oFunctions']->popupBox('explanation_popup', $_page['header'], $sCode);
    exit;
}

// --------------- page components

$_page['name_index'] = 44;
$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = DesignBoxContent($_page['header_text'], PageMainCode(), $oTemplConfig -> PageExplanation_db_num);

// --------------- [END] page components

PageCode();

// --------------- page components functions

function membershipActionsList($membershipID)
{
    $sNoLimit = _t('_no limit');
    $sqlFields = '';
    $aFields = array('AllowedCount', 'AllowedPeriodLen', 'AllowedPeriodStart', 'AllowedPeriodEnd');
    foreach ($aFields as $sField)
        $sqlFields .= ",IFNULL(`$sField`, '$sNoLimit') as `$sField`";
    $sqlQuery = "
        SELECT `IDAction`, `Name` $sqlFields
        FROM `sys_acl_matrix`
        INNER JOIN `sys_acl_actions` ON `sys_acl_matrix`.`IDAction` = `sys_acl_actions`.`ID`
        WHERE `sys_acl_matrix`.`IDLevel` = ?";
    $aDraw['bx_repeat:actions'] = $GLOBALS['MySQL']->getAll($sqlQuery, [$membershipID]);

    translateMembershipActions($aDraw['bx_repeat:actions']);

    return $GLOBALS['oSysTemplate']->parseHtmlByName('memlevel_actions_list.html', $aDraw);
}

/**
 * Prints HTML Code for explanation
 */
function PageMainCode()
{
    $sCode = '';
    switch ( $_GET['explain'] ) {
        case 'Unconfirmed': $sCode = _t("_ATT_UNCONFIRMED_E"); break;
        case 'Approval': $sCode = _t("_ATT_APPROVAL_E"); break;
        case 'Active': $sCode = _t("_ATT_ACTIVE_E"); break;
        case 'Rejected': $sCode = _t("_ATT_REJECTED_E"); break;
        case 'Suspended': $sCode = _t("_ATT_SUSPENDED_E", $GLOBALS['site']['title']); break;
        case 'membership': $sCode = membershipActionsList((int)$_GET['type']); break;
    }

    return $GLOBALS['oSysTemplate']->parseHtmlByName('default_padding.html', array('content' => $sCode));
}
