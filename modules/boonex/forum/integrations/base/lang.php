<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

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
