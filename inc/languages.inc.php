<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_DOL_LANGUAGE_DEFAULT', 'en');
define('BX_DOL_LANGUAGE_CATEGORY_SYSTEM', 1);

if (!defined ('BX_SKIP_INSTALL_CHECK')) {
    $sCurrentLanguage = getCurrentLangName(false);
    if( !$sCurrentLanguage ) {
        echo '<br /><b>Fatal error:</b> Cannot apply localization.';
        exit;
    }
    require_once( BX_DIRECTORY_PATH_ROOT . "langs/lang-{$sCurrentLanguage}.php" );
}

require_once(BX_DIRECTORY_PATH_INC . 'db.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'utils.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
require_once(BX_DIRECTORY_PATH_INC . 'params.inc.php');

if (!defined ('BX_SKIP_INSTALL_CHECK')) {
    getCurrentLangName(true);

    if (isset($_GET['lang'])) {
        bx_import('BxDolPermalinks');
        $oPermalinks = new BxDolPermalinks();
        if ($oPermalinks->redirectIfNecessary(array('lang')))
            exit;
    }
}

function getCurrentLangName($isSetCookie = true)
{
    $sLang = '';

    if( !$sLang && !empty($_GET['lang']) ) $sLang = tryToGetLang( $_GET['lang'], $isSetCookie );
    if( !$sLang && !empty($_POST['lang']) ) $sLang = tryToGetLang( $_POST['lang'], $isSetCookie );
    if( !$sLang && !empty($_COOKIE['lang']) ) $sLang = tryToGetLang( $_COOKIE['lang'] );
    if( !$sLang && ($sLangProfile = getProfileLangName()) ) $sLang = tryToGetLang( $sLangProfile );
    if( !$sLang && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ) $sLang = tryToGetLang( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
    if( !$sLang ) $sLang = tryToGetLang( getParam( 'lang_default' ) );
    if( !$sLang ) $sLang = tryToGetLang( BX_DOL_LANGUAGE_DEFAULT );

    setlocale(LC_TIME, $sLang.'_'.strtoupper($sLang).'.utf-8', $sLang.'_'.strtoupper($sLang).'.utf8', $sLang.'.utf-8', $sLang.'.utf8', $sLang);

    return $sLang;
}

function tryToGetLang( $sLangs, $bSetCookie = false )
{
    $sLangs = trim( $sLangs );
    if( !$sLangs )
        return '';

    $sLangs = preg_replace( '/[^a-zA-Z0-9,;-]/m', '', $sLangs ); // we do not need 'q=0.3'. we are using live queue :)
    $sLangs = strtolower( $sLangs );

    if( !$sLangs )
        return '';

    $aLangs = explode( ',', $sLangs ); // ru,en-us;q=0.7,en;q=0.3 => array( 'ru' , 'en-us;q=0.7' , 'en;q=0.3' );
    foreach( $aLangs as $sLang ) {
        if( !$sLang ) continue;

        list( $sLang ) = explode( ';', $sLang, 2 ); // en-us;q=0.7 => en-us
        if( !$sLang ) continue;

        // check with country
        if( checkLangExists( $sLang ) ) {
            if( $bSetCookie && ($_COOKIE['lang'] != $sLang) && ($GLOBALS['glLangSet'] != $sLang) ) {
                setLangCookie( $sLang );
                $GLOBALS['glLangSet'] = $sLang;
            }
            return $sLang;
        }

        //drop country
        list( $sLang, $sCntr ) = explode( '-', $sLang, 2 ); // en-us => en
        if( !$sLang or !$sCntr ) continue; //no lang or nothing changed

        //check again. without country
        if( checkLangExists( $sLang ) ) {
            if( $bSetCookie )
                setLangCookie( $sLang );
            return $sLang;
        }
    }

    return '';
}

function checkLangExists( $sLang )
{
    if (!preg_match('/^[A-Za-z0-9_]+$/', $sLang))
        return false;
    if( file_exists( BX_DIRECTORY_PATH_ROOT . "langs/lang-{$sLang}.php" ) )
        return true;

    // $sQuery = "SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = '$sLang'";
    // $iLangID = (int)db_value( $sQuery );

    $iLangID = (int)$GLOBALS['MySQL']->fromCache('checkLangExists_'.$sLang, 'getOne', "SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = '$sLang'");

    if( !$iLangID )
        return false;

    if( compileLanguage( $iLangID ) )
        return true;

    return false;
}

function setLangCookie( $sLang )
{
    $iProfileId = getLoggedId();

    if ($iProfileId) {
        $iLangID = getLangIdByName($sLang);
        if (!$iLangID)
            $iLangID = 0 ;

        db_res( 'UPDATE `Profiles` SET `LangID` = ' . (int) $iLangID . ' WHERE `ID` = ' . (int) $_COOKIE['memberID'] );

        // recompile profile cache ;
        createUserDataFile($iProfileId);
    }

    setcookie( 'lang', '',     time() - 60*60*24,     '/' );
    setcookie( 'lang', $sLang, time() + 60*60*24*365, '/' );
}

function getLangIdByName($sLangName)
{
    return (int)db_value( "SELECT `ID` FROM `sys_localization_languages` WHERE `Name` = '" . process_db_input($sLangName, BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION) . "'" );
}

function getLangNameById($sLangId)
{
    return db_value( "SELECT `Name` FROM `sys_localization_languages` WHERE `ID` = '" . (int)$sLangId . "'" );
}

function getProfileLangName($iProfile = false)
{
    if (!$iProfile)
        $iProfile = isset($_COOKIE['memberID']) ? (int)$_COOKIE['memberID'] : 0;
    if (!$iProfile)
        return false;
    return $GLOBALS['MySQL']->fromMemory('profile_lang_' . $iProfile, 'getOne', 'SELECT `l`.`Name` FROM `sys_localization_languages` AS `l` INNER JOIN `Profiles` AS `p` ON (`p`.`LangID` = `l`.`ID`) WHERE `p`.`ID` = ' . $iProfile);
}

function getLangsArr( $bAddFlag = false, $bRetIDs = false )
{
    $aLangsFull = getLangsArrFull($bRetIDs);

    $aLangs = array();
    foreach ($aLangsFull as $sKey => $aLang) {
        $sFlag = $bAddFlag ? ($aLang['Flag'] ? $aLang['Flag'] : 'xx') : '';
        $aLangs[ $sKey ] = $aLang['Title'] . $sFlag;
    }

    return $aLangs;
}

function getLangsArrFull($bRetIDs = false)
{
    return $GLOBALS['MySQL']->getAllWithKey('SELECT * FROM `sys_localization_languages` ORDER BY `Title` ASC', $bRetIDs ? 'ID' : 'Name');
}

function deleteLanguage($langID = 0)
{
    $langID = (int)$langID;

    if($langID <= 0) return false;

    $resLangs = db_res('
            SELECT	`ID`, `Name`
            FROM	`sys_localization_languages`
            WHERE	`ID` = '.$langID);

    if($resLangs->rowCount() <= 0) return false;

    $arrLang = $resLangs->fetch();

    $numStrings = db_res('
        SELECT COUNT(`IDKey`)
        FROM `sys_localization_strings`
        WHERE `IDLanguage` = ?', [$langID]);

    $numStrings = $numStrings->fetch(PDO::FETCH_NUM);
    $numStrings = $numStrings[0];

    $res1 = db_res('DELETE FROM `sys_localization_strings` WHERE `IDLanguage` = '.$langID);

    if(db_affected_rows($res1) < $numStrings) return false;

    $res2 = db_res('DELETE FROM `sys_localization_languages` WHERE `ID` = '.$langID);

    if(db_affected_rows($res2) <= 0) return false;

    @unlink( BX_DIRECTORY_PATH_ROOT . 'langs/lang-'.$arrLang['Name'].'.php');

    // delete from email templates
    $sQuery = "DELETE FROM `sys_email_templates` WHERE `LangID` = '{$langID}'";
    db_res($sQuery);

    return true;
}

function getLocalizationKeys()
{
    $resKeys = db_res('SELECT `ID`, `IDCategory`, `Key` FROM `sys_localization_keys`');

    $arrKeys = array();

    while($arr = $resKeys->fetch()) {
        $ID = $arr['ID'];
        unset($arr['ID']);
        $arrKeys[$ID] = $arr;
    }

    return $arrKeys;
}

function getLocalizationKeysBy($aParams)
{
	global $MySQL;

	$aMethod = array('name' => 'getAll', 'params' => array(0 => 'query'));
	$sSelectClause = $sJoinClause = $sWhereClause = $sGroupClause = $sOrderClause = $sLimitClause = "";

	$sSelectClause = "`tk`.`ID` AS `id`, `tk`.`IDCategory` AS `category_id`, `tk`.`Key` AS `key`";

	if(!isset($aParams['order']) || empty($aParams['order']))
		$sOrderClause = " `tk`.`Key` ASC ";

	switch($aParams['type']) {
		case 'by_language_name_key_key':
			$aMethod['name'] = 'getAllWithKey';
			$aMethod['params'][1] = 'key';

			$sSelectClause .= ", `ts`.`String` AS `string` ";
			$sJoinClause = " LEFT JOIN `sys_localization_strings` AS `ts` ON `tk`.`ID`=`ts`.`IDKey` LEFT JOIN `sys_localization_languages` AS `tl` ON `ts`.`IDLanguage`=`tl`.`ID` ";
			$sWhereClause = " AND `tl`.`Name`='" . $aParams['value'] . "' ";
			break;

		case 'by_language_id_key_key':
			$aMethod['name'] = 'getAllWithKey';
			$aMethod['params'][1] = 'key';

			$sSelectClause .= ", `ts`.`String` AS `string` ";
			$sJoinClause = " LEFT JOIN `sys_localization_strings` AS `ts` ON `tk`.`ID`=`ts`.`IDKey` ";
			$sWhereClause = " AND `ts`.`IDLanguage`='" . (int)$aParams['value'] . "' ";
			break;
	}

	$aMethod['params'][0] = "SELECT " . $sSelectClause . "
		FROM `sys_localization_keys` AS `tk`" . $sJoinClause . "
		WHERE 1" . $sWhereClause . " " . $sGroupClause . "
		ORDER BY" . $sOrderClause . $sLimitClause;
	return call_user_func_array(array($MySQL, $aMethod['name']), $aMethod['params']);
}

function getLocalizationStringParams($keyID)
{
    $keyID = (int)$keyID;

    $resParams = db_res("
        SELECT	`IDParam`,
                `Description`
        FROM	`sys_localization_string_params`
        WHERE	`IDKey` = $keyID
        ORDER BY `IDParam`
    ");

    $arrParams = array();

    while ($arr = $resParams->fetch()) {
        $arrParams[(int)$arr['IDParam']] = $arr['Description'];
    }

    return $arrParams;
}

function getLocalizationCategories()
{
    $resCategories = db_res('SELECT `ID`, `Name` FROM `sys_localization_categories` ORDER BY `Name`');

    $arrCategories = array();

    while ($arr = $resCategories->fetch()) {
        $arrCategories[$arr['ID']] = $arr['Name'];
    }

    return $arrCategories;
}

function compileLanguage($langID = 0)
{
    $langID = (int)$langID;

    $newLine = "\n";

    if($langID <= 0) {
        $resLangs = db_res('SELECT `ID`, `Name` FROM `sys_localization_languages`');
    } else {
        $resLangs = db_res('
            SELECT	*
            FROM	`sys_localization_languages`
            WHERE	`ID` = '.$langID
        );
    }

    if ( $resLangs->rowCount() <= 0 )
        return false;

    while($arrLanguage = $resLangs->fetch()) {
    	$aKeys = getLocalizationKeysBy(array('type' => 'by_language_id_key_key', 'value' => $arrLanguage['ID']));
    	if($arrLanguage['Name'] != BX_DOL_LANGUAGE_DEFAULT && getParam('lang_subst_from_en') == 'on') {
			$aKeysAll = getLocalizationKeysBy(array('type' => 'by_language_name_key_key', 'value' => BX_DOL_LANGUAGE_DEFAULT));
			$aKeys = array_merge($aKeysAll, $aKeys);
    	}

    	$handle = fopen( BX_DIRECTORY_PATH_ROOT . "langs/lang-{$arrLanguage['Name']}.php", 'w');
        if($handle === false)
        	return false;

        $fileContent = "<"."?PHP{$newLine}\$LANG_INFO=" . var_export($arrLanguage, true) . ";{$newLine}\$LANG = array(";

        foreach($aKeys as $aKey) {
            $langKey = str_replace(array("\\", "'"), array("\\\\", "\\'"), $aKey['key']);
            $langStr = str_replace(array("\\", "'"), array("\\\\", "\\'"), $aKey['string']);

            $fileContent .= "{$newLine}\t'$langKey' => '$langStr',";
        }

        $fileContent = trim($fileContent, ',');

        $writeResult = fwrite($handle, $fileContent."{$newLine});?".">");
        if($writeResult === false) return false;

        if(fclose($handle) === false) return false;

        @chmod( BX_DIRECTORY_PATH_ROOT . "langs/lang-{$arrLanguage['Name']}.php", 0666);
    }

    return true;
}

function addStringToLanguage($langKey, $langString, $langID = -1, $categoryID = BX_DOL_LANGUAGE_CATEGORY_SYSTEM)
{
    // input validation
    $langID = (int)$langID;
    $categoryID = (int)$categoryID;

    if ( $langID == -1 ) {
        $resLangs = db_res('SELECT `ID`, `Name` FROM `sys_localization_languages`');
    } else {
        $resLangs = db_res('
            SELECT	`ID`, `Name`
            FROM	`sys_localization_languages`
            WHERE	`ID` = '.$langID);
    }

    $langKey = process_db_input($langKey, BX_TAGS_STRIP);
    $langString = process_db_input($langString, BX_TAGS_VALIDATE);

    $resInsertKey = db_res( "
        INSERT INTO	`sys_localization_keys`
        SET			`IDCategory` = $categoryID,
                    `Key` = '$langKey'", false );
    if ( !$resInsertKey || db_affected_rows($resInsertKey) <= 0 )
        return false;

    $keyID = db_last_id();

    while($arrLanguage = $resLangs->fetch()) {
        $resInsertString = db_res( "
            INSERT INTO	`sys_localization_strings`
            SET			`IDKey` = $keyID,
                        `IDLanguage` = {$arrLanguage['ID']},
                        `String` = '$langString'", false );
        if ( !$resInsertString || db_affected_rows($resInsertString) <= 0 )
            return false;

        compileLanguage($arrLanguage['ID']);
    }

    return true;
}

function updateStringInLanguage($langKey, $langString, $langID = -1)
{
    // input validation
    $langID = (int)$langID;

    if ( $langID == -1 ) {
        $resLangs = db_res('SELECT `ID`, `Name` FROM `sys_localization_languages`');
    } else {
        $resLangs = db_res('
            SELECT	`ID`, `Name`
            FROM	`sys_localization_languages`
            WHERE	`ID` = '.$langID);
    }

    $langKey = process_db_input($langKey, BX_TAGS_STRIP);
    $langString = process_db_input($langString, BX_TAGS_VALIDATE);

    $arrKey = db_arr( "
        SELECT	`ID`
        FROM	`sys_localization_keys`
        WHERE	`Key` = '$langKey'", false );

    if ( !$arrKey )
        return false;

    $keyID = $arrKey['ID'];

    while($arrLanguage = $resLangs->fetch()) {
        $resUpdateString = db_res( "
            UPDATE	`sys_localization_strings`
            SET			`String` = '$langString'
            WHERE		`IDKey` = $keyID
            AND			`IDLanguage` = {$arrLanguage['ID']}", false );
        if ( !$resUpdateString || db_affected_rows($resUpdateString) <= 0 )
            return false;
    }

    return true;
}

function deleteStringFromLanguage($langKey, $langID = -1)
{
    // input validation
    $langID = (int)$langID;

    if ( $langID == -1 ) {
        $resLangs = db_res('SELECT `ID`, `Name` FROM `sys_localization_languages`');
    } else {
        $resLangs = db_res('
            SELECT	`ID`, `Name`
            FROM	`sys_localization_languages`
            WHERE	`ID` = '.$langID);
    }

    $langKey = process_db_input($langKey, BX_TAGS_STRIP);
    $langString = empty($langString) ? '' : process_db_input($langString, BX_TAGS_VALIDATE);

    $arrKey = db_arr( "
        SELECT	`ID`
        FROM	`sys_localization_keys`
        WHERE	`Key` = '$langKey'", false );

    if ( !$arrKey )
        return false;

    $keyID = $arrKey['ID'];

    while($arrLanguage = $resLangs->fetch()) {
        $resDeleteString = db_res( "
            DELETE	FROM `sys_localization_strings`
            WHERE		`IDKey` = $keyID
            AND			`IDLanguage` = {$arrLanguage['ID']}", false );
        if ( !$resDeleteString || db_affected_rows($resDeleteString) <= 0 )
            return false;
    }

    $resDeleteKey = db_res( "
        DELETE FROM `sys_localization_keys`
        WHERE	`Key` = '$langKey' LIMIT 1", false );

    return !$resDeleteKey || db_affected_rows($resDeleteKey) <= 0 ? false : true;
}

function _t_action( $str, $arg0 = "", $arg1 = "", $arg2 = "" )
{
    return MsgBox( _t($str,$arg0,$arg1,$arg2) );
}

function _t_echo_action( $str, $arg0 = "", $arg1 = "", $arg2 = "" )
{
    return MsgBox( _t($str,$arg0,$arg1,$arg2) );
}

function echo_t_err( $str, $arg0 = "", $arg1 = "", $arg2 = "" )
{
    return MsgBox( _t($str,$arg0,$arg1,$arg2) );
}

function _t_err( $str, $arg0 = "", $arg1 = "", $arg2 = "" )
{
    return MsgBox( _t($str,$arg0,$arg1,$arg2) );
}

function _t($key, $arg0 = "", $arg1 = "", $arg2 = "")
{
    global $LANG;

    if(isset($LANG[$key])) {
        $str = $LANG[$key];
        $str = str_replace('{0}', $arg0, $str);
        $str = str_replace('{1}', $arg1, $str);
        $str = str_replace('{2}', $arg2, $str);
        return $str;
    } else {
        return $key;
    }
}

function _t_ext($key, $args)
{
    global $LANG;

    if(isset($LANG[$key])) {
        $str = $LANG[$key];

        if(!is_array($args)) {
            return str_replace('{0}', $args, $str);
        }

        foreach ($args as $key => $val) {
            $str = str_replace('{'.$key.'}', $val, $str);
        }

        return $str;
    } else {
        return $key;
    }
}

function _t_format_size ($iBytes, $iPrecision = 2)
{
    $aUnits = array('_sys_x_byte', '_sys_x_kilobyte', '_sys_x_megabyte', '_sys_x_gigabyte', '_sys_x_terabyte');

    $iBytes = max($iBytes, 0);
    $iPow = floor(($iBytes ? log($iBytes) : 0) / log(1024));
    $iPow = min($iPow, count($aUnits) - 1);

    $iBytes /= (1 << (10 * $iPow));
    return _t($aUnits[$iPow], round($iBytes, $iPrecision));
}

function bx_lang_name()
{
    return $GLOBALS['sCurrentLanguage'];
}

function bx_lang_info()
{
    return $GLOBALS['LANG_INFO'];
}
