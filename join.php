<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_JOIN_PAGE', 1);

require_once( './inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );

bx_import('BxTemplJoinPageView');

check_logged();

if (isLogged()) {
    header ('Location:' . BX_DOL_URL_ROOT . 'member.php');
    exit;
}

$_page['header'] = _t( '_JOIN_H' );
$_page['header_text'] = _t( '_JOIN_H' );

if(getParam('reg_by_inv_only') == 'on' && getID($_COOKIE['idFriend']) == 0){
    $_page['name_index'] = 0;
    $_page_cont[0]['page_main_code'] = MsgBox(_t('_registration by invitation only'));
    PageCode();
    exit;
}

$_page['name_index'] = 81;
$_ni = $_page['name_index'];

$oJoinView = new BxTemplJoinPageView();
$_page_cont[$_ni]['page_main_code'] = $oJoinView->getCode();

$GLOBALS['oSysTemplate']->addJs(array('join.js', 'jquery.form.min.js'));
$GLOBALS['oSysTemplate']->addCss(array('join.css', 'explanation.css'));
PageCode();
