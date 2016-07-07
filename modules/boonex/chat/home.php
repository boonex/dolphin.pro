<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_MODULES . $aModule['path'] . '/classes/' . $aModule['class_prefix'] . 'Module.php');

global $_page;
global $_page_cont;

$iId = ( isset($_COOKIE['memberID']) && ($GLOBALS['logged']['member'] || $GLOBALS['logged']['admin']) ) ? (int) $_COOKIE['memberID'] : 0;
$iIndex = 57;

$_page['name_index']	= $iIndex;
$_page['css_name']		= '';

$_page['header'] = _t('_chat_page_caption');
$_page['header_text'] = _t('_chat_box_caption', $site['title']);

$oChat = new BxChatModule($aModule);
$_page_cont[$iIndex]['page_main_code'] = $oChat->getContent($iId);

PageCode($oChat->_oTemplate);
