<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );

// --------------- page variables / login

$_page['name_index'] = 41;
$_page['css_name'] = 'result.css';

$logged['member'] = member_auth( 0, false );

switch ( $_REQUEST['result'] ) {
    case '1000':
        $header	= _t("_RESULT0_H");
        $result_text = _t("_RESULT0_H");
        $desc = _t("_RESULT1000");
    break;
    case '0':
        $header	= _t("_RESULT0_H");
        $result_text = _t("_RESULT0_H");
        $desc = _t("_RESULT0","<a href=\"cart.php?" . time() . "\">");
    break;
    case '-1':
        $header = _t("_RESULT-1_H");
        $result_text = _t("_RESULT-1_A");
        $desc = _t("_RESULT-1_D");
    break;
    case '1':
        $header	= _t("_RESULT1_H");
        $result_text = _t("_RESULT1_THANK", $site['title']);
        $desc = _t("_RESULT1_DESC");
    break;
    case '2':
        $header	= _t("_RESULT1_H");
        $result_text = _t("_RESULT1_THANK", $site['title']);
        $desc = _t("_RESULT2DESC", $site['title']);
    break;
    default:
        exit;
    break;
}

if ( $_POST['result'] == 2 || $_POST['result'] == 3 ) {
    $i = 0;
    while ( $_COOKIE["cartentries$_COOKIE[memberID]"][$i] )
        setcookie( "cartentries$_COOKIE[memberID]" . "[$i]", $_COOKIE[cartentries][$i++], time() - 24*3600, "/" );
}

$_page['header'] = $header;
$_page['header_text'] = $header;

// --------------- page components

$_ni = $_page['name_index'];
$_page_cont[$_ni]['page_main_code'] = PageCompPageMainCode();

// --------------- [END] page components

PageCode();

// --------------- page components functions

/**
 * page code function
 */
function PageCompPageMainCode()
{
    global $result_text;
    global $desc;

    ob_start();

?>
<div class="result_text"><?= $result_text ?></div>
<div class="result_desc"><?= $desc ?></div>
<?php

    $ret = ob_get_contents();
    ob_end_clean();

    return $ret;
}
