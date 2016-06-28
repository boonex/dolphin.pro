<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once('../../../inc/header.inc.php');
require_once( BX_DIRECTORY_PATH_INC . "db.inc.php" );

$sSQL = '';
$iCategID = (int)bx_get('cat_id');

$aResult = array();

switch (bx_get('action')) {
    case 'get_subcat_info':
    default:
        if ($iCategID>0) {
            $sCustName1 = $sCustName2 = $sUnit = $sUnit2 = '';
            $sSQL = "
                SELECT `bx_ads_category_subs`.`ID` , `bx_ads_category_subs`.`NameSub` AS `Name`, `CustomFieldName1`, `CustomFieldName2`, `Unit1`, `Unit2`
                FROM `bx_ads_category_subs`
                INNER JOIN `bx_ads_category` ON (`bx_ads_category`.`ID`=`bx_ads_category_subs`.`IDClassified`)
                WHERE `bx_ads_category_subs`.`IDClassified` = '{$iCategID}'
                ORDER BY `Name` ASC
            ";

            $aSubCats = array();
            $vData = db_res($sSQL);
            while ($aUnit = $vData->fetch()) {
                if ($sCustName1 == '') {
                    $sCustName1 = htmlspecialchars($aUnit['CustomFieldName1']);
                }
                if ($sCustName2 == '') {
                    $sCustName2 = htmlspecialchars($aUnit['CustomFieldName2']);
                }
                if ($sUnit == '') {
                    $sUnit = htmlspecialchars($aUnit['Unit1']);
                }
                if ($sUnit2 == '') {
                    $sUnit2 = htmlspecialchars($aUnit['Unit2']);
                }

                $iSubCatID = (int)$aUnit['ID'];
                $iSubCatName = ($aUnit['Name']);
                $aSubCats[] = array('id' => $iSubCatID, 'value' => $iSubCatName);
            }

            $aResult['CustomFieldName1'] = $sCustName1;
            $aResult['CustomFieldName2'] = $sCustName2;
            $aResult['Unit'] = $sUnit;
            $aResult['Unit2'] = $sUnit2;
            $aResult['SubCats'] = $aSubCats;

            echo json_encode($aResult);
        }
        break;
}
