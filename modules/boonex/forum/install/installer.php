<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstaller.php");

function is_compiled_template ($s)
{
    return preg_match ('/\/[a-z]+_[a-z]+$/', $s);
}

class BxOrcaInstaller extends BxDolInstaller
{
    function __construct($aConfig)
    {
        parent::__construct($aConfig);
        $this->_aActions = array_merge($this->_aActions, array(
            'check_requirements' => array(
                'title' => 'Check Orca Forum requrements',
            ),
        ));
    }

    function uninstall($aParams)
    {
        $ret = parent::uninstall($aParams);

        $sPath = BX_DIRECTORY_PATH_MODULES . 'boonex/forum/';

        $a = $this->_read_in_dir ("{$sPath}cachejs/", 'is_file');
        array_walk($a, array($this, '_unlink'));

        $a = $this->_read_in_dir ("{$sPath}classes/", 'is_dir');
        array_walk($a, array($this, '_rmdir_rf'));

        $a = $this->_read_in_dir ("{$sPath}conf/", 'is_file');
        array_walk($a, array($this, '_unlink'));

        $a = $this->_read_in_dir ("{$sPath}js/", 'is_dir');
        array_walk($a, array($this, '_rmdir_rf'));

        $a = $this->_read_in_dir ("{$sPath}log/", 'is_file');
        array_walk($a, array($this, '_unlink'));

        $a = $this->_read_in_dir ("{$sPath}layout/", 'is_compiled_template');
        array_walk($a, array($this, '_rmdir_rf'));

        return $ret;
    }

    function _read_in_dir ($sDir, $sFunc)
    {
        $aRet = array ();
        if ($h = opendir($sDir)) {
            while (false !== ($sFile = readdir($h))) {
                if ($sFile != '.' && $sFile != '..' && $sFile[0] != '.' && $sFunc($sDir.$sFile)) {
                    $aRet[] = $sDir.$sFile;
                }
            }
            closedir($h);
        }
        return $aRet;
    }

    function _rmdir_rf($dirname)
    {
        if ($dirHandle = opendir($dirname)) {
            chdir($dirname);
            while ($file = readdir($dirHandle)) {
                if ($file == '.' || $file == '..') continue;
                if (is_dir($file)) $this->_rmdir_rf($file);
                else unlink($file);
            }
            chdir('..');
            closedir($dirHandle);
            @rmdir($dirname);
        }
    }

    function _unlink($s)
    {
        unlink ($s);
    }

    function actionCheckRequirements ()
    {
        $iErrors = 0;
        if (((int)phpversion()) >= 5) { // PHP 5
            $iErrors += (class_exists('XsltProcessor')) ? 0 : 1;
        } else { // PHP 4
            $iErrors += (function_exists('domxml_xslt_stylesheet_file')) ? 0 : 1;
            $iErrors += (function_exists('xslt_create')) ? 0 : 1;
        }
        return array('code' => !$iErrors ? BX_DOL_INSTALLER_SUCCESS : BX_DOL_INSTALLER_FAILED, 'content' => '');
    }

    function actionCheckRequirementsFailed ()
    {
        return '
            <div style="border:1px solid red; padding:10px;">

                Orca requres PHP XSLT extension. Please enable it first then proceed with installation.

                <br /><br />

                For <u>PHP4</u>, make sure that PHP is compiled with the following extensions:
                <pre>
                    --with-dom --enable-xslt --with-xslt-sablot
                </pre>
                If these PHP extensions are enabled, then you can see <u>domxml</u> and <u>xslt</u> extensions in phpinfo() with the following options:
                <pre>
                    DOM/XML: enabled
                    XSLT support: enabled
                </pre>

                <br /><br />

                For <u>PHP5</u>, make sure that PHP is compiled with the following extension:
                <pre>
                    --with-xsl
                </pre>
                If this PHP extension is enabled, then you can see <u>xslt</u> extensions in phpinfo() with the following option:
                <pre>
                    XSLT support: enabled
                </pre>
            </div>';
    }
}
