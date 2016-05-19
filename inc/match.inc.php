<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolProfileFields.php');

function getMatchFields()
{
    $oDb = BxDolDb::getInstance();

    return $oDb->fromCache('sys_profile_match_fields', 'getAllWithKey',
        'SELECT `ID`, `Name`, `MatchField`, `MatchPercent`, `Type` FROM `sys_profile_fields` WHERE `MatchPercent` > 0',
        'ID');
}

function getMatchProfiles($iProfileId, $bForce = false, $sSort = 'none')
{
    $aResult = array();
    if (!getParam('enable_match')) {
        return $aResult;
    }

    $oDb = BxDolDb::getInstance();

    if (!(int)$iProfileId) {
        return $aResult;
    }

    if (!$bForce) {
        $aMatch = $oDb->getRow("SELECT `profiles_match` FROM `sys_profiles_match` WHERE `profile_id` = ? AND `sort` = ?",
            [$iProfileId, $sSort]);
        if (!empty($aMatch)) {
            return unserialize($aMatch['profiles_match']);
        }
    } else {
        $oDb->query("DELETE FROM `sys_profiles_match` WHERE `profile_id` = $iProfileId");
    }

    $aProf = getProfileInfo($iProfileId);

    if (empty($aProf)) {
        return $aResult;
    }

    $aMathFields = getMatchFields();
    $iAge        = (int)$oDb->getOne("SELECT DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), '{$aProf['DateOfBirth']}')), '%Y') + 0 AS age");

    foreach ($aMathFields as $sKey => $aFields) {
        // TODO: pdo dynamic bindings
        $aMathFields[$sKey]['profiles'] = array();

        if ($aProf[$aFields['Name']] && $aMathFields[$aFields['MatchField']]['Name']) {
            if ($aMathFields[$aFields['MatchField']]['Name'] == 'DateOfBirth') {
                if ($iAge) {
                    $sCond = "(DATE_FORMAT(FROM_DAYS(DATEDIFF(NOW(), `DateOfBirth`)), '%Y') + 0) = $iAge";
                }
            } elseif ($aMathFields[$aFields['MatchField']]['Type'] == 'select_set' && $aFields['Type'] != 'select_set') {
                $sCond = "FIND_IN_SET('" . process_db_input($aProf[$aFields['Name']], BX_TAGS_NO_ACTION,
                        BX_SLASHES_NO_ACTION) . "', `{$aMathFields[$aFields['MatchField']]['Name']}`)";
            } elseif ($aMathFields[$aFields['MatchField']]['Type'] != 'select_set' && $aFields['Type'] == 'select_set') {
                $sCond = "FIND_IN_SET(`{$aMathFields[$aFields['MatchField']]['Name']}`, '" . process_db_input($aProf[$aFields['Name']],
                        BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION) . "')";
            } elseif ($aMathFields[$aFields['MatchField']]['Type'] == 'select_set' && $aFields['Type'] == 'select_set') {
                $a = explode(',', $aProf[$aFields['Name']]);
                if ($a) {
                    $sCond = '(';
                    foreach ($a as $sVal) {
                        $sCond .= "FIND_IN_SET('" . process_db_input($sVal, BX_TAGS_NO_ACTION,
                                BX_SLASHES_NO_ACTION) . "', `{$aMathFields[$aFields['MatchField']]['Name']}`) OR ";
                    }
                    $sCond = rtrim($sCond, ' OR');
                    $sCond .= ')';
                }
            } elseif ($aMathFields[$aFields['MatchField']]['Name']) {
                $sCond = "`{$aMathFields[$aFields['MatchField']]['Name']}` = '" . process_db_input($aProf[$aFields['Name']],
                        BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION) . "'";
            }
            if ($sCond) {
                $aMathFields[$sKey]['profiles'] = $oDb->getAllWithKey("SELECT `ID` FROM `Profiles` WHERE `Status` = 'Active' AND `ID` != ? AND $sCond",
                    'ID', [$iProfileId]);
            }
        }
    }

    $sCondSort = '';
    if ($sSort == 'activity') {
        $sCondSort = 'ORDER BY `DateLastNav` DESC';
    } else {
        if ($sSort == 'date_reg') {
            $sCondSort = 'ORDER BY `DateReg` DESC';
        }
    }

    $iPercentThreshold = getParam('match_percent');
    $aProfiles         = $oDb->getColumn("SELECT `ID` FROM `Profiles` WHERE `Status` = 'Active' AND `ID` != $iProfileId $sCondSort");
    foreach ($aProfiles as $iProfId) {
        $iPercent = 0;

        foreach ($aMathFields as $sKey => $aFields) {
            if (isset($aFields['profiles'][$iProfId])) {
                $iPercent += (int)$aFields['MatchPercent'];
            }
        }

        if ($iPercent >= $iPercentThreshold) {
            $aResult[] = $iProfId;
        }
    }

    $oDb->query("INSERT INTO `sys_profiles_match`(`profile_id`, `sort`, `profiles_match`) VALUES(?, ?, ?)", [
        $iProfileId,
        $sSort,
        serialize($aResult)
    ]);

    return $aResult;
}

function getProfilesMatch($iPID1 = 0, $iPID2 = 0)
{
    if (!getParam('enable_match')) {
        return 0;
    }

    $iPID1 = (int)$iPID1;
    $iPID2 = (int)$iPID2;

    if (!$iPID1 or !$iPID2) {
        return 0;
    }

    if ($iPID1 == $iPID2) {
        return 0;
    }

    $aProf1 = getProfileInfo($iPID1);
    $aProf2 = getProfileInfo($iPID2);

    if (empty($aProf1) || empty($aProf2)) {
        return 0;
    }

    $iMatch      = 0;
    $aMathFields = getMatchFields();
    foreach ($aMathFields as $sKey => $aFields) {
        $bRes = false;

        if ($aProf1[$aFields['Name']]) {
            if ($aMathFields[$aFields['MatchField']]['Name'] == 'DateOfBirth') {
                $bRes = age($aProf1['DateOfBirth']) == age($aProf2['DateOfBirth']);
            } elseif ($aMathFields[$aFields['MatchField']]['Type'] == 'select_set' || $aFields['Type'] == 'select_set') {
                $a1   = explode(',', $aProf1[$aFields['Name']]);
                $a2   = explode(',', $aProf2[$aMathFields[$aFields['MatchField']]['Name']]);
                $bRes = array_intersect($a1, $a2);
            } else {
                $bRes = $aProf1[$aFields['Name']] == $aProf2[$aMathFields[$aFields['MatchField']]['Name']];
            }
        }

        if ($bRes) {
            $iMatch += (int)$aFields['MatchPercent'];
        }
    }

    return $iMatch;
}
