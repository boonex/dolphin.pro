<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('BxDolIO.php' );

class BxDolAdminTools extends BxDolIO
{
    var $sTroubledElements;

    var $aInstallDirs;
    var $aInstallFiles;
    var $aFlashDirs;
    var $aFlashFiles;
    var $aPostInstallPermDirs;
    var $aPostInstallPermFiles;

    //constructor
    function __construct()
    {
        parent::__construct();

        $this->sTroubledElements = '';

        $this->aInstallDirs = array(
            'backup',
            'cache',
            'cache_public',
            'langs',
            'media/images',
            'media/images/banners',
            'media/images/blog',
            'media/images/classifieds',
            'media/images/membership',
            'media/images/profile',
            'media/moxie/files',
            'media/moxie/storage',
            'tmp',
        );

        $this->aInstallFiles = array(
            'sitemap.xml',
        );

        $this->aFlashDirs = array(
            'flash/modules/board/files',
            'flash/modules/chat/files',
            'flash/modules/photo/files',
            'flash/modules/im/files',
            'flash/modules/mp3/files',
            'flash/modules/video/files',
            'flash/modules/video_comments/files'
        );

        $this->aFlashFiles = array(
            'flash/modules/global/data/integration.dat',
            'flash/modules/board/xml/config.xml',
            'flash/modules/board/xml/langs.xml',
            'flash/modules/board/xml/main.xml',
            'flash/modules/board/xml/skins.xml',
            'flash/modules/chat/xml/config.xml',
            'flash/modules/chat/xml/langs.xml',
            'flash/modules/chat/xml/main.xml',
            'flash/modules/chat/xml/skins.xml',
            'flash/modules/desktop/xml/config.xml',
            'flash/modules/desktop/xml/langs.xml',
            'flash/modules/desktop/xml/main.xml',
            'flash/modules/desktop/xml/skins.xml',
            'flash/modules/global/app/ffmpeg.exe',
            'flash/modules/global/xml/config.xml',
            'flash/modules/global/xml/main.xml',
            'flash/modules/im/xml/config.xml',
            'flash/modules/im/xml/langs.xml',
            'flash/modules/im/xml/main.xml',
            'flash/modules/im/xml/skins.xml',
            'flash/modules/mp3/xml/config.xml',
            'flash/modules/mp3/xml/langs.xml',
            'flash/modules/mp3/xml/main.xml',
            'flash/modules/mp3/xml/skins.xml',
            'flash/modules/photo/xml/config.xml',
            'flash/modules/photo/xml/langs.xml',
            'flash/modules/photo/xml/main.xml',
            'flash/modules/photo/xml/skins.xml',
            'flash/modules/video/xml/config.xml',
            'flash/modules/video/xml/langs.xml',
            'flash/modules/video/xml/main.xml',
            'flash/modules/video/xml/skins.xml',
            'flash/modules/video_comments/xml/config.xml',
            'flash/modules/video_comments/xml/langs.xml',
            'flash/modules/video_comments/xml/main.xml',
            'flash/modules/video_comments/xml/skins.xml'
        );

        $this->aPostInstallPermDirs = array(
        );

        $this->aPostInstallPermFiles = array(
        );
    }

    function GenCommonCode()
    {
        $sAdditionDir = (isAdmin()==true) ? BX_DOL_URL_ROOT : '../';

        $sRet = <<<EOF
<style type="text/css">

    div.hidden {
        display:none;
    }

    .left_side_sw_caption {
        float:left;
        text-align:justify;
        width:515px;
    }

    .right_side_sw_caption {
        float:right;
        font-weight:normal;
    }

    tr.head td,
    tr.cont td {
    	background-color: rgba(255, 255, 255, 0.2);
    }

    tr.head td {        
        height: 17px;
        padding: 5px;

        border-color: silver;

        text-align: center;
        font-size: 13px;
        font-weight: bold;
    }
    tr.cont td {
        height:15px;
        padding:2px 5px;
        font-size:13px;
        border-color:silver;
    }

    .install_table {
        border-width:0px;
    }

    span.unwritable {
        color:#d00;
        font-weight:bold;
        margin-right:5px;
    }

    span.writable {
        color:#0b0;
        font-weight:bold;
        margin-right:5px;
    }

    span.desired {
        font-weight:bold;
        margin-right:5px;
    }

    tr.head td.left_aligned {
        text-align:left;
        font-weight:bold;
    }
</style>
EOF;
        return $sRet;
    }

    /**
     * Generate permissions table for modules
     * @param $iType - 1: folder, 2: file
     * @return HTML 
     */ 
    function GenPermTableForModules($iType) 
    {
        $aList = array ();
        bx_import('BxDolModuleDb');
        $oDbModules = new BxDolModuleDb();
        $aModules = $oDbModules->getModules();
        foreach ($aModules as $a) {
            if (empty($a['path']) || !include(BX_DIRECTORY_PATH_MODULES . $a['path'] . 'install/config.php'))
                continue;
            if (empty($aConfig['install_permissions']) || !is_array($aConfig['install_permissions']['writable']))
                continue;
            foreach ($aConfig['install_permissions']['writable'] as $sPath) {
                if (1 == $iType && is_dir(BX_DIRECTORY_PATH_MODULES . $a['path'] . $sPath))
                    $aList[] = basename(BX_DIRECTORY_PATH_MODULES) . '/' . $a['path'] . $sPath;
                elseif (2 == $iType && is_file(BX_DIRECTORY_PATH_MODULES . $a['path'] . $sPath))
                    $aList[] = basename(BX_DIRECTORY_PATH_MODULES) . '/' . $a['path'] . $sPath;
            }
        }
        return $this->GenArrElemPerm($aList, $iType);
    }

    function GenPermTable($isShowModules = false)
    {
        $sModulesDirsC = function_exists('_t') ? _t('_adm_admtools_modules_dirs') : 'Modules Directories';
        $sModulesFilesC = function_exists('_t') ? _t('_adm_admtools_modules_files') : 'Modules Files';
        $sDirsC = function_exists('_t') ? _t('_adm_admtools_Directories') : 'Directories';
        $sFilesC = function_exists('_t') ? _t('_adm_admtools_Files') : 'Files';
        $sElementsC = function_exists('_t') ? _t('_adm_admtools_Elements') : 'Elements';
        $sFlashC = function_exists('_t') ? _t('_adm_admtools_Flash') : 'Flash';
        $sCurrentLevelC = function_exists('_t') ? _t('_adm_admtools_Current_level') : 'Current level';
        $sDesiredLevelC = function_exists('_t') ? _t('_adm_admtools_Desired_level') : 'Desired level';
        $sBadFilesC = function_exists('_t') ? _t('_adm_admtools_Bad_files') : 'The following files and directories have inappropriate permissions';
        $sShowOnlyBadC = function_exists('_t') ? _t('_adm_admtools_Only_bad_files') : 'Show only files and directories with inappropriate permissions';
        $sDescriptionC = function_exists('_t') ? _t('_adm_admtools_Perm_description') : 'Dolphin needs special access for certain files and directories. Please, change permissions as specified in the chart below. Helpful info about permissions is <a href="https://www.boonex.com/trac/dolphin/wiki/DetailedInstall#InstallScript-Step1-Permissions" target="_blank">available here</a>.';

        $this->sTroubledElements = '';

        $sInstallDirs = $this->GenArrElemPerm($this->aInstallDirs, 1);
        $sFlashDirs = $this->GenArrElemPerm($this->aFlashDirs, 1);
        $sInstallFiles = $this->GenArrElemPerm($this->aInstallFiles, 2);
        $sFlashFiles = $this->GenArrElemPerm($this->aFlashFiles, 2);
        if ($isShowModules) {
            $sModulesDirs = $this->GenPermTableForModules(1);
            $sModulesFiles = $this->GenPermTableForModules(2);
            if ($sModulesDirs)
                $sModulesDirs = "
                    <tr class='head'>
                        <td>{$sModulesDirsC}</td>
                        <td>{$sCurrentLevelC}</td>
                        <td>{$sDesiredLevelC}</td>
                    </tr>" . $sModulesDirs;
            if ($sModulesFiles)
                $sModulesFiles = "
                    <tr class='head'>
                        <td>{$sModulesFilesC}</td>
                        <td>{$sCurrentLevelC}</td>
                        <td>{$sDesiredLevelC}</td>
                    </tr>" . $sModulesFiles;
        }
        $sAdditionDir = (isAdmin()==true) ? BX_DOL_URL_ROOT : '../';
        $sLeftAddEl = (isAdmin()==true) ? '<div class="left_side_sw_caption">'.$sDescriptionC.'</div>' : '';

        $sRet = <<<EOF
<script type="text/javascript">
    <!--
    function callSwitcher()
    {
        $('table.install_table tr:not(.troubled)').toggle();
    }

    function switchToTroubled(e)
    {
        if (!e.checked) {
            $('table.install_table tr:not(.troubled)').show();
        } else  {
            $('table.install_table tr:not(.troubled)').hide();
        }
        return false;
    }
    -->
</script>

<table width="100%" cellspacing="1" cellpadding="0" class="install_table">
    <tr class="head troubled">
        <td colspan="3" style="text-align:center;">
        {$sLeftAddEl}
        <div class="right_side_sw_caption">
            <input type="checkbox" id="bx-install-permissions-show-erros-only" onclick="switchToTroubled(this)" /> <label for="bx-install-permissions-show-erros-only">$sShowOnlyBadC</label>
        </div>
        <div class="clear_both"></div>
        </td>
    </tr>
    <tr class="head">
        <td colspan="3" style="text-align:center;" class="normal_td">{$sDirsC}</td>
    </tr>
    <tr class="head">
        <td>{$sDirsC}</td>
        <td>{$sCurrentLevelC}</td>
        <td>{$sDesiredLevelC}</td>
    </tr>
    {$sInstallDirs}
    <tr class="head">
        <td>{$sFlashC} {$sDirsC}</td>
        <td>{$sCurrentLevelC}</td>
        <td>{$sDesiredLevelC}</td>
    </tr>
    {$sFlashDirs}
    {$sModulesDirs}
    <tr class="head">
        <td colspan="3" style="text-align:center;">{$sFilesC}</td>
    </tr>
    <tr class="head">
        <td>{$sFilesC}</td>
        <td>{$sCurrentLevelC}</td>
        <td>{$sDesiredLevelC}</td>
    </tr>
    {$sInstallFiles}
    <tr class="head">
        <td>{$sFlashC} {$sFilesC}</td>
        <td>{$sCurrentLevelC}</td>
        <td>{$sDesiredLevelC}</td>
    </tr>
    {$sFlashFiles}
    {$sModulesFiles}
    <tr class="head troubled">
        <td colspan="3" style="text-align:center;">{$sBadFilesC}</td>
    </tr>
    <tr class="head troubled">
        <td>{$sElementsC}</td>
        <td>{$sCurrentLevelC}</td>
        <td>{$sDesiredLevelC}</td>
    </tr>
    {$this->sTroubledElements}
</table>
EOF;
        return $sRet;
    }

    function GenArrElemPerm($aElements, $iType) { //$iType: 1 - folder, 2 - file
        if (!is_array($aElements) || empty($aElements))
            return '';
        $sWritableC = function_exists('_t') ? _t('_adm_admtools_Writable') : 'Writable';
        $sNonWritableC = function_exists('_t') ? _t('_adm_admtools_Non_Writable') : 'Non-Writable';
        $sNotExistsC = function_exists('_t') ? _t('_adm_admtools_Not_Exists') : 'Not Exists';
        $sExecutableC = function_exists('_t') ? _t('_adm_admtools_Executable') : 'Executable';
        $sNonExecutableC = function_exists('_t') ? _t('_adm_admtools_Non_Executable') : 'Non-Executable';

        $iType = ($iType==1) ? 1 : 2;

        $sElements = '';
        $i = 0;
        foreach ($aElements as $sCurElement) {
            $iCurType = $iType;

            $sAwaitedPerm = ($iCurType==1) ? $sWritableC : $sWritableC;

            $sElemCntStyle = ($i%2==0) ? 'even' : 'odd' ;
            $bAccessible = ($iCurType==1) ? $this->isWritable($sCurElement) : $this->isWritable($sCurElement);

            if ($sCurElement == 'flash/modules/global/app/ffmpeg.exe') {
                $sAwaitedPerm = $sExecutableC;
                $bAccessible = $this->isExecutable($sCurElement);
            }

            if ($bAccessible) {
                $sResultPerm = ($iCurType==1) ? $sWritableC : $sWritableC;

                if ($sCurElement == 'flash/modules/global/app/ffmpeg.exe') {
                    $sResultPerm = $sExecutableC;
                }

                $sElements .= <<<EOF
<tr class="cont {$sElemCntStyle}">
    <td>{$sCurElement}</td>
    <td class="span">
        <span class="writable">{$sResultPerm}</span>
    </td>
    <td class="span">
        <span class="desired">{$sAwaitedPerm}</span>
    </td>
</tr>
EOF;
            } else {
                $sPerm = $this->getPermissions($sCurElement);
                $sResultPerm = '';
                if ($sPerm==false) {
                    $sResultPerm = $sNotExistsC;
                } else {
                    $sResultPerm = ($iCurType==1) ? $sNonWritableC : $sNonWritableC;
                }

                if ($sCurElement == 'flash/modules/global/app/ffmpeg.exe') {
                    $sResultPerm = $sNonExecutableC;
                }

                $sPerm = '';

                $sElements .= <<<EOF
<tr class="cont {$sElemCntStyle}">
    <td>{$sCurElement}</td>
    <td class="span">
        <span class="unwritable">{$sPerm} {$sResultPerm}</span>
    </td>
    <td class="span">
        <span class="desired">{$sAwaitedPerm}</span>
    </td>
</tr>
EOF;

                $this->sTroubledElements .= <<<EOF
<tr class="cont {$sElemCntStyle} troubled">
    <td>{$sCurElement}</td>
    <td class="span">
        <span class="unwritable">{$sPerm} {$sResultPerm}</span>
    </td>
    <td class="span">
        <span class="desired">{$sAwaitedPerm}</span>
    </td>
</tr>
EOF;

            }
            $i++;
        }
        return $sElements;
    }

    function performInstalCheck() { //check requirements
        $aErrors = array();

        $aErrors[] = (ini_get('register_globals') == 0) ? '' : '<font color="red">register_globals is On (warning, you should have this param in the Off state, or your site will be unsafe)</font>';
        $aErrors[] = (ini_get('safe_mode') == 0) ? '' : '<font color="red">safe_mode is On, disable it</font>';
        $aErrors[] = (((int)phpversion()) < 4) ? '<font color="red">PHP version too old, update server please</font>' : '';
        $aErrors[] = (!extension_loaded( 'mbstring')) ? '<font color="red">mbstring extension not installed. <b>Warning!</b> Dolphin cannot work without <b>mbstring</b> extension.</font>' : '';
        $aErrors[] = (ini_get('short_open_tag') == 0 && version_compare(phpversion(), "5.4", "<") == 1) ? '<font color="red">short_open_tag is Off (must be On!)<b>Warning!</b> Dolphin cannot work without <b>short_open_tag</b>.</font>' : '';
        $aErrors[] = (ini_get('allow_url_include') == 0) ? '' : '<font color="red">allow_url_include is On (warning, you should have this param in the Off state, or your site will be unsafe)</font>';

        $aErrors = array_diff($aErrors, array('')); //delete empty
        if (count($aErrors)) {
            $sErrors = implode(" <br /> ", $aErrors);
            echo <<<EOF
{$sErrors} <br />
Please go to the <br />
<a href="https://www.boonex.com/trac/dolphin/wiki/GenDol7TShooter">Dolphin Troubleshooter</a> <br />
and solve the problem.
EOF;
            exit;
        }
    }

    function GenCacheEnginesTable()
    {
        $sRet = '<table width="100%" cellspacing="1" cellpadding="0" class="install_table">';
        $sRet .= '
<tr class="head troubled">
    <td></td>
    <td class="center_aligned">' . _t('_sys_adm_installed') . '</td>
    <td class="center_aligned">' . _t('_sys_adm_cache_support') . '</td>
</tr>';

        $aEngines = array ('File', 'Memcache', 'APC', 'XCache');
        foreach ($aEngines as $sEngine) {
            $oCacheObject = @bx_instance ('BxDolCache' . $sEngine);
            $sRet .= '
<tr class="head troubled">
    <td class="left_aligned">' . $sEngine . '</td>
    <td class="center_aligned">' . (@$oCacheObject->isInstalled() ? '<font color="#0b0">' . _t('_Yes') . '</font>' : '<font color="#d00">' . _t('_No') . '</font>') . '</td>
    <td class="center_aligned">' . (@$oCacheObject->isAvailable() ? '<font color="#0b0">' . _t('_Yes') . '</font>' : '<font color="#d00">' . _t('_No') . '</font>') . '</td>
</tr>';
        }

        $sRet .= '</table>';
        return $sRet;
    }

    function GenTabbedPage($isShowModules = false)
    {
        $sTitleC = _t('_adm_admtools_title');
        $sAuditC = _t('');
        $sPermissionsC = _t('');
        $sCacheEnginesC = _t('');

        $sAuditTab = $this->GenAuditPage();
        $sPermissionsTab = $this->GenPermTable($isShowModules);
        $sCacheEnginesTab = $this->GenCacheEnginesTable();

        $sBoxContent = <<<EOF
<script type="text/javascript">
    <!--
    function switchAdmPage(oLink)
    {
        var sType = $(oLink).attr('id').replace('main_menu', '');
        var sName = '#page' + sType;

        $(oLink).parent('.notActive').hide().siblings('.notActive:hidden').show().siblings('.active').hide().siblings('#' + $(oLink).attr('id') + '-act').show();
        $(sName).siblings('div:visible').bx_anim('hide', 'fade', 'slow', function(){
            $(sName).bx_anim('show', 'fade', 'slow');
        });

        return false;
    }
    -->
</script>

<div class="boxContent" id="adm_pages">
    <div id="page0" class="visible">{$sAuditTab}</div>
    <div id="page1" class="hidden">{$sPermissionsTab}</div>
    <div id="page2" class="hidden">
        <iframe frameborder="0" width="100%" height="800" scrolling="auto" src="host_tools.php?get_phpinfo=true"></iframe>
    </div>
    <div id="page3" class="hidden">{$sCacheEnginesTab}</div>
</div>
EOF;

        $aTopItems = array(
            'main_menu0' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:switchAdmPage(this)', 'title' => _t('_adm_admtools_Audit'), 'active' => 1),
            'main_menu1' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:switchAdmPage(this)', 'title' => _t('_adm_admtools_Permissions'), 'active' => 0),
            'main_menu2' => array('href' => 'javascript:void(0)', 'onclick' => 'javascript:switchAdmPage(this)', 'title' => _t('_adm_admtools_phpinfo'), 'active' => 0),
        );

        return DesignBoxAdmin($sTitleC, $sBoxContent, $aTopItems, '', 11);
    }

    //************
    function isFolderReadWrite($filename)
    {
        clearstatcache();

        $aPathInfo = pathinfo(__FILE__);
        $filename = $aPathInfo['dirname'] . '/../../' . $filename;

        return (@file_exists($filename . '/.') && is_readable( $filename ) && is_writable( $filename ) ) ? true : false;
    }

    function isFileReadWrite($filename)
    {
        clearstatcache();

        $aPathInfo = pathinfo(__FILE__);
        $filename = $aPathInfo['dirname'] . '/../../' . $filename;

        return (is_file($filename) && is_readable( $filename ) && is_writable( $filename ) ) ? true : false;
    }

    function isFileExecutable($filename)
    {
        clearstatcache();

        $aPathInfo = pathinfo(__FILE__);
        $filename = $aPathInfo['dirname'] . '/../../' . $filename;

        return (is_file($filename) && is_executable( $filename ) ) ? true : false;
    }

    //************

    function isAllowUrlInclude()
    {
        $sAllowUrlInclude = ini_get('allow_url_include');
        return !($sAllowUrlInclude == 0);
    }

    function GenAuditPage()
    {
        $sDolphinPath = BX_DIRECTORY_PATH_ROOT;

        $sEmailToCkeckMailSending = getParam('site_email');

        $sLatestDolphinVer = file_get_contents("http://rss.boonex.com/");
        if (preg_match ('#<dolphin>([\.0-9]+)</dolphin>#', $sLatestDolphinVer, $m))
            $sLatestDolphinVer = $m[1];
        else
            $sLatestDolphinVer = 'undefined';

        $sMinPhpVer = '5.3.0';
        $sMinMysqlVer = '4.1.2';

        $a = unserialize(file_get_contents("http://www.php.net/releases/index.php?serialize=1"));
        $sLatestPhpVersion = $a[5]['version'];

        $aPhpSettings = array (
            'allow_url_fopen' => array('op' => '=', 'val' => true, 'type' => 'bool'),
            'allow_url_include' => array('op' => '=', 'val' => false, 'type' => 'bool'),
            'magic_quotes_gpc' => array('op' => '=', 'val' => false, 'type' => 'bool', 'warn' => 1),
            'memory_limit' => array('op' => '>=', 'val' => 128*1024*1024, 'type' => 'bytes', 'unlimited' => -1),
            'post_max_size' => array('op' => '>=', 'val' => 50*1024*1024, 'type' => 'bytes', 'warn' => 1),
            'upload_max_filesize' => array('op' => '>=', 'val' => 50*1024*1024, 'type' => 'bytes', 'warn' => 1),
            'register_globals' => array('op' => '=', 'val' => false, 'type' => 'bool'),
            'safe_mode' => array('op' => '=', 'val' => false, 'type' => 'bool'),
            'short_open_tag' => array('op' => '=', 'val' => true, 'type' => 'bool'),
            'disable_functions' => array('op' => 'without', 'val' => 'exec,shell_exec,popen,eval,assert,create_function,phpinfo,getenv,ini_set,mail,fsockopen,chmod,parse_ini_file'),
            'php module: curl' => array('op' => 'module', 'val' => 'curl'),
            'php module: gd' => array('op' => 'module', 'val' => 'gd'),
            'php module: mbstring' => array('op' => 'module', 'val' => 'mbstring'),
            'php module: xsl' => array('op' => 'module', 'val' => 'xsl', 'warn' => 1),
            'php module: json' => array('op' => 'module', 'val' => 'json'),
            'php module: fileinfo' => array('op' => 'module', 'val' => 'fileinfo'),
            'php module: openssl' => array('op' => 'module', 'val' => 'openssl', 'warn' => 1),
            'php module: zip' => array('op' => 'module', 'val' => 'zip', 'warn' => 1),
            'php module: ftp' => array('op' => 'module', 'val' => 'ftp', 'warn' => 1),
            'php module: calendar' => array('op' => 'module', 'val' => 'calendar', 'warn' => 1),
            'php module: exif' => array('op' => 'module', 'val' => 'exif'),
        );
        if (version_compare(phpversion(), "5.4", ">=") == 1)
            unset($aPhpSettings['short_open_tag']);

        $aMysqlSettings = array (
            'key_buffer_size' => array('op' => '>=', 'val' => 128*1024, 'type' => 'bytes'),
            'query_cache_limit' => array('op' => '>=', 'val' => 1000000, 'type' => 'bytes'),
            'query_cache_size' => array('op' => '>=', 'val' => 16*1024*1024, 'type' => 'bytes'),
            'max_heap_table_size' => array('op' => '>=', 'val' => 16*1024*1024, 'type' => 'bytes'),
            'tmp_table_size' => array('op' => '>=', 'val' => 16*1024*1024, 'type' => 'bytes'),
            'thread_cache_size ' => array('op' => '>', 'val' => 0),
        );

        $aRequiredApacheModules = array (
            'rewrite_module' => 'mod_rewrite',
        );

        $aDolphinOptimizationSettings = array (

            'DB cache' => array('enabled' => 'sys_db_cache_enable', 'cache_engine' => 'sys_db_cache_engine', 'check_accel' => true),

            'Page blocks cache' => array('enabled' => 'sys_pb_cache_enable', 'cache_engine' => 'sys_pb_cache_engine', 'check_accel' => true),

            'Member menu cache' => array('enabled' => 'always_on', 'cache_engine' => 'sys_mm_cache_engine', 'check_accel' => true),

            'Templates Cache' => array('enabled' => 'sys_template_cache_enable', 'cache_engine' => 'sys_template_cache_engine', 'check_accel' => true),

            'CSS files cache' => array('enabled' => 'sys_template_cache_css_enable', 'cache_engine' => '', 'check_accel' => false),

            'JS files cache' => array('enabled' => 'sys_template_cache_js_enable', 'cache_engine' => '', 'check_accel' => false),

            'Compression for CSS/JS cache' => array('enabled' => 'sys_template_cache_compress_enable', 'cache_engine' => '', 'check_accel' => false),
        );

        ob_start();
?>
<style>
.ok {
    color: #0b0;
}
.fail {
    color: #d00;
}
.warn {
    color: #000000;
}
.undef {
    color: #cccccc;
}
</style>
<h2>Software requirements</h2>
<ul>
    <li><b>PHP</b>:
        <?php
        $sPhpVer = PHP_VERSION;
        echo $sPhpVer . ' - ';
        if (version_compare($sPhpVer, $sMinPhpVer, '<'))
            echo '<b class="fail">FAIL</b> (your version is incompatible with Dolphin, must be at least ' . $sMinPhpVer . ')';
        elseif (version_compare($sPhpVer, '5.3.0', '>=') && version_compare($sPhpVer, '5.4.0', '<'))
            echo '<b class="warn">WARNING</b> (your PHP version is outdated, upgrade to the latest ' . $sLatestPhpVersion . ' maybe required)';
        else
            echo '<b class="ok">OK</b>';

        ?>
        <ul>
        <?php
        foreach ($aPhpSettings as $sName => $r) {
            $a = $this->checkPhpSetting($sName, $r);
            echo "<li>$sName = " . $this->format_output($a['real_val'], $r) ." - ";
            if ($a['res'])
                echo '<b class="ok">OK</b>';
            elseif ($r['warn'])
                echo "<b class='warn'>WARNING</b> (should be {$r['op']} " . $this->format_output($r['val'], $r) . ")";
            else
                echo "<b class='fail'>FAIL</b> (must be {$r['op']} " . $this->format_output($r['val'], $r) . ")";
            echo "</li>\n";
        }
        ?>
        </ul>
    </li>
    <li><b>MySQL</b>:
        <?php
            $oDb = BxDolDb::getInstance();
            $sMysqlVer = $oDb->res('select version()')->fetchColumn();
            echo $sMysqlVer . ' - ';
            if (preg_match ('/^(\d+)\.(\d+)\.(\d+)/', $sMysqlVer, $m)) {
                $sMysqlVer = "{$m[1]}.{$m[2]}.{$m[3]}";
                if (version_compare($sMysqlVer, $sMinMysqlVer, '<'))
                    echo '<b class="fail">FAIL</b> (your version is incompatible with Dolphin, must be at least ' . $sMinMysqlVer . ')';
                else
                    echo '<b class="ok">OK</b>';
            } else {
                echo '<b class="undef">UNDEFINED</b>';
            }
        ?>
    </li>
    <li><b>Web-server</b>:
        <?php
            echo $_SERVER['SERVER_SOFTWARE'];
        ?>
        <ul>
            <?php
                $bIsNginx = (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

                if(!$bIsNginx) {
                    foreach ($aRequiredApacheModules as $sName => $sNameCompiledName)
                        echo '<li>' . $sName . ' - ' . $this->checkApacheModule($sName, $sNameCompiledName) . '</li>';
                }
            ?>
        </ul>
    </li>
    <li><b>OS</b>:
        <?php
            echo php_uname();
        ?>
    </li>
</ul>

<h2>Hardware requirements</h2>
<p>
    Hardware requirements can not be determined automatically - <a href="#manual_audit">manual server audit</a> may be reqired.
</p>

<h2>Site setup</h2>
<ul>
    <li>
        <b>Dolphin version</b> =
        <?php
            $sDolphinVer = $GLOBALS['site']['ver'] . '.' . $GLOBALS['site']['build'];
            echo $sDolphinVer . ' - ';
            if (!version_compare($sDolphinVer, $sLatestDolphinVer, '>='))
                echo '<b class="warn">WARNING</b> (your Dolphin version is outdated please upgrade to the latest ' . $sLatestDolphinVer . ' version)';
            else
                echo '<b class="ok">OK</b>';
        ?>
    </li>
    <li>
        <b>files and folders permissions</b>
        <br />
        Please <a href="javascript:void(0);" onclick="switchAdmPage($('#main_menu1'));">click here</a> to find out if dolphin permissions are correct.
    </li>
    <li>
        <b>ffmpeg</b>
        <pre class="code"><?php echo `{$sDolphinPath}flash/modules/global/app/ffmpeg.exe 2>&1`;?></pre>
        if you don't know if output is correct then <a href="#manual_audit">manual server audit</a> may be reqired.
    </li>
    <li>
        <script language="javascript">
            function bx_sys_adm_audit_test_email()
            {
                $('#bx-sys-adm-audit-test-email').html('Sending...');
                $.post('<?php echo $GLOBALS['site']['url_admin']; ?>host_tools.php?action=audit_send_test_email', function(data) {
                    $('#bx-sys-adm-audit-test-email').html(data);
                });
            }
        </script>
        <b>mail sending - </b>
        <span id="bx-sys-adm-audit-test-email"><a href="javascript:void(0);" onclick="bx_sys_adm_audit_test_email()">click here</a> to send test email to <?php echo $sEmailToCkeckMailSending; ?></span>
    </li>
    <li>
        <b>cronjobs</b>
        <pre class="code"><?php echo `crontab -l 2>&1`;?></pre>
        if you are unsure if output is correct then <a href="#manual_audit">manual server audit</a> may be reqired.
    </li>
    <li>
        <b>last cronjob execution time - </b>
        <span><?php $iCronTime = (int)getParam('sys_cron_time'); echo !empty($iCronTime) ? getLocaleDate($iCronTime, BX_DOL_LOCALE_DATE) : (function_exists('_t') ? _t('_None') : 'None'); ?></span>
    </li>
    <li>
        <b>media server</b>
        <br />
        Please follow <a href="<?php echo $GLOBALS['site']['url_admin']; ?>flash.php">this link</a> to check media server settings. Also you can try video chat - if video chat is working then most probably that flash media server is working correctly, however it doesn't guarantee that all other flash media server application will work.
    </li>
    <li>
        <b>forums</b>
        <br />
        Please follow <a href="<?php echo BX_DOL_URL_ROOT; ?>forum/">this link</a> to check if forum is functioning properly. If it is working but '[L[' signs are displayed everywhere, then you need to <a href="<?php echo BX_DOL_URL_ROOT; ?>forum/?action=goto&manage_forum=1">compile language file</a> (you maybe be need to compile language file separately for every language and template you have).
    </li>
</ul>

<h2>Site optimization</h2>
<ul>
    <li><b>PHP</b>:
        <ul>
            <li><b>PHP accelerator</b> =
            <?php
                $sAccel = $this->getPhpAccelerator();
                if (!$sAccel)
                    echo 'NO - <b class="warn">WARNING</b> (Dolphin can be much faster if you install some PHP accelerator))';
                else
                    echo $sAccel . ' - <b class="ok">OK</b>';
            ?>
            </li>
            <li><b>PHP setup</b> =
            <?php
                $sSapi = php_sapi_name();
                echo $sSapi . ' - ';
                if (0 === strcasecmp('cgi', $sSapi))
                    echo '<b class="warn">WARNING</b> (your PHP setup maybe very inefficient, <a href="?action=phpinfo">please check it for sure</a> and try to switch to mod_php, apache dso module or FastCGI)';
                else
                    echo '<b class="ok">OK</b>';
            ?>
            </li>
        </ul>
    </li>
    <li><b>MySQL</b>:
        <ul>
            <?php
                foreach ($aMysqlSettings as $sName => $r) {
                    $a = $this->checkMysqlSetting($sName, $r);
                    $operation = ($r['op'] === 'strcasecmp') ? '' : $r['op'];
                    echo "<li><b>$sName</b> = " . $this->format_output($a['real_val'], $r) ." - " . ($a['res'] ? '<b class="ok">OK</b>' : "<b class='fail'>FAIL</b> (must be {$operation} " . $this->format_output($r['val'], $r) . ")") . "</li>\n";
                }
            ?>
        </ul>
    </li>
    <li><b>Web-server</b>:
        <ul>
            <li>
                <b>User-side caching for static conten</b> =
                <a href="<?php echo $this->getUrlForGooglePageSpeed('LeverageBrowserCaching'); ?>">click here to check it in Google Page Speed</a>
                <br />
                If it is not enabled then please consider implement this optimization, since it improve perceived site speed and save the bandwidth, refer to <a target="_blank" href="https://www.boonex.com/trac/dolphin/wiki/HostingServerSetupRecommendations#Usersidecachingforstaticcontent">this tutorial</a> on how to do this.
                <br />
                <?php
                    $sName = 'expires_module';
                    echo 'To apply this optimization you need to have <b>' . $sName . '</b> Apache module - ' . $this->checkApacheModule($sName);
                ?>
            </li>
            <li>
                <b>Server-side content compression</b> = can be checked <a href="#manual_audit">manually</a> or in "Page Speed" tool build-in into browser.
                <br />
                If it is not enabled then please consider implement this optimization, since it improve perceived site speed and save the bandwidth, refer to <a href="https://www.boonex.com/trac/dolphin/wiki/HostingServerSetupRecommendations#Serversidecontentcompression">this tutorial</a> on how to do this.
                </textarea>
                <br />
                <?php
                    $sName = 'deflate_module';
                    echo 'To apply this optimization you need to have <b>' . $sName . '</b> Apache module - ' . $this->checkApacheModule($sName);
                ?>
            </li>
        </ul>
    </li>
    <li><b>Dolphin</b>:
        <ul>
            <?php

                foreach ($aDolphinOptimizationSettings as $sName => $a) {

                    echo "<li><b>$sName</b> = ";

                    echo ('always_on' == $a['enabled'] || getParam($a['enabled'])) ? 'On' : 'Off';

                    if ($a['cache_engine'])
                        echo " (" . getParam($a['cache_engine']) . ' based cache engine)';

                    echo ' - ';

                    if ('always_on' != $a['enabled'] && !getParam($a['enabled']))
                        echo '<b class="fail">FAIL</b> (please enable this cache in Dolphin Admin Panel -> Settings -> Advanced Settings)';
                    elseif ($a['check_accel'] && !$this->getPhpAccelerator() && 'File' == getParam($a['cache_engine']))
                        echo '<b class="warn">WARNING</b> (installing PHP accelerator will speed-up file cache)';
                    else
                        echo '<b class="ok">OK</b>';

                    echo "</li>\n";
                }

            ?>
        </ul>
    </li>
</ul>

<a name="manual_audit"></a>
<h2>Manual Server Audit</h2>
<p>
    Some things can not be determined automatically, manual server audit is required to check it. If you don't know how to do it by yourself you can submit <a target="_blank" href="https://www.boonex.com/help/contact">BoonEx Server Audit Request</a>.
</p>

<?php

        return ob_get_clean();
    }

    function checkPhpSetting($sName, $a)
    {
        $mixedVal = ini_get($sName);
        $mixedVal = $this->format_input ($mixedVal, $a);

        switch ($a['op']) {
            case 'without':
                $aFuncsDisabled = explode(',', $mixedVal);
                $aFuncsMustBeEnabled = explode(',', $a['val']);
                $a = array_intersect($aFuncsDisabled, $aFuncsMustBeEnabled);
                $bResult = !$a;
                break;
            case 'module':
                $bResult = extension_loaded($a['val']);
                $mixedVal = $bResult ? $a['val'] : '';
                break;
            case 'val':
                $mixedVal = $bResult = $a['val'];
                break;
            case '>':
                $bResult = (isset($a['unlimited']) && $mixedVal == $a['unlimited']) ? true : ($mixedVal > $a['val']);
                break;
            case '>=':
                $bResult = (isset($a['unlimited']) && $mixedVal == $a['unlimited']) ? true : ($mixedVal >= $a['val']);
                break;
            case '=':
            default:
                $bResult = ($mixedVal == $a['val']);
        }
        return array ('res' => $bResult, 'real_val' => $mixedVal);
    }

    function checkMysqlSetting($sName, $a)
    {
        $mixedVal = $this->mysqlGetOption($sName);
        $mixedVal = $this->format_input ($mixedVal, $a);

        switch ($a['op']) {
            case '>':
                $bResult = ($mixedVal > $a['val']);
                break;
            case '>=':
                $bResult = ($mixedVal >= $a['val']);
                break;
            case 'strcasecmp':
                $bResult = 0 === strcasecmp($mixedVal, $a['val']);
                break;
            case '=':
            default:
                $bResult = ($mixedVal == $a['val']);
        }
        return array ('res' => $bResult, 'real_val' => $mixedVal);
    }

    function format_output ($mixedVal, $a)
    {
        switch ($a['type']) {
            case 'bool':
                return $mixedVal ? 'On' : 'Off';
            case 'bytes':
                return format_bytes($mixedVal, true);
            default:
                return $mixedVal;
        }
    }

    function format_input ($mixedVal, $a)
    {
        switch (isset($a['type'])) {
            case 'bytes':
                return $this->format_bytes($mixedVal);
            default:
                return $mixedVal;
        }
    }

    function format_bytes($val)
    {
        return return_bytes($val);
    }

    function checkApacheModule ($sModule, $sNameCompiledName = '')
    {
        $a = array (
            'deflate_module' => 'mod_deflate',
            'expires_module' => 'mod_expires',
        );
        if (!$sNameCompiledName && isset($a[$sModule]))
            $sNameCompiledName = $a[$sModule];

        if (function_exists('apache_get_modules')) {

            $aModules = apache_get_modules();
            $ret = in_array($sNameCompiledName, $aModules);

        } else {

            $sApachectlPath = trim(`which apachectl`);
            if (!$sApachectlPath)
                $sApachectlPath = trim(`which apache2ctl`);
            if (!$sApachectlPath)
                $sApachectlPath = trim(`which /usr/local/apache/bin/apachectl`);
            if (!$sApachectlPath)
                $sApachectlPath = trim(`which /usr/local/apache/bin/apache2ctl`);
            if (!$sApachectlPath)
                return '<b class="undef">UNDEFINED</b> (try to check manually: apachectl -M 2>&1 | grep ' . $sModule . ')';

            $ret = (boolean)`$sApachectlPath -M 2>&1 | grep $sModule`;
            if (!$ret)
                $ret = (boolean)`$sApachectlPath -l 2>&1 | grep $sNameCompiledName`;
        }

        return $ret ? '<b class="ok">OK</b>' : '<b class="fail">FAIL</b> (You will need to install ' . $sModule . ' for Apache)';
    }


    function getPhpAccelerator ()
    {
        $aAccelerators = array (
            'eAccelerator' => array('op' => 'module', 'val' => 'eaccelerator'),
            'APC' => array('op' => 'module', 'val' => 'apc'),
            'XCache' => array('op' => 'module', 'val' => 'xcache'),
            'OPcache' => array('op' => 'val', 'val' => function_exists('opcache_get_status') && ($a = opcache_get_status(false)) ? $a['opcache_enabled'] : false),
        );
        foreach ($aAccelerators as $sName => $r) {
            $a = $this->checkPhpSetting($sName, $r);
            if ($a['res'])
                return $sName;
        }
        return false;
    }

    function mysqlGetOption ($s)
    {
        return db_value("SELECT @@{$s}");
    }

    function getUrlForGooglePageSpeed ($sRule)
    {
        $sUrl = urlencode(BX_DOL_URL_ROOT);
        return 'http://pagespeed.googlelabs.com/#url=' . $sUrl . '&mobile=false&rule=' . $sRule;
    }

    function sendTestEmail ()
    {
        $sEmailToCkeckMailSending = getParam('site_email');
        $mixedRet = sendMail($sEmailToCkeckMailSending, 'Audit Test Email', 'Sample text for testing<br /><u><b>Sample text for testing</b></u>');
        if (!$mixedRet)
            return '<b class="fail">FAIL</b> (mail send failed)';
        else
            return 'test mail was send, please check ' . $sEmailToCkeckMailSending . ' mailbox';
    }
}
