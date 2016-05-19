<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_INC . "match.inc.php");
bx_import('BxDolAlerts');
bx_import('BxDolDb');
bx_import('BxDolEmailTemplates');

class BxDolAlertsResponceMatch extends BxDolAlertsResponse
{
    function response($oAlert)
    {
        $iRecipientId = $oAlert->iObject;

        if ($oAlert->sUnit == 'profile') {
            switch ($oAlert->sAction) {
                case 'join':
                case 'edit':
                    $this->_checkProfileMatch($iRecipientId, $oAlert->sAction);
                    break;

                case 'change_status':
                    $this->_profileChangeStatus();
                    break;

                case 'delete':
                    $this->_profileDelete($iRecipientId);
                    break;
            }
        }
    }

    function _checkProfileMatch($iProfileId, $sAction)
    {
        if (!getParam('enable_match'))
            return;

        $aProfile = getProfileInfo($iProfileId);

        if ($aProfile['Status'] == 'Active' && ($aProfile['UpdateMatch'] || $sAction == 'join')) {
            $oDb = BxDolDb::getInstance();

            // clear field "UpdateMatch"
            $oDb->query("UPDATE `Profiles` SET `UpdateMatch` = 0 WHERE `ID`= $iProfileId");

            // clear cache
            $oDb->query("DELETE FROM `sys_profiles_match`");

            // get send mails
            $aSendMails = $oDb->getRow("SELECT `profiles_match` FROM `sys_profiles_match_mails` WHERE `profile_id` = ?", [$iProfileId]);
            $aSend = !empty($aSendMails) ? unserialize($aSendMails['profiles_match']) : array();

            $aProfiles = getMatchProfiles($iProfileId);
            foreach ($aProfiles as $iProfId) {
                if (isset($aSend[(int)$iProfId]))
                    continue;

                $aProfile = getProfileInfo($iProfId);
                if (1 != $aProfile['EmailNotify'] || 'Unconfirmed' == $aProfile['Status'])
                    continue;

                $oEmailTemplate = new BxDolEmailTemplates();
                $aMessage = $oEmailTemplate->parseTemplate('t_CupidMail', array(
                    'StrID' => $iProfId,
                    'MatchProfileLink' => getProfileLink($iProfileId)
                ), $iProfId);

                if (!empty($aProfile) && $aProfile['Status'] == 'Active')
                    $oDb->query("INSERT INTO `sys_sbs_queue`(`email`, `subject`, `body`) VALUES('" . $aProfile['Email'] . "', '" . process_db_input($aMessage['subject'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION) . "', '" . process_db_input($aMessage['body'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION) . "')");

                $aSend[(int)$iProfId] = 0;
            }

            if (empty($aSendMails))
                $oDb->query("INSERT INTO `sys_profiles_match_mails`(`profile_id`, `profiles_match`) VALUES($iProfileId, '" . serialize($aSend) . "')");
            else
                $oDb->query("UPDATE `sys_profiles_match_mails` SET `profiles_match` = '" . serialize($aSend) . "' WHERE `profile_id` = $iProfileId");
        }
    }

    function _profileDelete($iProfileId)
    {
        $oDb = BxDolDb::getInstance();

        $oDb->query("DELETE FROM `sys_profiles_match`");
        $oDb->query("DELETE FROM `sys_profiles_match_mails` WHERE `profile_id` = $iProfileId");
    }

    function _profileChangeStatus()
    {
        $oDb = BxDolDb::getInstance();
        $oDb->query("DELETE FROM `sys_profiles_match`");
    }
}
