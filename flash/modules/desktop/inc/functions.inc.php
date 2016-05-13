<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/

function getOnlineUsersInfo($sUserId, $bInit)
{
    global $aXmlTemplates;

    $aSaved = getSavedUsers();
    $aActive = getActiveUsers($sUserId);
    $aFriends = getFriends($sUserId);
    saveUsers($aActive);

    $sContents = "";
    if($bInit)
        $aFullUsers = array_unique(array_merge($aActive['online'], $aFriends));
    else {
        $aFullUsers = array_diff($aActive['online'], $aSaved['online'], $aFriends);
        $aNewOfflineUsers = array_intersect($aSaved['online'], $aActive['offline']);
        $aNewOnlineUsers = array_intersect($aSaved['offline'], $aActive['online'], $aFriends);
        for($i=0; $i<count($aNewOfflineUsers); $i++)
            $sContents .= parseXml($aXmlTemplates['user'], $aNewOfflineUsers[$i], FALSE_VAL);
        for($i=0; $i<count($aNewOnlineUsers); $i++)
            $sContents .= parseXml($aXmlTemplates['user'], $aNewOnlineUsers[$i], TRUE_VAL);
    }

    $rResult = getUsersMedia($aFullUsers);
    if($rResult != null) {
        for($i=0; $i<$rResult->rowCount(); $i++) {
            $aUser = $rResult->fetch();
            $aUserInfo = getUserInfo($aUser['ID']);
            $sOnline = in_array($aUser['ID'], $aActive['online']) ? TRUE_VAL : FALSE_VAL;
            $sFriend = in_array($aUser['ID'], $aFriends) ? TRUE_VAL : FALSE_VAL;
            $sMusic = $aUser['CountMusic'] > 0 ? TRUE_VAL : FALSE_VAL;
            $sVideo = $aUser['CountVideo'] > 0 ? TRUE_VAL : FALSE_VAL;
            $sContents .= parseXml($aXmlTemplates['user'], $aUser['ID'], $aUserInfo['nick'], $aUserInfo['sex'], $aUserInfo['age'], $aUserInfo['photo'], $aUserInfo['profile'], $sOnline, $sFriend, $sMusic, $sVideo, $aUserInfo['desc']);
        }
    }
    return makeGroup($sContents, "users");
}

function saveUsers($aUsers)
{
    global $sModule;

    $iTime = time() + 31536000;
    foreach($aUsers as $sKey => $aValue)
        setCookie("ray_" . $sModule . "_" . $sKey, implode(",", $aValue), $iTime);
}

function getSavedUsers()
{
    global $sModule;
    return array(
        'online' => explode(",", $_COOKIE["ray_" . $sModule . "_online"]),
        'offline' => explode(",", $_COOKIE["ray_" . $sModule . "_offline"])
    );
}
