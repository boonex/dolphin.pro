<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */


define('BX_INSTALL_DO_NOT_EXIT_ON_ERROR', 1);

// include necessary files to perform install
$_REQUEST['action'] = 'empty';
$aPathInfo = pathinfo(__FILE__);
require_once($aPathInfo['dirname'] . '/index.php');

/**
 * Install command line interface
 */
class BxDolInstallCmd
{
    protected $_sHeaderPath;
    protected $_aSiteConfig;
    protected $_isQuiet = false;
    protected $_aReturnCodes = array(
        'success' => array ('code' => 0, 'msg' => 'Success.'),
        'already installed' => array ('code' => 0, 'msg' => 'Script is already installed. Can\'t perform install.'),
        'requirements failed' => array ('code' => 2, 'msg' => 'Requirements aren\'t met.'),
        'permissions failed' => array ('code' => 3, 'msg' => 'Folders and/or files permissions aren\'t correct.'),
        'create config failed' => array ('code' => 4, 'msg' => 'Form data was not submitted.'),
        'lang compile failed' => array ('code' => 5, 'msg' => 'Language compilation failed.'),
    );

    public function __construct()
    {
        $aPathInfo = pathinfo(__FILE__);

        $this->_aSiteConfig = array (
            'site_url' => 'localhost',
            'site_dir' => str_replace('/install', '/', $aPathInfo['dirname']),
            'php_path' => '/usr/bin/php',
            // form data below
            'site_config' => true,
            'db_name' => 'test',
            'db_user' => 'root',
            'db_password' => 'root',
            'site_title' => 'Dolphin',
            'site_email' => 'no-reply@example.com',
            'admin_email' => 'admin@example.com',
            'admin_username' => 'admin',
            'admin_password' => 'dolphin',
        );

        $this->_sHeaderPath = $this->_aSiteConfig['site_dir'] . 'inc/header.inc.php';
    }

    public function main()
    {
        // set neccessary options

        $a = getopt('hq', $this->getOptions());

        if (isset($a['h']))
            $this->finish($this->_aReturnCodes['success']['code'], $this->getHelp());

        if (isset($a['q']))
            $this->_isQuiet = true;

        $this->_aSiteConfig = array_merge($this->_aSiteConfig, $a);

        // initialize environment

        $this->init();

        // peform install

        $this->checkRequirements();
        $this->checkPermissions();
        $this->createSiteConfig();
        $this->compileLanguage();

        $this->finish($this->_aReturnCodes['success']['code'], $this->_aReturnCodes['success']['msg']);
    }

    protected function getOptions()
    {
        $a = array ();
        foreach ($this->_aSiteConfig as $sKey => $sValue)
            if ('site_config' != $sKey)
                $a[] = "$sKey::";
        return $a;
    }

    protected function getHelp()
    {
        $s = "Usage: php cmd.php [options]\n";

        $s .= str_pad("\t -h", 35) . "Print this help\n";
        $s .= str_pad("\t -q", 35) . "Quiet\n";

        foreach ($this->_aSiteConfig as $sKey => $sVal)
            if ('site_config' != $sKey)
                $s .= str_pad("\t --{$sKey}=<value>", 35) . "Default value: {$sVal}\n";

        $s .= "\n";
        $s .= "Return codes:\n";
        foreach ($this->_aReturnCodes as $r)
            $s .= str_pad("\t {$r['code']}", 5) . "{$r['msg']}\n";

        return $s;
    }

    protected function finish($iCode, $sMsg)
    {
        if (!$this->_isQuiet)
            fwrite($iCode ? STDERR : STDOUT, $sMsg . "\n");

        exit($iCode);
    }

    protected function init()
    {
        // skip this test if script is already installed
        if (file_exists($this->_sHeaderPath))
            $this->finish($this->_aReturnCodes['already installed']['code'], $this->_aReturnCodes['already installed']['msg']);
    }

    public function checkRequirements()
    {
        if (!empty($GLOBALS['aErrors']))
            $this->finish($this->_aReturnCodes['requirements failed']['code'], $this->_aReturnCodes['requirements failed']['msg']);
    }

    public function checkPermissions()
    {
        $sError = '';
        $sErrorMsg = checkPreInstallPermission($sError);

        if ($sErrorMsg)
            $this->finish($this->_aReturnCodes['permissions failed']['code'], $this->_aReturnCodes['permissions failed']['msg']);
    }

    public function createSiteConfig()
    {
        $this->_setSiteConfigVars();
        genInstallationProcessPage();

        if (!file_exists($this->_sHeaderPath))
            $this->finish($this->_aReturnCodes['create config failed']['code'], $this->_aReturnCodes['create config failed']['msg']);
    }

    public function compileLanguage()
    {
        global $oSysTemplate, $tmpl;
        require_once($GLOBALS['aPathInfo']['dirname'] . '/../inc/header.inc.php');
        require_once(BX_DIRECTORY_PATH_INC . 'design.inc.php');
        performInstallLanguages();
        
        $sLang = db_value("SELECT `Name` FROM `sys_localization_languages` LIMIT 1");
        if (empty($sLang) || !file_exists($GLOBALS['aPathInfo']['dirname'] . '/../langs/lang-' . $sLang . '.php'))
            $this->finish($this->_aReturnCodes['lang compile failed']['code'], $this->_aReturnCodes['lang compile failed']['msg']);
    }

    protected function _setSiteConfigVars() 
    {
        $aSiteConfigMapVars = array (
            'site_url' => &$this->_aSiteConfig['site_url'],
            'dir_root' => &$this->_aSiteConfig['site_dir'],
            'dir_php' => &$this->_aSiteConfig['php_path'],

            // db conf
            'sql_file' => eval($GLOBALS['aDbConf']['sql_file']['def_exp']),
            'db_host' => $GLOBALS['aDbConf']['db_host']['def'],
            'db_port' => $GLOBALS['aDbConf']['db_port']['def'],
            'db_sock' => $GLOBALS['aDbConf']['db_sock']['def'],
            'db_name' => &$this->_aSiteConfig['db_name'],
            'db_user' => &$this->_aSiteConfig['db_user'],
            'db_password' => &$this->_aSiteConfig['db_password'],

            // general
            'site_title' => &$this->_aSiteConfig['site_title'],
            'site_desc' => '',
            'site_email' => &$this->_aSiteConfig['admin_email'],
            'notify_email' => &$this->_aSiteConfig['site_email'],
            'bug_report_email' => &$this->_aSiteConfig['admin_email'],
            'admin_username' => &$this->_aSiteConfig['admin_username'],
            'admin_password' => &$this->_aSiteConfig['admin_password'],
        );

        foreach ($aSiteConfigMapVars as $k => $v)
            $_POST[$k] = $_REQUEST[$k] = $v;
    }
}

$o = new BxDolInstallCmd();
$o->main();

