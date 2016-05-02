<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

define('BX_SYS_PRE_VALUES_TABLE', 'sys_pre_values');

$oCache = $GLOBALS['MySQL']->getDbCacheObject();
$GLOBALS['aPreValues'] = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_pre_values'));
if (null === $GLOBALS['aPreValues'])
    compilePreValues();

function getPreKeys ()
{
    return $GLOBALS['MySQL']->fromCache('sys_prevalues_keys', 'getAll', "SELECT DISTINCT `Key` FROM `" . BX_SYS_PRE_VALUES_TABLE . "`");
}

function getPreValues ($sKey, $aFields = array(), $iTagsFilter = BX_TAGS_NO_ACTION)
{
    $sqlFields = "*";
    if (is_array($aFields) && !empty($aFields)) {
        foreach ($aFields as $sValue)
            $sqlFields .= "`$sValue`, ";
        $sqlFields = trim($sqlFields, ', ');
    }
    $sqlQuery = "SELECT $sqlFields FROM `" . BX_SYS_PRE_VALUES_TABLE ."`
                WHERE `Key` = ?
                ORDER BY `Order` ASC";
    return $GLOBALS['MySQL']->getAllWithKey($sqlQuery, 'Value', [$sKey]);
}

function getPreValuesCount ($sKey, $aFields = array(), $iTagsFilter = BX_TAGS_NO_ACTION)
{
    $sKeyDb = process_db_input($sKey, $iTagsFilter);
    return $GLOBALS['MySQL']->getOne("SELECT COUNT(*) FROM `" . BX_SYS_PRE_VALUES_TABLE . "` WHERE `Key` = '$sKeyDb'");
}

function compilePreValues()
{
    $GLOBALS['MySQL']->cleanCache('sys_prevalues_keys');

    $aPreValues = array ();
    $aKeys = getPreKeys();

    foreach ($aKeys as $aKey) {

        $sKey = $aKey['Key'];
        $aPreValues[$sKey] = array ();

        $aRows = getPreValues($sKey);
        foreach ($aRows as $aRow) {

            $aPreValues[$sKey][$aRow['Value']] = array ();

            foreach ($aRow as $sValKey => $sValue) {
                if ($sValKey == 'Key' or $sValKey == 'Value' or $sValKey == 'Order')
                    continue; //skip key, value and order. they already used

                if (!strlen($sValue))
                    continue; //skip empty values

                $aPreValues[$sKey][$aRow['Value']][$sValKey] = $sValue;
            }

        }

    }

    $oCache = $GLOBALS['MySQL']->getDbCacheObject();
    $oCache->setData ($GLOBALS['MySQL']->genDbCacheKey('sys_pre_values'), $aPreValues);

    $GLOBALS['aPreValues'] = $aPreValues;
}
