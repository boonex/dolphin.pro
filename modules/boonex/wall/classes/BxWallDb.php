<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

class BxWallDb extends BxDolModuleDb
{
    var $_oConfig;
    /*
     * Constructor.
     */
    function BxWallDb(&$oConfig)
    {
        parent::BxDolModuleDb();

        $this->_oConfig = $oConfig;
        $this->_sPrefix = $oConfig->getDbPrefix();
    }
    function insertData($aData)
    {
        foreach($aData['handlers'] as $aHandler) {
            //--- Delete module related events ---//
            $this->deleteEvent(array('type' => $aHandler['alert_unit'], 'action' => $aHandler['alert_action']));

            //--- Update Wall Handlers ---//
            $this->query("INSERT INTO `" . $this->_sPrefix . "handlers`(`alert_unit`, `alert_action`, `module_uri`, `module_class`, `module_method`, `groupable`, `group_by`, `timeline`, `outline`) VALUES('" . $aHandler['alert_unit'] . "', '" . $aHandler['alert_action'] . "', '" . $aHandler['module_uri'] . "', '" . $aHandler['module_class'] . "', '" . $aHandler['module_method'] . "', '" . $aHandler['groupable'] . "', '" . $aHandler['group_by'] . "', '" . $aHandler['timeline'] . "', '" . $aHandler['outline'] . "')");
        }

        //--- Update System Alerts ---//
        $iHandlerId = (int)$this->getOne("SELECT `id` FROM `sys_alerts_handlers` WHERE `name`='" . $this->_oConfig->getAlertSystemName() . "' LIMIT 1");

        foreach($aData['alerts'] as $aAlert)
            $this->query("INSERT INTO `sys_alerts`(`unit`, `action`, `handler_id`) VALUES('" . $aAlert['unit'] . "', '" . $aAlert['action'] . "', '" . $iHandlerId . "')");
    }
    function deleteData($aData)
    {
        foreach($aData['handlers'] as $aHandler) {
            //--- Delete module related events ---//
            $this->deleteEvent(array('type' => $aHandler['alert_unit'], 'action' => $aHandler['alert_action']));

            //--- Update Wall Handlers ---//
            $this->query("DELETE FROM `" . $this->_sPrefix . "handlers` WHERE `alert_unit`='" . $aHandler['alert_unit'] . "' AND `alert_action`='" . $aHandler['alert_action'] . "' AND `module_uri`='" . $aHandler['module_uri'] . "' AND `module_class`='" . $aHandler['module_class'] . "' AND `module_method`='" . $aHandler['module_method'] . "' LIMIT 1");
        }

        //--- Update System Alerts ---//
        $iHandlerId = (int)$this->getOne("SELECT `id` FROM `sys_alerts_handlers` WHERE `name`='" . $this->_oConfig->getAlertSystemName() . "' LIMIT 1");

        foreach($aData['alerts'] as $aAlert)
           $this->query("DELETE FROM `sys_alerts` WHERE `unit`='" . $aAlert['unit'] . "' AND `action`='" . $aAlert['action'] . "' AND `handler_id`='" . $iHandlerId . "' LIMIT 1");
    }
    function insertEvent($aParams)
    {
        if((int)$this->query("INSERT INTO `" . $this->_sPrefix . "events`(`" . implode("`, `", array_keys($aParams)) . "`, `date`) VALUES('" . implode("', '", array_values($aParams)) . "', UNIX_TIMESTAMP())") <= 0)
            return 0;

        $iId = (int)$this->lastId();
        if($iId > 0 && isset($aParams['owner_id']) && (int)$aParams['owner_id'] > 0) {
               //--- Wall -> Update for Alerts Engine ---//
            bx_import('BxDolAlerts');
            $oAlert = new BxDolAlerts('bx_' . $this->_oConfig->getUri(), 'update', $aParams['owner_id']);
            $oAlert->alert();
            //--- Wall -> Update for Alerts Engine ---//
        }

        return $iId;
    }
    function updateEvent($aParams, $iId)
    {
        $aUpdate = array();
        foreach($aParams as $sKey => $sValue)
           $aUpdate[] = "`" . $sKey . "`='" . $sValue . "'";
        $sSql = "UPDATE `" . $this->_sPrefix . "events` SET " . implode(", ", $aUpdate) . " WHERE `id`='" . $iId . "'";
        return $this->query($sSql);
    }
    function deleteEvent($aParams, $sWhereAddon = "")
    {
        $aWhere = array();
        foreach($aParams as $sKey => $sValue)
           $aWhere[] = "`" . $sKey . "`='" . $sValue . "'";
        $sSql = "DELETE FROM `" . $this->_sPrefix . "events` WHERE " . implode(" AND ", $aWhere) . $sWhereAddon;
        return $this->query($sSql);
    }
    function deleteEventCommon($aParams)
    {
        return $this->deleteEvent($aParams, " AND `type` LIKE '" . $this->_oConfig->getCommonPostPrefix() . "%'");
    }
    function getUser($mixed, $sType = 'id')
    {
        switch($sType) {
            case 'id':
                $sWhereClause = "`ID`='" . $mixed . "'";
                break;
            case 'username':
                $sWhereClause = "`NickName`='" . $mixed . "'";
                break;
        }

        $sSql = "SELECT `ID` AS `id`, `Couple` AS `couple`, `NickName` AS `username`, `Password` AS `password`, `Email` AS `email`, `Sex` AS `sex`, `Status` AS `status` FROM `Profiles` WHERE " . $sWhereClause . " LIMIT 1";
        $aUser = $this->getRow($sSql);

        if(empty($aUser))
            $aUser = array('id' => 0, 'couple' => 0, 'username' => _t('_wall_anonymous'), 'password' => '', 'email' => '', 'sex' => 'male');

        return $aUser;
    }

    //--- View Events Functions ---//
    function getHandlers($aParams = array())
    {
        $sMethod = 'getAll';
        $sWhereClause = '';

        switch($aParams['type']) {
            case 'timeline':
                $sWhereClause = "AND `timeline`='1'";
                break;
            case 'outline':
                $sWhereClause = "AND `outline`='1'";
                break;
            case 'by_uri':
                $sWhereClause = "AND `module_uri`='" . $aParams['value'] . "'";
                break;
        }

        $sSql = "SELECT
                `id` AS `id`,
                `alert_unit` AS `alert_unit`,
                `alert_action` AS `alert_action`,
                `module_uri` AS `module_uri`,
                `module_class` AS `module_class`,
                `module_method` AS `module_method`,
                `groupable` AS `groupable`,
                `group_by` AS `group_by`,
                `timeline` AS `timeline`,
                `outline` AS `outline`
            FROM `" . $this->_sPrefix . "handlers`
            WHERE 1 AND `alert_unit` NOT LIKE ('wall_common_%') " . $sWhereClause;
        return $this->$sMethod($sSql);
    }

    function getEvents($aParams)
    {
        global $sHomeUrl;
        $sJoinClause = $sWhereClause = $sOrderClause = $sLimitClause = "";

        $sWhereModuleFilter = '';
        if(isset($aParams['modules']) && !empty($aParams['modules']) && is_array($aParams['modules']))
        	$sWhereModuleFilter = "AND `type` IN ('" . implode("','", $aParams['modules']) . "') ";

        if(isset($aParams['timeline']) && strpos($aParams['timeline'], BX_WALL_DIVIDER_TIMELINE) !== false) {
            list($iTLStart, $iTLEnd) = explode(BX_WALL_DIVIDER_TIMELINE, $aParams['timeline']);

            $iNowMorning = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $iNowEvening = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
            $sWhereClause .= "AND `date`>='" . ($iNowMorning - 86400 * $iTLEnd) . "' AND `date`<='" . ($iNowEvening - 86400 * $iTLStart) . "' ";
        }

        switch($aParams['type']) {
            case 'id':
                $sWhereClause = "AND `te`.`id`='" . $aParams['object_id'] . "' ";
                $sLimitClause = "LIMIT 1";
                break;

            case 'owner':
		        if($sWhereModuleFilter == '') {
					$aHidden = $this->_oConfig->getHandlersHidden(BX_WALL_VIEW_TIMELINE);
					$sWhereModuleFilter = "AND `th`.`timeline`='1' AND `th`.`id` NOT IN ('" . implode("','", $aHidden) . "') ";
				}

                if(!empty($aParams['owner_id'])) {
                    if(is_array($aParams['owner_id'])) {
                    	$sIds = implode("','", $aParams['owner_id']);

                        $sWhereClause .= "AND (`te`.`owner_id` IN ('" . $sIds . "') OR (`te`.`owner_id`='0' AND `te`.`object_id` IN ('" . $sIds . "'))) ";
                    }
                    else
                    	$sWhereClause .= "AND (`te`.`owner_id`='" . $aParams['owner_id'] . "' OR (`te`.`owner_id`='0' AND `te`.`object_id`='" . $aParams['owner_id'] . "')) ";
                }
                else 
                	$sWhereClause .= "AND NOT(`te`.`owner_id`<>'0' AND `te`.`type` LIKE '" . $this->_oConfig->getCommonPostPrefix() . "%' AND `te`.`action`='') ";

                $sWhereClause .= isset($aParams['filter']) ? $this->_getFilterAddon($aParams['owner_id'], $aParams['filter']) : '';
                $sWhereClause .= $sWhereModuleFilter;
                $sOrderClause = isset($aParams['order']) ? "ORDER BY `te`.`date` " . strtoupper($aParams['order']) : "";
                $sLimitClause = isset($aParams['count']) ? "LIMIT " . $aParams['start'] . ", " . $aParams['count'] : "";
                break;

            case 'last':
		        if($sWhereModuleFilter == '') {
					$aHidden = $this->_oConfig->getHandlersHidden(BX_WALL_VIEW_TIMELINE);
					$sWhereModuleFilter = "AND `th`.`timeline`='1' AND `th`.`id` NOT IN ('" . implode("','", $aHidden) . "') ";
				}

                if(!empty($aParams['owner_id'])) {
                    if(is_array($aParams['owner_id'])) {
                    	$sIds = implode("','", $aParams['owner_id']);

                        $sWhereClause .= "AND (`te`.`owner_id` IN ('" . $sIds . "') OR (`te`.`owner_id`='0' AND `te`.`object_id` IN ('" . $sIds . "'))) ";
                    }
                    else
                    	$sWhereClause .= "AND (`te`.`owner_id`='" . $aParams['owner_id'] . "' OR (`te`.`owner_id`='0' AND `te`.`object_id`='" . $aParams['owner_id'] . "')) ";
                }
                else 
                	$sWhereClause .= "AND NOT(`te`.`owner_id`<>'0' AND `te`.`type` LIKE '" . $this->_oConfig->getCommonPostPrefix() . "%' AND `te`.`action`='') ";

                $sWhereClause .= isset($aParams['filter']) ? $this->_getFilterAddon($aParams['owner_id'], $aParams['filter']) : '';
                $sWhereClause .= $sWhereModuleFilter;
                $sOrderClause = "ORDER BY `te`.`date` ASC";
                $sLimitClause = "LIMIT 1";
                break;

            case BX_WALL_VIEW_OUTLINE:
		        if($sWhereModuleFilter == '') {
					$aHidden = $this->_oConfig->getHandlersHidden(BX_WALL_VIEW_OUTLINE);
					$sWhereModuleFilter = "AND `th`.`outline`='1' AND `th`.`id` NOT IN ('" . implode("','", $aHidden) . "') ";
				}

				$sJoinClause = "LEFT JOIN `Profiles` AS `tp` ON `te`.`owner_id`=`tp`.`ID`";
				$sWhereClause .= "AND `tp`.`Status`='Active' ";
                $sWhereClause .= isset($aParams['filter']) ? $this->_getFilterAddon($aParams['owner_id'], $aParams['filter']) : '';
                $sWhereClause .= $sWhereModuleFilter;
                $sOrderClause = isset($aParams['order']) ? "ORDER BY `te`.`date` " . strtoupper($aParams['order']) : "";
                $sLimitClause = isset($aParams['count']) ? "LIMIT " . $aParams['start'] . ", " . $aParams['count'] : "";
                break;
        }

        $sSql = "SELECT
                `te`.`id` AS `id`,
                `te`.`owner_id` AS `owner_id`,
                `te`.`object_id` AS `object_id`,
                `te`.`type` AS `type`,
                `te`.`action` AS `action`,
                `te`.`content` AS `content`,
                `te`.`title` AS `title`,
                `te`.`description` AS `description`,
                `te`.`date` AS `date`,
                DATE_FORMAT(FROM_UNIXTIME(`te`.`date`), '" . $this->_oConfig->getDividerDateFormat() . "') AS `print_date`,
                DAYOFYEAR(FROM_UNIXTIME(`te`.`date`)) AS `days`,
                DAYOFYEAR(NOW()) AS `today`,
                (UNIX_TIMESTAMP() - `te`.`date`) AS `ago`,
                ROUND((UNIX_TIMESTAMP() - `te`.`date`)/86400) AS `ago_days`
            FROM `" . $this->_sPrefix . "events` AS `te`
            LEFT JOIN `" . $this->_sPrefix . "handlers` AS `th` ON `te`.`type`=`th`.`alert_unit` AND `te`.`action`=`th`.`alert_action` " . $sJoinClause . " 
            WHERE 1 " . $sWhereClause . " " . $sOrderClause . " " . $sLimitClause;

        $aEvents = array();
        $aEvent = $this->getFirstRow($sSql);
        while($aEvent) {
            $aEvent['content'] = str_replace("[ray_url]", $sHomeUrl, $aEvent['content']);
            $aEvent['ago'] = defineTimeInterval($aEvent['date']);
            $aEvents[] = $aEvent;

            $aEvent = $this->getNextRow();
        }

        return $aEvents;
    }

    function getEventsCount($iOwnerId, $sFilter, $sTimeline, $aModules)
    {
        $sWhereClause = "";
        if(!empty($iOwnerId)) {
            if(!is_array($iOwnerId))
                $sWhereClause = "`owner_id`='" . $iOwnerId . "' ";
            else
                $sWhereClause = "`owner_id` IN ('" . implode("','", $iOwnerId) . "') ";
        }

    	if(!empty($sTimeline) && strpos($sTimeline, BX_WALL_DIVIDER_TIMELINE) !== false) {
            list($iTLStart, $iTLEnd) = explode(BX_WALL_DIVIDER_TIMELINE, $sTimeline);

            $iNowMorning = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $iNowEvening = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
            $sWhereClause .= "AND `date`>='" . ($iNowMorning - 86400 * $iTLEnd) . "' AND `date`<='" . ($iNowEvening - 86400 * $iTLStart) . "' ";
        }

        if(!empty($aModules) && is_array($aModules))
        	$sWhereClause .= "AND `type` IN ('" . implode("','", $aModules) . "') ";

		$sWhereClause .= $this->_getFilterAddon($iOwnerId, $sFilter);

        $sSql = "SELECT COUNT(*) FROM `" . $this->_sPrefix . "events` WHERE " . $sWhereClause . " LIMIT 1";
        return $this->getOne($sSql);
    }

    function updateSimilarObject($iId, &$oAlert, $sDuration = 'day')
    {
        $sType = $oAlert->sUnit;
        $sAction = $oAlert->sAction;

        //Check handler
        $aHandler = $this->_oConfig->getHandlers($sType . '_' . $sAction);
        if(empty($aHandler) || !is_array($aHandler) || (int)$aHandler['groupable'] != 1)
            return false;

        //Check content's extra values
        if(isset($aHandler['group_by']) && !empty($aHandler['group_by']) && (!isset($oAlert->aExtras[$aHandler['group_by']]) || empty($oAlert->aExtras[$aHandler['group_by']])))
            return false;

        $sWhereClause = "";
        switch($sDuration) {
            case 'day':
                $iDayStart  = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                $iDayEnd  = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
                $sWhereClause .= "AND `date`>" . $iDayStart . " AND `date`<" . $iDayEnd . " ";
                break;
        }

        if(isset($aHandler['group_by']))
            $sWhereClause .= "AND `content` LIKE '%" . $oAlert->aExtras[$aHandler['group_by']] . "%' ";

        $sSql = "UPDATE `" . $this->_sPrefix . "events`
            SET
                `object_id`=CONCAT(`object_id`, '," . $oAlert->iObject . "'),
                `title`='',
                `description`='',
                `date`=UNIX_TIMESTAMP()
            WHERE
                `id`<>'" . $iId . "' AND
                `owner_id`='" . $oAlert->iSender . "' AND
                `type`='" . $sType . "' AND
                `action`='" . $sAction . "' " . $sWhereClause;
        $mixedResult = $this->query($sSql);

        if((int)$mixedResult > 0)
            $this->deleteEvent(array('id' => $iId));

        return $mixedResult;
    }

    //--- Comment Functions ---//
    function getCommentsCount($iId)
    {
        $sSql = "SELECT COUNT(`cmt_id`) FROM `" . $this->_sPrefix . "comments` WHERE `cmt_object_id`='" . $iId . "' AND `cmt_parent_id`='0' LIMIT 1";
        return (int)$this->getOne($sSql);
    }

    //--- Shared Media Functions ---//
    function getSharedCategory($sType, $iId)
    {
        $aType2Db = array(
            'sharedPhoto' => array('table' =>'bx_shared_photo_files', 'id' => 'medID'),
            'sharedMusic' => array('table' => 'RayMp3Files', 'id' => 'ID'),
            'sharedVideo' => array('table' => 'RayVideoFiles', 'id' => 'ID')
        );

        $sSql = "SELECT `Categories` FROM `" . $aType2Db[$sType]['table'] . "` WHERE `" . $aType2Db[$sType]['id'] . "`='" . $iId . "' LIMIT 1";
        return $this->getOne($sSql);
    }

    //--- Private functions ---//
    function _getFilterAddon($iOwnerId, $sFilter)
    {
        switch($sFilter) {
            case BX_WALL_FILTER_OWNER:
                $sFilterAddon = " AND `te`.`action`='' AND `te`.`object_id`='" . $iOwnerId . "' ";
                break;
            case BX_WALL_FILTER_OTHER:
                $sFilterAddon = " AND `te`.`action`='' AND `te`.`object_id`<>'" . $iOwnerId . "' ";
                break;
            case BX_WALL_FILTER_ALL:
            default:
                $sFilterAddon = "";
        }
        return $sFilterAddon;
    }
}
