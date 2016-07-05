<?php

/**
 *
 * Overwrite necessary variables or add new in this file
 *
 *******************************************************************************/

global $gConf;

$dir = array();

$aPathInfo = pathinfo(__FILE__);
require_once($aPathInfo['dirname'] . '/../../../../../inc/header.inc.php');

$path = BX_DIRECTORY_PATH_ROOT . 'modules/boonex/forum/'; // path to orca files

/**
 * forum tweaks
 */
require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');

$gConf['date_format'] = getLocaleFormat(BX_DOL_LOCALE_DATE, BX_DOL_LOCALE_DB); // time/date format

$gConf['fulltext_search'] = getParam('useLikeOperator') ? false : true; // use FULLTEXT search or search using LIKE

/**
 * directories configuration
 */
$gConf['dir']['error_log']   = $path . 'log/orca.error.log'; // error log file path
$gConf['dir']['classes']     = $path . 'classes/'; // classes directiry path
$gConf['dir']['js']          = $path . 'js/'; // js directiry path
$gConf['dir']['inc']         = $path . 'inc/';    // include files path
$gConf['dir']['xmlcache']    = $path . 'xml/'; // not used
$gConf['dir']['xml']         = $path . 'integrations/' . BX_ORCA_INTEGRATION . '/'; // path to integratiom directory
$gConf['dir']['base']        = $path;  // base dir
$gConf['dir']['cache']       = $path . 'cachejs/'; // js files cache
$gConf['dir']['config']      = $path . 'conf/params.conf'; // config
$gConf['dir']['layouts']     = $path . 'layout/'; // layouts dir
$gConf['dir']['editor']      = BX_DIRECTORY_PATH_PLUGINS . 'tiny_mce/'; // path to javascript editor
$gConf['dir']['langs']       = $path . 'integrations/base/langs/'; // lang files locaiton
$gConf['dir']['attachments'] = $path . 'data/attachments/'; // attachments dir

/**
 * skin configuration
 */
$gConf['skin'] = 'uni';
$skin          = isset($_GET['skin']) && $_GET['skin'] ? $_GET['skin'] : (isset($_COOKIE['skin']) ? $_COOKIE['skin'] : (function_exists('db_value') ? db_value("SELECT `VALUE` FROM `sys_options` WHERE `Name` = 'template' LIMIT 1") : ''));
if ($skin && preg_match("/^\w+$/", $skin) && file_exists($path . 'layout/' . $skin)) {
    $gConf['skin'] = $skin;
}

/**
 * language configuration
 */
$gConf['lang'] = isset($_GET['lang']) && $_GET['lang'] ? $_GET['lang'] : (isset($_COOKIE['lang']) ? $_COOKIE['lang'] : '');
if (!$gConf['lang'] || !preg_match("/^[a-z]{2}$/",
        $gConf['lang']) || !file_exists($path . 'layout/base_' . $gConf['lang'])
) {
    if (function_exists('db_value')) {
        $gConf['lang'] = db_value("SELECT `VALUE` FROM `sys_options` WHERE `Name` = ? LIMIT 1", ['lang_default']);
    } else {
        $gConf['lang'] = 'en';
    }
}

/**
 * urls configuration
 */
$gConf['url']['base']        = $site['url'] . 'forum/';    // base url
$gConf['url']['layouts']     = $gConf['url']['base'] . 'layout/'; // layouts url
$gConf['url']['js']          = $gConf['url']['base'] . 'js/'; // layouts url
$gConf['url']['editor']      = $site['plugins'] . 'tiny_mce/'; // url to javascript editor
$gConf['url']['attachments'] = $site['url'] . 'data/attachments/'; // url to attachments

/**
 * langs pathes configuration
 */
if ($gConf['lang'] && file_exists($path . 'layout/' . $gConf['skin'] . '_' . $gConf['lang'])) {
    $gConf['dir']['classes'] = $gConf['dir']['classes'] . $gConf['lang'] . '/';
    $gConf['dir']['js']      = $gConf['dir']['js'] . $gConf['lang'] . '/';
    $gConf['url']['js']      = $gConf['url']['js'] . $gConf['lang'] . '/';
    $gConf['skin']           = $gConf['skin'] . '_' . $gConf['lang'];
}

/**
 * include custom template patches
 */
require_once($gConf['dir']['layouts'] . $gConf['skin'] . '/params.php');

/**
 * database configuration
 */
$gConf['db']['host']   = DATABASE_HOST;    // hostname
$gConf['db']['db']     = DATABASE_NAME;        // database name
$gConf['db']['user']   = DATABASE_USER;    // database username
$gConf['db']['pwd']    = DATABASE_PASS;    // database password
$gConf['db']['port']   = DATABASE_PORT;    // database port
$gConf['db']['sock']   = DATABASE_SOCK;    // database socket
$gConf['db']['prefix'] = 'bx_';       // tables names prefix

function isXsltEnabled()
{
    if (((int)phpversion()) >= 5) {

        if (class_exists('DOMDocument') && class_exists('XsltProcessor')) {
            return true;
        }
    } else {

        if (function_exists('domxml_xslt_stylesheet_file')) {
            return true;
        } elseif (function_exists('xslt_create')) {
            return true;
        }
    }

    return false;
}

if ('auto' == $gConf['xsl_mode']) {
    $gConf['xsl_mode'] = isXsltEnabled() ? 'server' : 'client';
}
