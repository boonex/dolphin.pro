<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModuleDb');
bx_import('BxDolIO');

class BxDolInstallerUtils extends BxDolIO
{
    function __construct()
    {
        parent::__construct();
    }

    function isXsltEnabled()
    {
        if (((int)phpversion()) >= 5) {
            if (class_exists ('DOMDocument') && class_exists ('XsltProcessor'))
                return true;
        } else {
            if (function_exists('domxml_xslt_stylesheet_file'))
                return true;
            elseif (function_exists ('xslt_create'))
                return true;
        }
        return false;
    }

    function isAllowUrlInclude()
    {
        $sAllowUrlInclude = ini_get('allow_url_include');
        return !($sAllowUrlInclude == 0);
    }

    public static function isModuleInstalled($sUri)
    {
        $oModuleDb = new BxDolModuleDb();
        return $oModuleDb->isModule($sUri);
    }
}
