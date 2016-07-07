<?php
/**
*                            Orca Interactive Forum Script
*                              ---------------
*     Started             : Mon Mar 23 2006
*     Copyright           : (C) 2007 BoonEx Group
*     Website             : http://www.boonex.com
* This file is part of Orca - Interactive Forum Script
* Creative Commons Attribution 3.0 License
**/

/**
 * file to get language string, overrride it if you use external language file
 *******************************************************************************/

function getLangString ($s, $sLang = '')
{
    global $gConf;

    if (!$sLang)
        $sLang = $gConf['lang'];

    require_once ($gConf['dir']['langs'] . $sLang .  '.php');
    return isset($GLOBALS['L'][$s]) ? $GLOBALS['L'][$s] : '_' . $s;
}

?>
