<?php
/***************************************************************************
*
* IMPORTANT: This is a commercial product made by BoonEx Ltd. and cannot be modified for other than personal usage.
* This product cannot be redistributed for free or a fee without written permission from BoonEx Ltd.
* This notice may not be removed from the source code.
*
***************************************************************************/
require_once(BX_DIRECTORY_PATH_INC . "utils.inc.php");
require_once(BX_DIRECTORY_PATH_CLASSES . "BxDolInstallerUtils.php");

function getUserVideoLink()
{
    global $sModulesUrl;
    if(BxDolInstallerUtils::isModuleInstalled("videos"))
        return $sModulesUrl . "video/videoslink.php?id=#user#";

    return "";
}

function getUserMusicLink()
{
    global $sModulesUrl;
    if(BxDolInstallerUtils::isModuleInstalled("sounds"))
        return $sModulesUrl . "mp3/soundslink.php?id=#user#";
    return "";
}

function getBlockedUsers($sBlockerId)
{
    $aUsers = array();
    $rResult = getResult("SELECT `Profile` FROM `sys_block_list` WHERE `ID`='" . $sBlockerId . "'");
    while($aUser = $rResult->fetch())
        $aUsers[] = $aUser['Profile'];
    return $aUsers;
}
