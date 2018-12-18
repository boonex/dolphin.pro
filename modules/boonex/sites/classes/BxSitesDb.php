<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

define('BX_SITES_TABLE_PREFIX', 'bx_sites');

class BxSitesDb extends BxDolModuleDb
{
    var $_oConfig;
    var $sTablePrefix;
    var $_sFieldId = 'id';
    var $_sFieldAuthorId = 'ownerid';
    var $_sFieldUri = 'entryUri';
    var $_sFieldTitle = 'title';
    var $_sFieldDescription = 'description';
    var $_sFieldThumb = 'photo';

    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct();

        $this->_oConfig = $oConfig;
          $this->sTablePrefix = $oConfig->getDbPrefix();
    }

    function getMembershipActions()
    {
        $sSql = "SELECT `ID` AS `id`, `Name` AS `name` FROM `sys_acl_actions` WHERE `Name`='use sites'";
        return $this->getAll($sSql);
    }

    function getSiteById($iSiteId)
    {
        return $this->getRow ("SELECT * FROM `" . BX_SITES_TABLE_PREFIX . "_main` WHERE `id` = ? LIMIT 1", [$iSiteId]);
    }

    function getEntryByIdAndOwner ($iSiteId, $iUnusedParam = 0, $bUnusedParam = true)
    {
        return $this->getSiteById($iSiteId);
    }

    function getSiteByEntryUri($sEntryUri)
    {
        return $this->getRow ("SELECT * FROM `" . BX_SITES_TABLE_PREFIX . "_main` WHERE `entryUri` = ? LIMIT 1", [$sEntryUri]);
    }

    function getSiteLatest()
    {
        return $this->getRow ("SELECT * FROM `" . BX_SITES_TABLE_PREFIX . "_main` ORDER BY `date` DESC LIMIT 1");
    }

    function getSiteByUrl($sUrl)
    {
        return $this->getRow ("SELECT * FROM `" . BX_SITES_TABLE_PREFIX . "_main` WHERE `url` = ? LIMIT 1", [$sUrl]);
    }

    function getSites()
    {
        return $this->getAll("SELECT * FROM `" . BX_SITES_TABLE_PREFIX . "_main`");
    }

    function getSitesByAuthor($iProfileId)
    {
        return $this->getAll("SELECT * FROM `" . BX_SITES_TABLE_PREFIX . "_main` WHERE `ownerid` = $iProfileId");
    }

    function markFeatured($iSiteId)
    {
        return $this->query ("UPDATE `" . BX_SITES_TABLE_PREFIX . "_main` SET `featured` = (`featured` - 1)*(`featured` - 1) WHERE `id` = $iSiteId LIMIT 1");
    }

    function deleteSiteById($iSiteId)
    {
        return $this->query("DELETE FROM `" . BX_SITES_TABLE_PREFIX . "_main` WHERE `id` = $iSiteId");
    }

    function getProfileIdByNickName($sNick)
    {
        return $this->getOne ("SELECT `ID` FROM `Profiles` WHERE `NickName` = '$sNick' LIMIT 1");
    }

    function getSitesByMonth($iYear, $iMonth, $iNextYear, $iNextMonth)
    {
        return $this->getAll("SELECT *, DAYOFMONTH(FROM_UNIXTIME(`date`)) AS `Day`
            FROM `" . BX_SITES_TABLE_PREFIX . "_main`
            WHERE `date` >= UNIX_TIMESTAMP('$iYear-$iMonth-1') AND `date` < UNIX_TIMESTAMP('$iNextYear-$iNextMonth-1') AND `status` = 'approved'");
    }

    function getSettingsCategory($sName)
    {
        return $this->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name` = '{$sName}' LIMIT 1");
    }

    function setStatusSite($iSiteId, $sStatus)
    {
        $this->query("UPDATE `" . BX_SITES_TABLE_PREFIX . "_main` SET `status` = '$sStatus' WHERE `id` = $iSiteId");
    }

    function getCountByOwnerAndStatus($iOwnerId, $sStatus)
    {
        return $this->getOne ("SELECT count(*) FROM `" . BX_SITES_TABLE_PREFIX . "_main` WHERE `status` = '$sStatus' AND `ownerid` = $iOwnerId");
    }

    // BEGIN STW INTEGRATION

    function addRequest($aSTWArgs, $aResponse, $sHash)
    {
        $iTimestamp = time();
        $aSTWArgs = process_db_input($aSTWArgs);
        $aResponse = process_db_input($aResponse);
        $sQuery = "UPDATE `{$this->sTablePrefix}stw_requests` SET `timestamp` = '" . process_db_input($iTimestamp) . "', `capturedon` = '" . $aResponse['stw_last_captured'] . "', `invalid` = '" . $aResponse['invalid'] . "',
            `stwerrcode` = '" . $aResponse['stw_response_code'] . "', `error` = '" . $aResponse['error'] . "', `errcode` = '" . $aResponse['stw_response_status'] . "' WHERE `hash` = '" . process_db_input($sHash) . "'";
        if ($this->query($sQuery) == 0) { // doesn't exist
            $this->res("INSERT INTO `{$this->sTablePrefix}stw_requests` SET `domain` = '" . $aSTWArgs['stwurl'] . "', `timestamp` = '" . process_db_input($iTimestamp) . "', `capturedon` = '" . $aResponse['stw_last_captured'] . "',
                `quality` = '" . $aSTWArgs['stwqual'] . "', `full` = '" . $aSTWArgs['stwfull'] . "', `xmax` = '" . $aSTWArgs['stwxmax'] . "', `ymax` = '" . $aSTWArgs['stwymax'] . "',
                `nrx` = '" . $aSTWArgs['stwnrx'] . "', `nry` = '" . $aSTWArgs['stwnry'] . "', `invalid` = '" . $aResponse['invalid'] . "', `stwerrcode` = '" . $aResponse['stw_response_code'] . "',
                `error` = '" . $aResponse['error'] . "', `errcode` = '" . $aResponse['stw_response_status'] . "', `hash` = '" . process_db_input($sHash) . "'");
        }
    }

    function addAccountInfo($sKeyID, $aResponse)
    {
        $iTimestamp = time();
        $aResponse = process_db_input($aResponse);
        $sQuery = "UPDATE `{$this->sTablePrefix}stwacc_info` SET `account_level` = '" . $aResponse['stw_account_level'] . "', `inside_pages` = '" . $aResponse['stw_inside_pages'] . "', `custom_size` = '" . $aResponse['stw_custom_size'] . "',
            `full_length` = '" . $aResponse['stw_full_length'] . "', `refresh_ondemand` = '" . $aResponse['stw_refresh_ondemand'] . "', `custom_delay` = '" . $aResponse['stw_custom_delay'] . "', `custom_quality` = '" . $aResponse['stw_custom_quality'] . "',
            `custom_resolution` = '" . $aResponse['stw_custom_resolution'] . "', `custom_messages` = '" . $aResponse['stw_custom_messages'] . "', `timestamp` = '" . process_db_input($iTimestamp) . "' WHERE `key_id` = '" . process_db_input($sKeyID) . "'";
        if ($this->query($sQuery) == 0) { // doesn't exist
            $this->res("INSERT INTO `{$this->sTablePrefix}stwacc_info` SET `key_id` = '" . process_db_input($sKeyID) . "', `account_level` = '" . $aResponse['stw_account_level'] . "', `inside_pages` = '" . $aResponse['stw_inside_pages'] . "', `custom_size` = '" . $aResponse['stw_custom_size'] . "',
            `full_length` = '" . $aResponse['stw_full_length'] . "', `refresh_ondemand` = '" . $aResponse['stw_refresh_ondemand'] . "', `custom_delay` = '" . $aResponse['stw_custom_delay'] . "', `custom_quality` = '" . $aResponse['stw_custom_quality'] . "',
            `custom_resolution` = '" . $aResponse['stw_custom_resolution'] . "', `custom_messages` = '" . $aResponse['stw_custom_messages'] . "', `timestamp` = '" . process_db_input($iTimestamp) . "'");
        }
    }

    function getAccountInfo($sKeyID)
    {
        return $this->getRow("SELECT * FROM `{$this->sTablePrefix}stwacc_info` WHERE `key_id` = ? LIMIT 1", [$sKeyID]);
    }

    // END STW INTEGRATION
}
