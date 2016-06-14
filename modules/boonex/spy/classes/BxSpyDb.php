<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

    class BxSpyDb extends BxDolModuleDb
    {
        var $_oConfig;
        var $sTablePrefix;

        /**
         * Constructor.
         */
        function __construct(&$oConfig)
        {
            parent::__construct();

            $this -> _oConfig = $oConfig;
            $this -> sTablePrefix = $oConfig -> getDbPrefix();
        }

        /**
         * Function will get all internal spy's handlers;
         *
         * @return : (array);
         */
        function getInternalHandlers()
        {
            $sQuery = "SELECT * FROM `{$this->sTablePrefix}handlers`";
            return $this -> getAll($sQuery);
        }

        /**
         * Function will return number of all events;
         *
         * @param  : $sType (string) - type of activity;
         * @return : (integer);
         */
        function getActivityCount($sType = '')
        {
            $sType = process_db_input($sType, BX_TAGS_STRIP);

            $sWhere = '';
            if($sType && $sType != 'all'){
                $sWhere = "WHERE `type` = '{$sType}'";
            }

            $sQuery = "SELECT COUNT(*) FROM `{$this->sTablePrefix}data` {$sWhere}";
            !($iCount = $this -> getOne($sQuery) ) ? $iCount = 0 : null;

            return $iCount;
        }

        /**
         * Function will get the latest event's Id;
         *
         * @param  : $sType (string) - type of activity;
         * @return : (integer);
         */
        function getLastActivityId($sType = '')
        {
            $sType   = process_db_input($sType, BX_TAGS_STRIP);
            $sWhere  = '';

            if($sType && $sType != 'all'){
                $sWhere = "WHERE `type` = '{$sType}'";
            }

            $sQuery = "SELECT `id` FROM `{$this->sTablePrefix}data` {$sWhere} ORDER BY `id` DESC LIMIT 1";
            !($iLastEventId = $this -> getOne($sQuery) ) ? $iLastEventId = 0 : null;

            return $iLastEventId;
        }

        /**
         * Function will get the latest friends event's Id;
         *
         * @param  : $sType (string) - type of activity;
         * @param  : $iProfile (integer) - profile's id;
         * @return : (integer);
         */
        function getLastFriendsActivityId($iProfileId, $sType = '')
        {
            $iProfileId = (int) $iProfileId;
            $sType   	= process_db_input($sType, BX_TAGS_STRIP);
            $sWhere 	= '';

            if($sType && $sType != 'all'){
                $sWhere = " AND `bx_spy_data`.`type` = '{$sType}'";
            }

            $sQuery =
            "
                SELECT
                    `bx_spy_data`.`id`
                FROM
                    `bx_spy_data`
                INNER JOIN
                    `bx_spy_friends_data`
                ON
                    `bx_spy_friends_data`.`event_id` = `bx_spy_data`.`id`
                WHERE
                    `bx_spy_friends_data`.`friend_id` = {$iProfileId}
                        AND
                    `bx_spy_data`.`sender_id` <> {$iProfileId}
                        {$sWhere}
                ORDER BY
                    `bx_spy_data`.`id` DESC LIMIT 1
            ";

            !($iLastEventId = $this -> getOne($sQuery) ) ? $iLastEventId = 0 : null;
            return $iLastEventId;
        }

        /**
         * Function will return number of all friends events;
         *
         * @param  : $sType (string) - type of activity;
         * @param  : $iProfile (integer) - profile's id;
         * @return : (integer);
         */
        function getFriendsActivityCount($iProfileId, $sType = '')
        {
            $iProfileId = (int) $iProfileId;
               $sType   	= process_db_input($sType, BX_TAGS_STRIP);
            $sWhere		= '';

            if($sType && $sType != 'all'){
                $sWhere = " AND `bx_spy_data`.`type` = '{$sType}'";
            }

            $sQuery =
            "
                SELECT
                    COUNT(`bx_spy_data`.`id`)
                FROM
                    `bx_spy_data`
                INNER JOIN
                    `bx_spy_friends_data`
                ON
                    `bx_spy_friends_data`.`event_id` = `bx_spy_data`.`id`
                WHERE
                    `bx_spy_friends_data`.`friend_id` = {$iProfileId}
                        AND
                    `bx_spy_data`.`sender_id` <> {$iProfileId}
                        {$sWhere}
            ";

            !($iCount = $this -> getOne($sQuery) ) ? $iCount = 0 : null;
            return $iCount;
        }

        /**
         * Function will return global category number;
         *
         * @return : (integer) - category's number;
         */
        function getSettingsCategory($sValueName)
        {
            $sValueName = process_db_input($sValueName, BX_TAGS_STRIP);
            return $this -> getOne('SELECT `kateg` FROM `sys_options` WHERE `Name` = "' . $sValueName . '"');
        }

        /**
         * Function will set activiti as viwed;
         *
         * @param  : $iActivityId (integer) - activity's id;
         * @return : void;
         */
        function setViewed($iActivityId)
        {
            $iActivityId = (int) $iActivityId;
            $sQuery = "UPDATE `{$this->sTablePrefix}data` SET `viewed` = 1";
            $this -> query($sQuery);
        }

        /**
         * Function will set all profile's activiti as viwed;
         *
         * @param  : $iProfileId (integer) - profile's id;
         * @return : void;
         */
        function setViewedProfileActivity($iProfileId)
        {
            $iProfileId = (int) $iProfileId;
            $sQuery = "UPDATE `{$this->sTablePrefix}data` SET `viewed` = 1 WHERE `recipient_id` = {$iProfileId}";
            $this -> query($sQuery);
        }

        function insertData(&$aData)
        {
            //--- Update Spy Handlers ---//
            foreach($aData['handlers'] as $aHandler) {
                $aHandler['alert_unit'] 	= process_db_input($aHandler['alert_unit'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['alert_action'] 	= process_db_input($aHandler['alert_action'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['module_uri'] 	= process_db_input($aHandler['module_uri'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['module_class'] 	= process_db_input($aHandler['module_class'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['module_method'] 	= process_db_input($aHandler['module_method'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);

                $sQuery =
                "
                    INSERT INTO
                        `{$this->sTablePrefix}handlers`
                    SET
                        `alert_unit`    = '{$aHandler['alert_unit']}',
                        `alert_action`  = '{$aHandler['alert_action']}',
                        `module_uri`    = '{$aHandler['module_uri']}',
                        `module_class`  = '{$aHandler['module_class']}',
                        `module_method` = '{$aHandler['module_method']}'
                ";

                $this -> query($sQuery);
            }

            $sAlertName = $this -> _oConfig -> getAlertSystemName();

            //--- Update System Alerts ---//
            $sQuery =
            "
                SELECT
                    `id`
                FROM
                    `sys_alerts_handlers`
                WHERE
                   `name`= ?
                LIMIT 1
            ";

            $iHandlerId = (int) $this -> getOne($sQuery, [$sAlertName]);

            foreach($aData['alerts'] as $aAlert) {
                $aAlert['unit']		= process_db_input($aAlert['unit'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aAlert['action']	= process_db_input($aAlert['action'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);

                $sQuery =
                "
                    INSERT INTO
                        `sys_alerts`
                    SET
                       `unit`       = '{$aAlert['unit']}',
                       `action`     = '{$aAlert['action']}',
                       `handler_id` = '{$iHandlerId}'
                ";

                $this -> query($sQuery);
            }
        }

        function deleteData(&$aData)
        {
            //--- Update Wall Handlers ---//
            foreach($aData['handlers'] as $aHandler) {
                $aHandler['alert_unit'] 	= process_db_input($aHandler['alert_unit'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['alert_action'] 	= process_db_input($aHandler['alert_action'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['module_uri'] 	= process_db_input($aHandler['module_uri'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['module_class'] 	= process_db_input($aHandler['module_class'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aHandler['module_method'] 	= process_db_input($aHandler['module_method'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);

                $sQuery =
                "
                    DELETE FROM
                        `{$this->sTablePrefix}handlers`
                    WHERE
                        `alert_unit`    = '{$aHandler['alert_unit']}'
                            AND
                        `alert_action`  = '{$aHandler['alert_action']}'
                            AND
                        `module_uri`    = '{$aHandler['module_uri']}'
                            AND
                        `module_class`  = '{$aHandler['module_class']}'
                            AND
                        `module_method` = '{$aHandler['module_method']}'
                    LIMIT 1
                ";

                $this -> query($sQuery);
            }

            // define system alert name;
            $sAlertName = $this -> _oConfig -> getAlertSystemName();

            //--- Update System Alerts ---//
            $sQuery =
            "
                SELECT
                    `id`
                FROM
                    `sys_alerts_handlers`
                WHERE
                   `name`= ?
                LIMIT 1
            ";

            $iHandlerId = (int) $this -> getOne($sQuery, [$sAlertName]);
            foreach($aData['alerts'] as $aAlert) {
                $aAlert['unit']		= process_db_input($aAlert['unit'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);
                $aAlert['action']	= process_db_input($aAlert['action'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);

                $sQuery =
                "
                    DELETE FROM
                        `sys_alerts`
                    WHERE
                        `unit`       = '{$aAlert['unit']}'
                            AND
                        `action`     = '{$aAlert['action']}'
                            AND
                        `handler_id` = '{$iHandlerId}'
                    LIMIT 1
                ";

                $this -> query($sQuery);
            }
        }

        /**
         * Function will create new activity;
         *
         * @param  : $iSenderId (integer) - activity's sender id;
         * @param  : $iRecipientId (integer) - activity's recipient id;
         * @param  : $aActivityInfo (array) - with some event's information;
                        [ lang_key ] - (string) language key;
                        [ params ]   - (array)  some nedded parameters;
                        [ type   ]   - (string) type of activity;
         * @return : (integer) created event's Id;
         */
        function createActivity($sAlertUnit, $sAlertAction, $iObjectId, $iCommentId, $iSenderId, $iRecipientId, $aActivityInfo)
        {
            $iSenderId = (int) $iSenderId;
            $iRecipientId = (int) $iRecipientId;

            // procces recived parameters
            $aParameters = isset($aActivityInfo['params']) ?  process_db_input(serialize($aActivityInfo['params']), BX_TAGS_STRIP, BX_SLASHES_NO_ACTION) : '';
            $sActivityType = isset($aActivityInfo['spy_type']) ? process_db_input($aActivityInfo['spy_type'],BX_TAGS_STRIP, BX_SLASHES_NO_ACTION) : 'content_activity';
            $sLangKey = process_db_input($aActivityInfo['lang_key'], BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);

            // execute query;
            $sQuery =
            "
                INSERT INTO
                    `{$this->sTablePrefix}data`
                SET
                    `alert_unit`	= '{$sAlertUnit}',
                    `alert_action`	= '{$sAlertAction}',
                    `object_id`		= {$iObjectId},
                    `comment_id`	= {$iCommentId},
                    `sender_id`     = {$iSenderId},
                    `recipient_id`  = {$iRecipientId},
                    `lang_key`      = '{$sLangKey}',
                    `params`        = '{$aParameters}',
                    `date`          = TIMESTAMP( NOW() ),
                    `type`          = '{$sActivityType}'
            ";

            $this -> query($sQuery);
            return $this -> lastId();
        }

        function deleteActivityByObject($sUnit, $iObjectId, $iCommentId = 0)
        {
            $sWhereAddon = "";
            if($iCommentId != 0)
                $sWhereAddon = "AND `comment_id`='" . $iCommentId . "'";

            $sSql = "DELETE FROM
                    `" . $this->sTablePrefix . "data`
                WHERE
                    `alert_unit`='" . $sUnit . "' AND
                    `object_id`='" . $iObjectId . "' " . $sWhereAddon;

            return $this->query($sSql);
        }

        function deleteActivityByUser($iUserId)
        {
            $sSql = "DELETE FROM
                    `" . $this->sTablePrefix . "data`
                WHERE
                    `sender_id`='" . $iUserId . "' OR
                    `recipient_id`='" . $iUserId . "'";

            return $this->query($sSql);
        }

        /**
         * Function will attach created event to their friend ;
         *
         * @param  : $iEventId  (integer) - event's  Id;
         * @param  : $iSenderId (integer) - sender's Id;
         * @param  : $iFriendId (integer) - friend's Id;
         * @return : void;
         */
        function attachFriendEvent($iEventId, $iSenderId, $iFriendId)
        {
            $iEventId  = (int) $iEventId;
            $iSenderId = (int) $iSenderId;
            $iFriendId = (int) $iFriendId;

            $sQuery =
            "
                INSERT INTO
                    `{$this->sTablePrefix}friends_data`
                SET
                    `event_id`  = {$iEventId},
                    `sender_id` = {$iSenderId},
                    `friend_id` = {$iFriendId}
            ";

            $this -> query($sQuery);
        }

        /**
         * Function will delete all unnecessary events;
         *
         * @param  : $iCount (integer) - number of rows that need to delete;
         * @return : void;
         */
        function deleteUselessData($iDays = 0)
        {
            $iDays = (int) $iDays;
            if ($iDays < 1) {
                return 0;
            }

            $iAffectedRows = $this -> query("DELETE FROM `{$this->sTablePrefix}data` WHERE `{$this->sTablePrefix}data`.`date` < DATE_SUB(NOW(), INTERVAL $iDays DAY)");
            $this -> query("OPTIMIZE TABLE `{$this->sTablePrefix}data`");

            $this -> query("DELETE `{$this->sTablePrefix}friends_data` FROM `{$this->sTablePrefix}friends_data` LEFT JOIN `{$this->sTablePrefix}data` ON (`{$this->sTablePrefix}data`.`id` =  `{$this->sTablePrefix}friends_data`.`event_id`) WHERE `{$this->sTablePrefix}data`.`id` IS NULL");
            $this -> query("OPTIMIZE TABLE `{$this->sTablePrefix}friends_data`");

            return $iAffectedRows;
        }
    }
