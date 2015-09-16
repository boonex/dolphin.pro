<?php

require_once( './../../../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

$_page['name_index'] = 17;

check_logged();

$_page['header'] = $_page['header_text'] = 'Update Wall Handlers and Shoutbox objects';

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompPageMainCode();

PageCode();

function PageCompPageMainCode()
{
    if (2 == $GLOBALS['MySQL']->getOne("SELECT COUNT(*) FROM `sys_modules` WHERE `uri` IN('spy', 'wall')")) {
        if (!$GLOBALS['MySQL']->getOne("SELECT COUNT(*) FROM `bx_spy_handlers` WHERE `alert_unit` = 'bx_wall' AND `alert_action` = 'post' AND `module_uri` = 'wall' AND `module_class` = 'Module' AND `module_method` = 'get_spy_post'")) {
            BxDolService::call('spy', 'update_handlers', array('wall', true));
            $s = 'Wall handlers for Spy were updated. <hr />';
        } 
        else {
            $s = 'Wall handlers are already updated. <hr />';
        }
    }

    if ($GLOBALS['MySQL']->getOne("SELECT COUNT(*) FROM `sys_modules` WHERE `uri` IN('shoutbox')")) {
        if ($GLOBALS['MySQL']->getOne("SELECT COUNT(*) FROM `bx_shoutbox_objects`") <= 1) {
            BxDolService::call('shoutbox', 'update_objects');
            $s .= 'Shoutbox objects were updated.';
        } 
        else {
            $s .= 'Shoutbox objects are already updated.';
        }
    }

    return DesignBoxContent($GLOBALS['_page']['header'], $s, $GLOBALS['oTemplConfig'] -> PageCompThird_db_num);
}
