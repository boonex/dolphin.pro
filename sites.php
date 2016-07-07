<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

$_page['name_index']	= 7;

check_logged();

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = MainPageCode();

$_page['header'] = _t('_Empty');
$_page['header_text'] = _t('_Empty');

function MainPageCode()
{
    return MsgBox('Sorry, Under Development');
}

PageCode();
