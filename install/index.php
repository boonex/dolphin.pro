<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

$aPhpExtensions = array(
    'curl',
    'gd',
    'mbstring',
    'xsl',
    'json',
    'fileinfo',
    'openssl',
    'zip',
    'ftp',
    'calendar',
    'exif',
    'pdo',
    'pdo_mysql'
);

$iMemoryLimitBytes = ini_get('memory_limit');
$last              = strtolower($iMemoryLimitBytes{strlen($iMemoryLimitBytes) - 1});
$iMemoryLimitBytes = (int)$iMemoryLimitBytes;
switch ($last) {
    case 'k':
        $iMemoryLimitBytes *= 1024;
        break;
    case 'm':
        $iMemoryLimitBytes *= 1024 * 1024;
        break;
    case 'g':
        $iMemoryLimitBytes *= 1024 * 1024 * 1024;
        break;
}

$aErrors   = array();
$aErrors[] = (ini_get('register_globals') == 0) ? '' : '<font color="red">register_globals is On (warning, you should have this param in the Off state, or your site will be unsafe)</font>';
$aErrors[] = (ini_get('safe_mode') == 0) ? '' : '<font color="red">safe_mode is On, disable it</font>';
$aErrors[] = (version_compare(PHP_VERSION, '5.4.0', '<')) ? '<font color="red">PHP version too old, please update to PHP 5.4.0 at least</font>' : '';
$aErrors[] = (ini_get('short_open_tag') == 0 && version_compare(phpversion(), "5.4",
        "<") == 1) ? '<font color="red">short_open_tag is Off (must be On!)<b>Warning!</b> Dolphin cannot work without <b>short_open_tag</b>.</font>' : '';
$aErrors[] = (ini_get('allow_url_include') == 0) ? '' : '<font color="red">allow_url_include is On (warning, you should have this param in the Off state, or your site will be unsafe)</font>';
$aErrors[] = ($iMemoryLimitBytes == -1 || $iMemoryLimitBytes >= 128 * 1024 * 1024) ? '' : '<font color="red"><b>memory_limit</b> must be at least 128M</font>';

foreach ($aPhpExtensions as $sExtension) {
    $aErrors[] = !extension_loaded($sExtension) ? '<font color="red"><b>' . $sExtension . '</b> extension isn\'t installed. <b>Warning!</b> Dolphin can\'t work properly without it.</font>' : '';
}

$aErrors = array_diff($aErrors, array('')); //delete empty
if (count($aErrors)) {
    $sErrors = implode(" <br /> ", $aErrors);

    if (!defined('BX_INSTALL_DO_NOT_EXIT_ON_ERROR')) {
        echo <<<EOF
{$sErrors} <br />
Please go to the <br />
<a href="https://www.boonex.com/trac/dolphin/wiki/GenDol7TShooter">Dolphin Troubleshooter</a> <br />
and solve the problem.
EOF;
        exit;
    }
}

error_reporting(E_ALL & ~E_NOTICE);


/*------------------------------*/
/*----------Vars----------------*/

require_once('../inc/version.inc.php');

$aConf                = array();
$aConf['iVersion']    = $site['ver'];
$aConf['iPatch']      = $site['build'];
$aConf['dolFile']     = '../inc/header.inc.php';
$aConf['confDir']     = '../inc/';
$aConf['headerTempl'] = <<<EOS
<?php

\$site['url']               = "%site_url%";
\$admin_dir                 = "administration";
\$iAdminPage				= 0;
\$site['url_admin']         = "{\$site['url']}\$admin_dir/";

\$site['mediaImages']       = "{\$site['url']}media/images/";
\$site['gallery']           = "{\$site['url']}media/images/gallery/";
\$site['flags']             = "{\$site['url']}media/images/flags/";
\$site['banners']           = "{\$site['url']}media/images/banners/";
\$site['tmp']               = "{\$site['url']}tmp/";
\$site['plugins']           = "{\$site['url']}plugins/";
\$site['base']              = "{\$site['url']}templates/base/";

\$site['bugReportMail']     = "%bug_report_email%";
\$site['logError']          = true;
\$site['fullError']         = false;
\$site['emailError']        = true;

\$dir['root']               = "%dir_root%";
\$dir['inc']                = "{\$dir['root']}inc/";
\$dir['profileImage']       = "{\$dir['root']}media/images/profile/";

\$dir['mediaImages']        = "{\$dir['root']}media/images/";
\$dir['gallery']            = "{\$dir['root']}media/images/gallery/";
\$dir['flags']              = "{\$dir['root']}media/images/flags/";
\$dir['banners']            = "{\$dir['root']}media/images/banners/";
\$dir['tmp']                = "{\$dir['root']}tmp/";
\$dir['cache']              = "{\$dir['root']}cache/";
\$dir['plugins']            = "{\$dir['root']}plugins/";
\$dir['base']               = "{\$dir['root']}templates/base/";
\$dir['classes']            = "{\$dir['inc']}classes/";

\$PHPBIN                    = "%dir_php%";

\$db['host']                = '%db_host%';
\$db['sock']                = '%db_sock%';
\$db['port']                = '%db_port%';
\$db['user']                = '%db_user%';
\$db['passwd']              = '%db_password%';
\$db['db']                  = '%db_name%';
\$db['persistent']          = true;

define('BX_DOL_URL_ROOT', \$site['url']);
define('BX_DOL_URL_ADMIN', \$site['url_admin']);
define('BX_DOL_URL_PLUGINS', \$site['plugins']);
define('BX_DOL_URL_MODULES', \$site['url'] . 'modules/' );
define('BX_DOL_URL_CACHE_PUBLIC', \$site['url'] . 'cache_public/');

define('BX_DOL_LOG_ERROR', \$site['logError']);
define('BX_DOL_FULL_ERROR', \$site['fullError']);
define('BX_DOL_EMAIL_ERROR', \$site['emailError']);
define('BX_DOL_REPORT_EMAIL', \$site['bugReportMail']);

define('BX_DIRECTORY_PATH_INC', \$dir['inc']);
define('BX_DIRECTORY_PATH_ROOT', \$dir['root']);
define('BX_DIRECTORY_PATH_BASE', \$dir['base']);
define('BX_DIRECTORY_PATH_CACHE', \$dir['cache']);
define('BX_DIRECTORY_PATH_CLASSES', \$dir['classes']);
define('BX_DIRECTORY_PATH_PLUGINS', \$dir['plugins']);
define('BX_DIRECTORY_PATH_DBCACHE', \$dir['cache']);
define('BX_DIRECTORY_PATH_MODULES', \$dir['root'] . 'modules/' );
define('BX_DIRECTORY_PATH_CACHE_PUBLIC', \$dir['root'] . 'cache_public/' );

define('DATABASE_HOST', \$db['host']);
define('DATABASE_SOCK', \$db['sock']);
define('DATABASE_PORT', \$db['port']);
define('DATABASE_USER', \$db['user']);
define('DATABASE_PASS', \$db['passwd']);
define('DATABASE_NAME', \$db['db']);
define('DATABASE_PERSISTENT', \$db['persistent']);

define('BX_DOL_SPLASH_VIS_DISABLE', 'disable');
define('BX_DOL_SPLASH_VIS_INDEX', 'index');
define('BX_DOL_SPLASH_VIS_ALL', 'all');


define('CHECK_DOLPHIN_REQUIREMENTS', 1);
if (defined('CHECK_DOLPHIN_REQUIREMENTS')) {
    \$aErrors = array();
    \$aErrors[] = (ini_get('register_globals') == 0) ? '' : '<font color="red">register_globals is On (warning, you should have this param in the Off state, or your site will be unsafe)</font>';
    \$aErrors[] = (ini_get('safe_mode') == 0) ? '' : '<font color="red">safe_mode is On, disable it</font>';
    \$aErrors[] = (version_compare(PHP_VERSION, '5.4.0', '<')) ? '<font color="red">PHP version too old, please update to PHP 5.4.0 at least</font>' : '';
    \$aErrors[] = (!extension_loaded( 'mbstring')) ? '<font color="red">mbstring extension not installed. <b>Warning!</b> Dolphin cannot work without <b>mbstring</b> extension.</font>' : '';
    \$aErrors[] = (ini_get('short_open_tag') == 0 && version_compare(phpversion(), "5.4", "<") == 1) ? '<font color="red">short_open_tag is Off (must be On!)<b>Warning!</b> Dolphin cannot work without <b>short_open_tag</b>.</font>' : '';
    \$aErrors[] = (ini_get('allow_url_include') == 0) ? '' : '<font color="red">allow_url_include is On (warning, you should have this param in the Off state, or your site will be unsafe)</font>';

    \$aErrors = array_diff(\$aErrors, array('')); //delete empty
    if (count(\$aErrors)) {
        \$sErrors = implode(" <br /> ", \$aErrors);
        echo <<<EOF
{\$sErrors} <br />
Please go to the <br />
<a href="https://www.boonex.com/trac/dolphin/wiki/GenDol7TShooter">Dolphin Troubleshooter</a> <br />
and solve the problem.
EOF;
        exit;
    }
}


//check correct hostname
\$aUrl = parse_url( \$site['url'] );
\$iPortDefault = 'https' == \$aUrl['scheme'] ? '443' : '80';
if ( isset(\$_SERVER['HTTP_HOST']) and 0 != strcasecmp(\$_SERVER['HTTP_HOST'], \$aUrl['host']) and 0 != strcasecmp(\$_SERVER['HTTP_HOST'], \$aUrl['host'] . ':' . (!empty(\$aUrl['port']) ? \$aUrl['port'] : \$iPortDefault)) ) {
    \$sPort = empty(\$aUrl['port']) || 80 == \$aUrl['port'] || 443 == \$aUrl['port'] ? '' : ':' . \$aUrl['port'];
    header( "Location:{\$aUrl['scheme']}://{\$aUrl['host']}{\$sPort}{\$_SERVER['REQUEST_URI']}", true, 301 );
    exit;
}


// check if install folder exists
if ( !defined ('BX_SKIP_INSTALL_CHECK') && file_exists( \$dir['root'] . 'install' ) ) {
    \$ret = <<<EOJ
<!DOCTYPE html>
<html>
<head>
    <title>Dolphin Installed</title>
    <link href="{\$site['url']}install/general.css" rel="stylesheet" type="text/css" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body class="bx-def-font">
    <div class="adm-header">
        <div class="adm-header-content">
            <div class="adm-header-title bx-def-margin-sec-left">
                <div class="adm-header-logo"><img src="{\$site['url']}install/images/dolphin-white.svg" /></div>
                <div class="adm-header-text bx-def-font-h1">DOLPHIN.PRO</div>
                <div class="clear_both">&nbsp;</div>
            </div>
            <div class="clear_both">&nbsp;</div>
        </div>
    </div>
    <div id="bx-install-main" class="bx-def-border bx-def-round-corners bx-def-margin-top bx-def-margin-bottom">
        <div id="bx-install-content" class="bx-def-padding">
            <div class="bx-install-header-caption bx-def-font-h1 bx-def-margin-bottom">
                Well done, mate! Dolphin is now installed.
            </div>
            <div class="bx-install-header-text bx-def-font-large bx-def-font-grayed">
                Remove directory called <b>"install"</b> from your server and <a href="{\$site['url']}administration/modules.php">proceed to Admin Panel to install modules</a>.
            </div>
        </div>
    </div>
</body>
</html>
EOJ;
    echo \$ret;
    exit();
}

// set error reporting level
// only show errors, hide notices, deprecated and strict warnings
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);

// set default encoding for multibyte functions
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

require_once(BX_DIRECTORY_PATH_INC . "version.inc.php");
require_once(BX_DIRECTORY_PATH_ROOT . "flash/modules/global/inc/header.inc.php");
require_once(BX_DIRECTORY_PATH_ROOT . "flash/modules/global/inc/content.inc.php");
require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolService.php");
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolExceptionHandler.php');

set_exception_handler([new BxDolExceptionHandler(), 'handle']);

\$oZ = new BxDolAlerts('system', 'begin', 0);
\$oZ->alert();

EOS;

$aConf['periodicTempl'] = <<<EOS
MAILTO=%site_email%<br />
* * * * * cd %dir_root%periodic; %dir_php% -q cron.php<br />
EOS;

$confFirst              = array();
$confFirst['site_url']  = array(
    'name'    => "Site URL",
    'ex'      => "http://www.mydomain.com/path/",
    'desc'    => "Your site URL (slash at the end is required)",
    'def'     => "http://",
    'def_exp' => function () {
            $str = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
            return preg_replace("/install\/(index\.php$)/", "", $str);
    },
    'check'   => function ($arg0) { return strlen($arg0) >= 10 ? true : false; }
);
$confFirst['dir_root']  = array(
    'name'    => "Directory root",
    'ex'      => "/path/to/your/script/files/",
    'desc'    => "Path to the directory where your Dolphin files are located (slash at the end is required)",
    'def_exp' => function () {
            $str = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $_SERVER['PHP_SELF'];
            return preg_replace("/install\/(index\.php$)/", "", $str);
    },
    'check'   => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);
$confFirst['dir_php']   = array(
    'name'    => "Path to php binary",
    'ex'      => "/usr/local/bin/php",
    'desc'    => "Full path to your PHP interpreter",
    'def'     => "/usr/local/bin/php",
    'def_exp' => function () {
            if ( file_exists("/usr/local/bin/php") ) return "/usr/local/bin/php";
            if ( file_exists("/usr/bin/php") ) return "/usr/bin/php";
            $fp = popen ( "whereis php", "r");
            if ( $fp ) {
                $s = fgets($fp);
                $s = sscanf($s, "php: %s");
                if ( file_exists("$s[0]") ) return "$s[0]";
            }
            return '';
    },
    'check'   => function ($arg0) { return strlen($arg0) >= 7 ? true : false; }
);
$aDbConf                = array();
$aDbConf['sql_file']    = array(
    'name'    => "SQL file",
    'ex'      => "/home/dolphin/public_html/install/sql/vXX.sql",
    'desc'    => "SQL file location",
    'def'     => "./sql/vXX.sql",
    'def_exp' => function () {
            if ( !( $dir = opendir( "sql/" ) ) )
                return "";
            while (false !== ($file = readdir($dir))) {
                if ( substr($file,-3) != 'sql' ) continue;
                closedir( $dir );
                return "./sql/$file";
            }
            closedir( $dir );
            return "";
    },
    'check'   => function ($arg0) { return strlen($arg0) >= 4 ? true : false; }
);
$aDbConf['db_host']     = array(
    'name'  => "Database host name",
    'ex'    => "localhost",
    'desc'  => "MySQL database host name",
    'def'   => "localhost",
    'check' => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);
$aDbConf['db_port']     = array(
    'name'  => "Database host port number",
    'ex'    => "5506",
    'desc'  => "Leave blank for default value or specify MySQL database host port number",
    'def'   => "",
    'check' => ''
);
$aDbConf['db_sock']     = array(
    'name'  => "Database socket path",
    'ex'    => "/tmp/mysql50.sock",
    'desc'  => "Leave blank for default value or specify MySQL database socket path",
    'def'   => "",
    'check' => ''
);
$aDbConf['db_name']     = array(
    'name'  => "Database name",
    'ex'    => "user_dolphin",
    'desc'  => "MySQL database name",
    'check' => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);
$aDbConf['db_user']     = array(
    'name'  => "Database user",
    'ex'    => "YourName",
    'desc'  => "MySQL database user name with read/write access",
    'check' => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);
$aDbConf['db_password'] = array(
    'name'  => "Database password",
    'ex'    => "MySuperSecretWord",
    'desc'  => "MySQL database password",
    'check' => function ($arg0) { return strlen($arg0) >= 0 ? true : false; }
);

$aGeneral                     = array();
$aGeneral['site_title']       = array(
    'name'  => "Site Title",
    'ex'    => "The Best Community",
    'desc'  => "The name of your site",
    'check' => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);
$aGeneral['site_desc']        = array(
    'name'  => "Site Description",
    'ex'    => "The place to find new friends, communicate and have fun.",
    'desc'  => "Meta description of your site",
    'check' => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);
$aGeneral['site_email']       = array(
    'name'  => "Site e-mail",
    'ex'    => "admin@your.site",
    'desc'  => "Site e-mail",
    'check' => function ($arg0) { return strlen($arg0) > 0 AND strstr($arg0,"@") ? true : false; }
);
$aGeneral['notify_email']     = array(
    'name'  => "Notify e-mail",
    'ex'    => "no-reply@your.site",
    'desc'  => "Email to send site notifications from",
    'check' => function ($arg0) { return strlen($arg0) > 0 AND strstr($arg0,"@") ? true : false; }
);
$aGeneral['bug_report_email'] = array(
    'name'  => "Bug report email",
    'ex'    => "admin@your.site",
    'desc'  => "Email for receiving bug reports",
    'check' => function ($arg0) { return strlen($arg0) > 0 AND strstr($arg0,"@") ? true : false; }
);
$aGeneral['admin_username']   = array(
    'name'  => "Admin Username",
    'ex'    => "admin",
    'desc'  => "Username to login to the administration area of the site",
    'check' => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);
$aGeneral['admin_password']   = array(
    'name'  => "Admin Password",
    'ex'    => "MySuperSecretWord",
    'desc'  => "Secure admin password",
    'check' => function ($arg0) { return strlen($arg0) >= 1 ? true : false; }
);

$aNonDeletableModules = array(
    'boonex/shared_photo/',
);

$aTemporalityWritableFolders = array(
    'inc',
);

/*----------Vars----------------*/
/*------------------------------*/


$sAction = $_REQUEST['action'];
$sError  = '';

define('BX_SKIP_INSTALL_CHECK', true);
// --------------------------------------------
if ($sAction == 'step6' || $sAction == 'step7' || $sAction == 'compile_languages') {
    require_once('../inc/header.inc.php');
    require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
    require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
}
// --------------------------------------------
require_once('../inc/classes/BxDolIO.php');


$sInstallPageContent = InstallPageContent($sError);

mb_internal_encoding('UTF-8');

if ($sInstallPageContent) {
    echo PageHeader($sAction, $sError);
    echo $sInstallPageContent;
    echo PageFooter($sAction);
}

function InstallPageContent(&$sError)
{
    global $aConf, $confFirst, $aDbConf, $aGeneral;

    $sRet = '';

    switch ($_REQUEST['action']) {
        case 'compile_languages':
            performInstallLanguages();
            $sRet .= 'Default Dolphin language was recompiled';
            break;

        case 'step7':
            $sRet .= genMainDolphinPage();
            break;

        case 'step6':
            $sErrorMessage = checkPostInstallPermissions($sError);
            $sRet .= (strlen($sErrorMessage)) ? genPostInstallPermissionTable($sErrorMessage) : genMainDolphinPage();
            break;

        case 'step5':
            $sRet .= genPostInstallPermissionTable();
            break;

        case 'step4':
            $sErrorMessage = checkConfigArray($aGeneral, $sError);
            $sRet .= (strlen($sErrorMessage)) ? genSiteGeneralConfig($sErrorMessage) : genInstallationProcessPage();
            break;

        case 'step3':
            $sErrorMessage = checkConfigArray($aDbConf, $sError);
            $sErrorMessage .= CheckSQLParams();

            $sRet .= (strlen($sErrorMessage)) ? genDatabaseConfig($sErrorMessage) : genSiteGeneralConfig();
            break;

        case 'step2':
            $sErrorMessage = checkConfigArray($confFirst, $sError);
            $sRet .= (strlen($sErrorMessage)) ? genPathCheckingConfig($sErrorMessage) : genDatabaseConfig();
            break;

        case 'step1':
            $sErrorMessage = checkPreInstallPermission($sError);
            $sRet .= (strlen($sErrorMessage)) ? genPreInstallPermissionTable($sErrorMessage) : genPathCheckingConfig();
            break;

        case 'preInstall':
            $sRet .= genPreInstallPermissionTable();
            break;

        case 'empty':
            break;

        default:
            $sRet .= StartInstall();
            break;
    }

    return $sRet;
}

function performInstallLanguages()
{
    db_res("TRUNCATE TABLE `sys_localization_languages`");
    db_res("TRUNCATE TABLE `sys_localization_keys`");
    db_res("TRUNCATE TABLE `sys_localization_strings`");

    if (!($sLangsDir = opendir(BX_DIRECTORY_PATH_ROOT . 'install/langs/'))) {
        return;
    }
    while (false !== ($sFilename = readdir($sLangsDir))) {
        if (substr($sFilename, -3) == 'php') {
            unset($LANG);
            unset($LANG_INFO);
            require_once(BX_DIRECTORY_PATH_ROOT . 'install/langs/' . $sFilename);
            walkThroughLanguage($LANG, $LANG_INFO);
        }
    }
    closedir($sLangsDir);
    compileLanguage();
}

function walkThroughLanguage($aLanguage, $aLangInfo)
{
    $sLangName          = $aLangInfo['Name'];
    $sLangFlag          = $aLangInfo['Flag'];
    $sLangTitle         = $aLangInfo['Title'];
    $sLangDir           = isset($aLangInfo['Direction']) && $aLangInfo['Direction'] ? $aLangInfo['Direction'] : 'LTR';
    $sLangCountryCode   = isset($aLangInfo['LanguageCountry']) && $aLangInfo['LanguageCountry'] ? $aLangInfo['LanguageCountry'] : $aLangInfo['Name'] . '_' . strtoupper($aLangInfo['Flag']);
    $sInsertLanguageSQL = "INSERT INTO `sys_localization_languages` VALUES (NULL, '{$sLangName}', '{$sLangFlag}', '{$sLangTitle}', '{$sLangDir}', '{$sLangCountryCode}')";
    db_res($sInsertLanguageSQL);
    $iLangKey = db_last_id();

    foreach ($aLanguage as $sKey => $sValue) {
        $sDqKey   = str_replace("'", "''", $sKey);
        $sDqValue = str_replace("'", "''", $sValue);

        $iExistedKey = (int)db_value("SELECT `ID` FROM `sys_localization_keys` WHERE `Key`='{$sDqKey}'");
        if ($iExistedKey > 0) { // Key exists, no need insert key
        } else {
            $sInsertKeySQL = "INSERT INTO `sys_localization_keys` VALUES(NULL, 1, '{$sDqKey}')";
            db_res($sInsertKeySQL);
            $iExistedKey = db_last_id();
        }

        $sInsertValueSQL = "INSERT INTO `sys_localization_strings` VALUES({$iExistedKey}, {$iLangKey}, '{$sDqValue}');";
        db_res($sInsertValueSQL);
    }
}


function genInstallationProcessPage($sErrorMessage = '')
{
    global $aConf, $confFirst, $aDbConf, $aGeneral;

    $sAdminName     = $_REQUEST['admin_username'];
    $sAdminPassword = $_REQUEST['admin_password'];
    $resRunSQL      = RunSQL($sAdminName, $sAdminPassword);

    $sForm = '';

    if ('done' == $resRunSQL) {
        $sForm = '
            <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
                <input type="submit" value="Next" class="bx-btn bx-btn-primary" />
                <input type="hidden" name="action" value="step5" />
            </form>';
    } else {
        $sForm = $resRunSQL . '
            <form action="' . $_SERVER['PHP_SELF'] . '" method="post">
                <input type="submit" value="Back" class="bx-btn" />';
        foreach ($_POST as $sKey => $sValue) {
            if ($sKey != "action") {
                $sForm .= '<input type="hidden" name="' . $sKey . '" value="' . $sValue . '" />';
            }
        }
        $sForm .= '<input type="hidden" name="action" value="step2" />
            </form>';

        return $sForm;
    }

    foreach ($confFirst as $key => $val) {
        $aConf['headerTempl'] = str_replace("%$key%", $_POST[$key], $aConf['headerTempl']);
    }
    foreach ($aDbConf as $key => $val) {
        $aConf['headerTempl'] = str_replace("%$key%", $_POST[$key], $aConf['headerTempl']);
    }
    foreach ($aGeneral as $key => $val) {
        $aConf['headerTempl'] = str_replace("%$key%", $_POST[$key], $aConf['headerTempl']);
    }

    $aConf['periodicTempl'] = str_replace("%site_email%", $_POST['site_email'], $aConf['periodicTempl']);
    $aConf['periodicTempl'] = str_replace("%dir_root%", $_POST['dir_root'], $aConf['periodicTempl']);
    $aConf['periodicTempl'] = str_replace("%dir_php%", $_POST['dir_php'], $aConf['periodicTempl']);

    $sInnerCode .= "<div class=\"bx-install-debug bx-def-border bx-def-padding-sec\">{$aConf['periodicTempl']}</div>";


    $fp = fopen($aConf['dolFile'], 'w');
    if ($fp) {
        fputs($fp, $aConf['headerTempl']);
        fclose($fp);
        chmod($aConf['dolFile'], 0666);
    } else {
        $trans = get_html_translation_table(HTML_ENTITIES);
        $templ = strtr($aConf['headerTempl'], $trans);
        $text  = 'Warning!!! can not get write access to config file ' . $aConf['dolFile'] . '. Please save config file below manually:</font><br>';
        $sInnerCode .= '<div class="bx-def-margin-top">';
        $sInnerCode .= printInstallError($text);
        $sInnerCode .= '<textarea cols="20" rows="10" class="headerTextarea bx-def-font bx-def-round-corners-with-border">' . $aConf['headerTempl'] . '</textarea>';
        $sInnerCode .= '</div>';
    }

    return <<<EOF
<div class="bx-install-header-caption bx-def-font-h1 bx-def-margin-bottom">
    Cron Jobs
</div>

<div class="bx-install-header-text bx-def-font-large bx-def-font-grayed bx-def-margin-bottom">
    Setup Cron Jobs as specified below. Helpful info about Cron Jobs is <a href="https://www.boonex.com/trac/dolphin/wiki/DetailedInstall#InstallScript-Step5-CronJobs" target="_blank">available here</a>.
</div>

{$sInnerCode}


<div class="bx-install-buttons bx-def-margin-top">
    {$sForm}
</div>

EOF;

}

function isAdmin()
{
    return false;
}

// check of step 5
function checkPostInstallPermissions(&$sError)
{
    global $aTemporalityWritableFolders;

    $sFoldersErr = $sFilesErr = $sErrorMessage = '';

    require_once('../inc/classes/BxDolAdminTools.php');
    $oAdmTools = new BxDolAdminTools();
    $oBxDolIO  = new BxDolIO();

    $aInstallDirsMerged = array_merge($aTemporalityWritableFolders, $oAdmTools->aPostInstallPermDirs);
    foreach ($aInstallDirsMerged as $sFolder) {
        if ($oBxDolIO->isWritable($sFolder)) {
            $sFoldersErr .= '&nbsp;&nbsp;&nbsp;' . $sFolder . ';<br />';
        }
    }
    if (strlen($sFoldersErr)) {
        $sError = 'error';
        $sErrorMessage .= '<strong>The following directories have inappropriate permissions</strong>:<br />' . $sFoldersErr;
    }
    foreach ($oAdmTools->aPostInstallPermFiles as $sFile) {
        if ($oBxDolIO->isWritable($sFile)) {
            $sFilesErr .= '&nbsp;&nbsp;&nbsp;' . $sFile . ';<br /> ';
        }
    }
    if (strlen($sFilesErr)) {
        $sError = 'error';
        $sErrorMessage .= '<strong>The following files have inappropriate permissions</strong>:<br />' . $sFilesErr;
    }

    return $sErrorMessage;
}

// step 5
function genPostInstallPermissionTable($sErrorMessage = '')
{
    global $aTemporalityWritableFolders;

    $sCurPage     = $_SERVER['PHP_SELF'];
    $sPostFolders = $sPostFiles = '';

    $sErrors = printInstallError($sErrorMessage);

    require_once('../inc/classes/BxDolAdminTools.php');
    $oAdmTools = new BxDolAdminTools();
    $oBxDolIO  = new BxDolIO();

    $aInstallDirsMerged = array_merge($aTemporalityWritableFolders, $oAdmTools->aPostInstallPermDirs);
    $i                  = 0;
    foreach ($aInstallDirsMerged as $sFolder) {
        $sStyleAdd = (($i % 2) == 0) ? 'background-color:#ede9e9;' : 'background-color:#fff;';

        $sEachFolder = ($oBxDolIO->isWritable($sFolder))
            ? '<span class="unwritable">Writable</span>' : '<span class="writable">Non-writable</span>';

        $sPostFolders .= <<<EOF
<tr style="{$sStyleAdd}" class="cont">
    <td>{$sFolder}</td>
    <td class="span">
        {$sEachFolder}
    </td>
    <td class="span">
        <span class="desired">Non-writable</span>
    </td>
</tr>
EOF;
        $i++;
    }

    $i = 0;
    foreach ($oAdmTools->aPostInstallPermFiles as $sFile) {
        $str     = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['PHP_SELF'];
        $sFolder = preg_replace("/install\/(index\.php$)/", "", $str);

        if (file_exists($sFolder . $sFile)) {
            $sStyleAdd = (($i % 2) == 0) ? 'background-color:#ede9e9;' : 'background-color:#fff;';

            $sEachFile = ($oBxDolIO->isWritable($sFile))
                ? '<span class="unwritable">Writable</span>'
                : '<span class="writable">Non-writable</span>';

            $sPostFiles .= <<<EOF
<tr style="{$sStyleAdd}" class="cont">
    <td>{$sFile}</td>
    <td class="span">
        {$sEachFile}
    </td>
    <td class="span">
        <span class="desired">Non-writable</span>
    </td>
</tr>
EOF;
            $i++;
        }
    }

    return <<<EOF
<div class="bx-install-header-caption bx-def-font-h1 bx-def-margin-bottom">
    Permissions Reversal
</div>

<div class="bx-install-header-text bx-def-font-large bx-def-font-grayed bx-def-margin-bottom">
    Reverse permissions for the files indicated below to keep your site secure. Helpful info about permissions is <a href="https://www.boonex.com/trac/dolphin/wiki/DetailedInstall#InstallScript-Step1-Permissions" target="_blank">available here</a>.
</div>

{$sErrors}


<table cellpadding="0" cellspacing="1" width="100%" border="0" class="install_table">
    <tr class="head">
        <td>Directories</td>
        <td>Current Level</td>
        <td>Desired Level</td>
    </tr>
    {$sPostFolders}
    <tr class="head">
        <td>Files</td>
        <td>Current Level</td>
        <td>Desired Level</td>
    </tr>
    {$sPostFiles}
</table>


<form id="bx-install-form-postInstallPerm-check" action="{$sCurPage}" method="post">
    <input type="hidden" name="action" value="step5" />
</form>

<form id="bx-install-form-postInstallPerm-next" action="{$sCurPage}" method="post">
    <input type="hidden" name="action" value="step6" />
</form>

<form id="bx-install-form-postInstallPerm-skip" action="{$sCurPage}" method="post">
    <input type="hidden" name="action" value="step7" />
</form>

<div class="bx-install-buttons bx-def-margin-top">
    <button class="bx-btn" onclick="$('#bx-install-form-postInstallPerm-check').submit()">Reload</button>
    <button class="bx-btn" onclick="$('#bx-install-form-postInstallPerm-skip').submit()">Skip</button>
    <button class="bx-btn bx-btn-primary" onclick="$('#bx-install-form-postInstallPerm-next').submit()">Next</button>
</div>

EOF;
}

function genSiteGeneralConfig($sErrorMessage = '')
{
    global $aGeneral;

    $sCurPage       = $_SERVER['PHP_SELF'];
    $sSGParamsTable = createTable($aGeneral);

    $sErrors = '';
    if (strlen($sErrorMessage)) {
        $sErrors = printInstallError($sErrorMessage);
        unset($_POST['site_title']);
        unset($_POST['site_email']);
        unset($_POST['notify_email']);
        unset($_POST['bug_report_email']);
    }

    $sOldDataParams = '';
    foreach ($_POST as $postKey => $postValue) {
        $sOldDataParams .= ('action' == $postKey || isset($aGeneral[$postKey])) ? '' : '<input type="hidden" name="' . $postKey . '" value="' . $postValue . '" />';
    }

    return <<<EOF
<div class="bx-install-header-caption bx-def-font-h1 bx-def-margin-bottom">
    Site Configuration
</div>

{$sErrors}

<form action="{$sCurPage}" method="post">
    <table cellpadding="0" cellspacing="1" width="100%" border="0" class="install_table">
        {$sSGParamsTable}
    </table>

    <div class="bx-install-buttons bx-def-margin-top">
        <input id="button" type="submit" value="Next" class="bx-btn bx-btn-primary" />
    </div>

    <input type="hidden" name="action" value="step4" />

    {$sOldDataParams}

</form>
EOF;
}

// check of config pages steps
function checkConfigArray($aCheckedArray, &$sError)
{
    $sErrorMessage = '';

    foreach ($aCheckedArray as $sKey => $sValue) {
        if (!is_callable($sValue['check'])) {
            continue;
        }

        if (!$sValue['check']($_POST[$sKey])) {
            $sFieldErr = $sValue['name'];
            $sErrorMessage .= "Please, input valid data to <b>{$sFieldErr}</b> field<br />";
            $error_arr[$sKey] = 1;
            unset($_POST[$sKey]);
        } else {
            $error_arr[$sKey] = 0;
        }

        //$config_arr[$sKey]['def'] = $_POST[$sKey];
    }

    if (strlen($sErrorMessage)) {
        $sError = 'error';
    }

    return $sErrorMessage;
}

function genDatabaseConfig($sErrorMessage = '')
{
    global $aDbConf;

    $sCurPage       = $_SERVER['PHP_SELF'];
    $sDbParamsTable = createTable($aDbConf);

    $sErrors = '';
    if (!empty($sErrorMessage)) {
        $sErrors = printInstallError($sErrorMessage);
        unset($_POST['db_name']);
        unset($_POST['db_user']);
        unset($_POST['db_password']);
    }

    $sOldDataParams = '';
    foreach ($_POST as $postKey => $postValue) {
        $sOldDataParams .= ('action' == $postKey || isset($aDbConf[$postKey])) ? '' : '<input type="hidden" name="' . $postKey . '" value="' . $postValue . '" />';
    }

    return <<<EOF
<div class="bx-install-header-caption bx-def-font-h1 bx-def-margin-bottom">
    Database
</div>

<div class="bx-install-header-text bx-def-font-large bx-def-font-grayed bx-def-margin-bottom">
    Please <a target="_blank" href="https://www.boonex.com/trac/dolphin/wiki/DetailedInstall#Part2:CreateaDatabaseandaUser">create a database</a> and tell Dolphin about it.
</div>

{$sErrors}

<form action="{$sCurPage}" method="post">
    <table cellpadding="0" cellspacing="1" width="100%" border="0" class="install_table">
        {$sDbParamsTable}
    </table>


    <div class="bx-install-buttons bx-def-margin-top">
        <input id="button" type="submit" value="Next" class="bx-btn bx-btn-primary" />
    </div>

    <input type="hidden" name="action" value="step3" />

    {$sOldDataParams}

</form>
EOF;
}

function genPathCheckingConfig($sErrorMessage = '')
{
    global $aConf, $confFirst;

    $sCurPage = $_SERVER['PHP_SELF'];

    $sGDRes = (extension_loaded('gd')) ? '<span class="writable">Installed</span>'
        : '<span class="unwritable">NOT installed</span>';

    $sError      = printInstallError($sErrorMessage);
    $sPathsTable = createTable($confFirst);

    return <<<EOF
<div class="bx-install-header-caption bx-def-font-h1 bx-def-margin-bottom">
    Paths Check
</div>

<div class="bx-install-header-text bx-def-font-large bx-def-font-grayed bx-def-margin-bottom">
    Dolphin checks general script paths.
</div>

{$sError}

<form action="{$sCurPage}" method="post">

    <table cellpadding="0" cellspacing="1" width="100%" border="0" class="install_table">
        {$sPathsTable}
        <tr class="cont" style="background-color:#ede9e9;">
            <td>
                GD Library
            </td>
            <td>
                {$sGDRes}
            </td>
        </tr>
    </table>

    <div class="bx-install-buttons bx-def-margin-top">
        <input id="button" type="submit" value="Next" class="bx-btn bx-btn-primary" />
    </div>

    <input type="hidden" name="action" value="step2" />

</form>
EOF;
}

function checkPreInstallPermission(&$sError)
{
    global $aTemporalityWritableFolders;

    $sFoldersErr = $sFilesErr = $sErrorMessage = '';

    $oBxDolIO = new BxDolIO();

    require_once('../inc/classes/BxDolAdminTools.php');
    $oAdmTools = new BxDolAdminTools();

    $aInstallDirsMerged = array_merge($aTemporalityWritableFolders, $oAdmTools->aInstallDirs);
    foreach ($aInstallDirsMerged as $sFolder) {
        if (!$oBxDolIO->isWritable($sFolder)) {
            $sFoldersErr .= '&nbsp;&nbsp;&nbsp;' . $sFolder . ';<br />';
        }
    }

    foreach ($oAdmTools->aFlashDirs as $sFolder) {
        if (!$oBxDolIO->isWritable($sFolder)) {
            $sFoldersErr .= '&nbsp;&nbsp;&nbsp;' . $sFolder . ';<br />';
        }
    }

    if (strlen($sFoldersErr)) {
        $sError = 'error';
        $sErrorMessage .= '<strong>The following directories have inappropriate permissions</strong>:<br />' . $sFoldersErr;
    }

    foreach ($oAdmTools->aInstallFiles as $sFile) {
        if (!$oBxDolIO->isWritable($sFile)) {
            $sFilesErr .= '&nbsp;&nbsp;&nbsp;' . $sFile . ';<br /> ';
        }
    }

    foreach ($oAdmTools->aFlashFiles as $sFile) {
        if (strpos($sFile, 'ffmpeg') === false) {
            if (!$oBxDolIO->isWritable($sFile)) {
                $sFilesErr .= '&nbsp;&nbsp;&nbsp;' . $sFile . ';<br /> ';
            }
        } else {
            if (!$oBxDolIO->isExecutable($sFile)) {
                $sFilesErr .= '&nbsp;&nbsp;&nbsp;' . $sFile . ';<br /> ';
            }
        }
    }

    if (strlen($sFilesErr)) {
        $sError = 'error';
        $sErrorMessage .= '<strong>The following files have inappropriate permissions</strong>:<br />' . $sFilesErr;
    }

    return $sErrorMessage;
}

// pre install
function genPreInstallPermissionTable($sErrorMessage = '')
{
    global $aTemporalityWritableFolders;

    $sCurPage = $_SERVER['PHP_SELF'];
    $sErrorMessage .= (ini_get('safe_mode') == 1 || ini_get('safe_mode') == 'On') ? "Please turn off <b>safe_mode</b> in your php.ini file configuration" : '';
    $sError = printInstallError($sErrorMessage);

    require_once('../inc/classes/BxDolAdminTools.php');
    $oAdmTools               = new BxDolAdminTools();
    $oAdmTools->aInstallDirs = array_merge($aTemporalityWritableFolders, $oAdmTools->aInstallDirs);
    $sPermTable              = $oAdmTools->GenCommonCode();
    $sPermTable .= $oAdmTools->GenPermTable();

    return <<<EOF
<div class="bx-install-header-caption bx-def-font-h1 bx-def-margin-bottom">
    Permissions
</div>

<div class="bx-install-header-text bx-def-font-large bx-def-font-grayed bx-def-margin-bottom">
    Change permissions of files and directories as specified in the chart below. Helpful info about permissions is <a href="https://www.boonex.com/trac/dolphin/wiki/DetailedInstall#InstallScript-Step1-Permissions" target="_blank">available here</a>.
</div>

{$sError}

{$sPermTable}


<form id="bx-install-form-preInstall-check" action="{$sCurPage}" method="post">
    <input type="hidden" name="action" value="preInstall" />
</form>

<form id="bx-install-form-preInstall-next" action="{$sCurPage}" method="post">
    <input type="hidden" name="action" value="step1" />
</form>

<div class="bx-install-buttons bx-def-margin-top">
    <button class="bx-btn" onclick="$('#bx-install-form-preInstall-check').submit()">Reload</button>
    <button class="bx-btn bx-btn-primary" onclick="$('#bx-install-form-preInstall-next').submit()">Next</button>
</div>

EOF;
}

function StartInstall()
{
    global $aConf;

    return <<<EOF
<div class="bx-install-step-startInstall-dolphin-pic">
    <img src="../administration/templates/base/images/dolphin.svg" />
</div>

<div class="bx-install-buttons">
    <form action="{$_SERVER['PHP_SELF']}" method="post">
    <input id="button" type="submit" value="INSTALL" class="bx-btn bx-btn-primary" />
    <input type="hidden" name="action" value="preInstall" />
    </form>
</div>

<div class="bx-install-step-startInstall-text bx-def-font-large bx-def-margin-top">
    Dolphin {$aConf['iVersion']}.{$aConf['iPatch']} by <a href="https://www.boonex.com" target="_blank">BoonEx</a>
</div>

EOF;
}

function genMainDolphinPage()
{
    performInstallLanguages();

    $sExistedAdminPass = db_value("SELECT `Password` FROM `Profiles` WHERE `ID`='1'");

    $aUrl  = parse_url($GLOBALS['site']['url']);
    $sPath = isset($aUrl['path']) && !empty($aUrl['path']) ? $aUrl['path'] : '/';
    $sHost = '';

    $iCookieTime = 0;
    setcookie("memberID", 1, $iCookieTime, $sPath, $sHost);
    $_COOKIE['memberID'] = 1;
    setcookie("memberPassword", $sExistedAdminPass, $iCookieTime, $sPath, $sHost, false, true /* http only */);
    $_COOKIE['memberPassword'] = $sExistedAdminPass;

    return <<<EOF
<script type="text/javascript">
    window.location = "../index.php";
</script>
EOF;
}

function PageHeader($sAction = '', $sError = '')
{
    global $aConf;

    $aActions = array(
        "startInstall" => "Dolphin Installation",
        "preInstall"   => "Permissions",
        "step1"        => "Paths",
        "step2"        => "Database",
        "step3"        => "Config",
        "step4"        => "Cron Jobs",
        "step5"        => "Permissions Reversal",
        "step6"        => "Modules"
    );

    if (!strlen($sAction)) {
        $sAction = "startInstall";
    }

    $iCounterCurrent = 1;
    $iCounterActive  = 1;

    foreach ($aActions as $sActionKey => $sActionValue) {
        if ($sAction != $sActionKey) {
            $iCounterActive++;
        } else {
            break;
        }
    }

    if ($sError) {
        $iCounterActive--;
    }

    $sSubActions = '';
    foreach ($aActions as $sActionKey => $sActionValue) {
        if ($sActionKey == "startInstall" || $sActionKey == "step6") {
            $sSubActions .= '';
        } elseif ($iCounterActive == $iCounterCurrent) {
            $sSubActions .= '<div class="bx-install-top-menu-div">&#8250;</div><div class="bx-install-top-menu-active">' . $sActionValue . '</div>';
        } else {
            $sSubActions .= '<div class="bx-install-top-menu-div">&#8250;</div><div class="bx-install-top-menu-inactive">' . $sActionValue . '</div>';
        }
        $iCounterCurrent++;
    }

    return <<<EOF
<!DOCTYPE html>
<html>
<head>
    <title>Dolphin Smart Community Builder Installation Script</title>
    <link href="general.css" rel="stylesheet" type="text/css" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <script src="../plugins/jquery/jquery.js" type="text/javascript" language="javascript"></script>
    <script src="../inc/js/functions.js" type="text/javascript" language="javascript"></script>
</head>
<body class="bx-def-font">

    <div class="adm-header">
        <div class="adm-header-content">
            <div class="adm-header-title bx-def-margin-sec-left">
                <div class="adm-header-logo"><img src="images/dolphin-white.svg" /></div>
                <div class="adm-header-text bx-def-font-h1">DOLPHIN.PRO</div>
                <div class="clear_both">&nbsp;</div>
            </div>
            <div id="bx-install-top-menu" class="bx-def-font-large">
                {$sSubActions}
            </div>
            <div class="clear_both">&nbsp;</div>
        </div>
    </div>

    <div id="bx-install-main" class="bx-def-border bx-def-round-corners bx-def-margin-top bx-def-margin-bottom">
        <div id="bx-install-content">



EOF;
}

function PageFooter($sAction)
{
    return <<<EOF


        </div>
    </div>
</body>
</html>
EOF;
}

function printInstallError($sText)
{
    $sRet = (strlen($sText)) ? '<div class="bx-install-error bx-def-padding bx-def-margin-bottom bx-def-font-large">' . $sText . '</div>' : '';

    return $sRet;
}

function createTable($arr)
{
    $ret = '';
    $i = 0;
    foreach ($arr as $key => $value) {
        $sStyleAdd = (($i % 2) == 0) ? 'background-color:#ede9e9;' : 'background-color:#fff;';

        $def_exp_text = "";
        if (is_callable($value['def_exp'])) {
            $def_exp  = $value['def_exp']();
            if (strlen($def_exp)) {
                $def_exp_text = "&nbsp;<font color=green>found</font>";
                $value['def'] = $def_exp;
            } else {
                $def_exp_text = "&nbsp;<font color=red>not found</font>";
            }
        }

        $st_err = ($error_arr[$key] == 1) ? ' style="background-color:#FFDDDD;" ' : '';

        $ret .= <<<EOF
    <tr class="cont" style="{$sStyleAdd}">
        <td>
            <div><b>{$value['name']}</b></div>
            <div class="bx-def-font-grayed">Description:</div>
            <div class="bx-def-font-grayed">Example:</div>
        </td>
        <td>
            <div><input {$st_err} size="30" name="{$key}" value="{$value['def']}" class="bx-def-font bx-def-round-corners-with-border" /> {$def_exp_text}</div>
            <div class="bx-def-font-grayed">{$value['desc']}</div>
            <div class="bx-def-font-grayed" style="font-style:italic;">{$value['ex']}</div>
        </td>
    </tr>
EOF;
        $i++;
    }

    return $ret;
}

function rewriteFile($sCode, $sReplace, $sFile)
{
    $ret = '';
    $fs  = filesize($sFile);
    $fp  = fopen($sFile, 'r');
    if ($fp) {
        $fcontent = fread($fp, $fs);
        $fcontent = str_replace($sCode, $sReplace, $fcontent);
        fclose($fp);
        $fp = fopen($sFile, 'w');
        if ($fp) {
            if (fputs($fp, $fcontent)) {
                $ret .= true;
            } else {
                $ret .= false;
            }
            fclose($fp);
        } else {
            $ret .= false;
        }
    } else {
        $ret .= false;
    }

    return $ret;
}

function RunSQL($sAdminName, $sAdminPassword)
{
    $aDbConf['host']   = $_POST['db_host'];
    $aDbConf['sock']   = $_POST['db_sock'];
    $aDbConf['port']   = $_POST['db_port'];
    $aDbConf['user']   = $_POST['db_user'];
    $aDbConf['passwd'] = $_POST['db_password'];
    $aDbConf['db']     = $_POST['db_name'];

//    $aDbConf['host'] .= ($aDbConf['port'] ? ":{$aDbConf['port']}" : '') . ($aDbConf['sock'] ? ":{$aDbConf['sock']}" : '');
//
//    $pass     = true;
    $errorMes = '';
    $filename = $_POST['sql_file'];

//    $vLink = @mysql_connect($aDbConf['host'], $aDbConf['user'], $aDbConf['passwd']);

    try {
        $sSocketOrHost = ($aDbConf['sock']) ? "unix_socket={$aDbConf['sock']}" : "host={$aDbConf['host']};port={$aDbConf['port']}";
        $vLink = new PDO(
            "mysql:{$sSocketOrHost};dbname={$aDbConf['db']};charset=utf8",
            $aDbConf['user'],
            $aDbConf['passwd'],
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""',
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]
        );
    } catch (PDOException $e) {
        return printInstallError($e->getMessage());
    }

    if (!($f = fopen($filename, "r"))) {
        return printInstallError('Could not open file with sql instructions:' . $filename);
    }

    //Begin SQL script executing
    $s_sql = "";
    while ($s = fgets($f, 10240)) {
        $s = trim($s); //Utf with BOM only

        if (!strlen($s)) {
            continue;
        }
        if (mb_substr($s, 0, 1) == '#') {
            continue;
        } //pass comments
        if (mb_substr($s, 0, 2) == '--') {
            continue;
        }
        if (substr($s, 0, 5) == "\xEF\xBB\xBF\x2D\x2D") {
            continue;
        }

        $s_sql .= $s;

        if (mb_substr($s, -1) != ';') {
            continue;
        }

        try {
            $vLink->exec($s_sql);
        } catch (PDOException $e) {
            $errorMes .= 'Error while executing: ' . $s_sql . '<br />' . $e->getMessage() . '<hr />';
        }

        $s_sql = '';
    }

    $sAdminNameDB     = $sAdminName;
    $sSiteEmail       = $_POST['site_email'];
    $sSaltDB          = base64_encode(substr(md5(microtime()), 2, 6));
    $sAdminPasswordDB = sha1(md5($sAdminPassword) . $sSaltDB); // encryptUserPwd
    $sAdminQuery      = "
        INSERT INTO `Profiles`
            (`NickName`, `Email`, `Password`, `Salt`, `Status`, `Role`, `DateReg`)
        VALUES
            (?, ?, ?, ?, ?, ?, NOW())
    ";

    try {
        $stmt = $vLink->prepare($sAdminQuery);
        $stmt->execute([$sAdminNameDB, $sSiteEmail, $sAdminPasswordDB, $sSaltDB, 'Active', 3]);
    } catch (PDOException $e) {
        $errorMes .= 'Error while executing: ' . $s_sql . '<br />' . $e->getMessage() . '<hr />';
    }

    fclose($f);

    $enable_gd_value = extension_loaded('gd') ? 'on' : '';
    $ret = '';

    try {
        $stmt = $vLink->prepare("UPDATE `sys_options` SET `VALUE`= ? WHERE `Name`= ?");
        $stmt->execute([$enable_gd_value, 'enable_gd']);
    } catch (PDOException $e) {
        $ret .= "<font color=red><i><b>Error</b>:</i> " . $e->getMessage() . "</font><hr>";
    }

    $sSiteTitle       = $_POST['site_title'];
    $sSiteDesc        = $_POST['site_desc'];
    $sSiteEmailNotify = $_POST['notify_email'];
    if ($sSiteEmail != '' && $sSiteTitle != '' && $sSiteEmailNotify != '') {
        $stmt = $vLink->prepare("UPDATE `sys_options` SET `VALUE`= ? WHERE `Name`= ?");

        try {
            $stmt->execute([$sSiteEmail, 'site_email']);
        } catch (PDOException $e) {
            $ret .= "<font color=red><i><b>Error</b>:</i> " . $e->getMessage() . "</font><hr>";
        }

        try {
            $stmt->execute([$sSiteTitle, 'site_title']);
        } catch (PDOException $e) {
            $ret .= "<font color=red><i><b>Error</b>:</i> " . $e->getMessage() . "</font><hr>";
        }

        try {
            $stmt->execute([$sSiteEmailNotify, 'site_email_notify']);
        } catch (PDOException $e) {
            $ret .= "<font color=red><i><b>Error</b>:</i> " . $e->getMessage() . "</font><hr>";
        }

        try {
            $stmt->execute([$sSiteDesc, 'MetaDescription']);
        } catch (PDOException $e) {
            $ret .= "<font color=red><i><b>Error</b>:</i> " . $e->getMessage() . "</font><hr>";
        }
    } else {
        $ret .= "<font color=red><i><b>Error</b>:</i> Didn't received POSTed site_email or site_title or site_email_notify</font><hr>";
    }

    $vLink = null;

    $errorMes .= $ret;

    if (strlen($errorMes)) {
        return printInstallError($errorMes);
    } else {
        return 'done';
    }
}

function CheckSQLParams()
{
    $aDbConf['host']   = $_POST['db_host'];
    $aDbConf['sock']   = $_POST['db_sock'];
    $aDbConf['port']   = $_POST['db_port'];
    $aDbConf['user']   = $_POST['db_user'];
    $aDbConf['passwd'] = $_POST['db_password'];
    $aDbConf['db']     = $_POST['db_name'];

    try {
        $sSocketOrHost = ($aDbConf['sock']) ? "unix_socket={$aDbConf['sock']}" : "host={$aDbConf['host']};port={$aDbConf['port']}";
        $vLink = new PDO(
            "mysql:{$sSocketOrHost};dbname={$aDbConf['db']};charset=utf8",
            $aDbConf['user'],
            $aDbConf['passwd'],
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""',
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ]
        );
    } catch (PDOException $e) {
        return printInstallError('MySQL error: ' . $e->getMessage());
    }

    $vLink = null;
}

// set error reporting level
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

