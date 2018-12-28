<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( '../inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPFM.php' );

send_headers_page_changed();

$logged['admin'] = member_auth( 1, true, true );

switch(bx_get('action')) {
    case 'get':
        header( 'Content-Type:text/javascript' );
        echo bx_charts_get_json(bx_get('o'), bx_get('from'), bx_get('to'));
        break;
}

function bx_charts_get_json($sObject, $sFrom, $sTo)
{
    $aObject = $GLOBALS['MySQL']->getRow("SELECT * FROM `sys_objects_charts` WHERE `object` = ? AND `active` = ?", [$sObject, 1]);
    if (!$aObject)
        return json_encode(array('error' => _t('_Error Occured')));

    $iFrom = bx_charts_get_ts($sFrom);
    $iTo = bx_charts_get_ts($sTo, true);
    if (!$iFrom || !$iTo)
        return json_encode(array('error' => _t('_Error Occured')));

    $aData = bx_charts_get_data($aObject, $iFrom, $iTo);
    if (!$aData)
        return json_encode(array('error' => _t('_Empty')));

    $aRet = array (
        'title' => _t($aObject['title']),
        'data' => $aData,
        'hide_date_range' => $aObject['field_date_dt'] || $aObject['field_date_ts'] ? false : true,
        'column_date' => $aObject['column_date'] >= 0 ? $aObject['column_date'] : false,
        'column_count' => $aObject['column_count'] >= 0 ? $aObject['column_count'] : false,
        'type' => $aObject['type'] ? $aObject['type'] : 'AreaChart',
        'options' => $aObject['options'] ? unserialize($aObject['options']) : false,
    );

    return json_encode($aRet);
}

function bx_charts_get_ts($s, $isNowIfError = false)
{
    $a = explode('-', $s); // YYYY-MM-DD
    if (!$a || empty($a[0]) || empty($a[1]) || empty($a[2]) || !(int)$a[0] || !(int)$a[1] || !(int)$a[2])
        return $isNowIfError ? time() : false;
    return mktime(0, 0, 0, $a[1], $a[2], $a[0]);
}

function bx_charts_get_dt_from_ts($iTs)
{
    return date('Y-m-d', $iTs);
}

function bx_charts_get_data($aObject, $iFrom, $iTo)
{
    // build query
    $sQuery = $aObject['query'] ? $aObject['query'] : "SELECT {field_date_formatted} AS `period`, COUNT(*) AS {object} FROM {table} WHERE {field_date} >= '{from}' AND {field_date} <= '{to}' GROUP BY `period` ORDER BY {field_date} ASC";
    $a = array (
        'field_date_formatted' => "DATE_FORMAT(" . ($aObject['field_date_dt'] ? "`{$aObject['field_date_dt']}`" : "FROM_UNIXTIME(`{$aObject['field_date_ts']}`)") . ", '%Y-%m-%d')",
        'object' => $aObject['object'],
        'table' => "`{$aObject['table']}`",
        'field_date' => "`" . ($aObject['field_date_dt'] ? $aObject['field_date_dt'] : $aObject['field_date_ts']) . "`",
        'from' => $aObject['field_date_dt'] ? bx_charts_get_dt_from_ts($iFrom) . ' 00:00:00' : $iFrom,
        'to' => $aObject['field_date_dt'] ? bx_charts_get_dt_from_ts($iTo) . ' 23:59:59' : $iTo + 24*3600 - 1,
    );
    foreach ($a as $k => $v)
      $sQuery = str_replace('{'.$k.'}', $v, $sQuery);

    // get data
    if ($aObject['column_date'] >= 0)
        $aData = $GLOBALS['MySQL']->getAllWithKey($sQuery, $aObject['column_date'], [], PDO::FETCH_NUM);
    else
        $aData = $GLOBALS['MySQL']->getAll($sQuery, [], PDO::FETCH_NUM);
    if (!$aData)
        return false;

    // fill in missed days and convert values to numbers
    if ($aObject['column_date'] >= 0) {
        $iColumnsNum = count(array_pop(array_slice($aData, 0, 1)));
        for ($i = $iFrom ; $i <= ($iTo + 24*3600 - 1); $i += 24*60*60) {
            $sDate = date('Y-m-d', $i);
            $aRow = array ();
            for ($j = 0 ; $j < $iColumnsNum ; ++$j) {
                $v = isset($aData[$sDate]) ? (int)$aData[$sDate][$j] : 0;
                $aRow[$j] = ($j == $aObject['column_date'] ? $sDate : $v);
            }
            $aData[$sDate] = $aRow;
        }
    } 
    else {
        foreach ($aData as $k => $v)
            foreach ($aData[$k] as $kk => $vv)
                if ($kk > 0)
                    $aData[$k][$kk] = (int)$aData[$k][$kk];
    }

    // return values only
    ksort($aData);
    return array_values($aData);
}
