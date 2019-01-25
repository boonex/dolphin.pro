<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

/**
 * @see BxDolCmts
 */
class BxDolCmtsQuery extends BxDolDb
{
    var $_aSystem; // current voting system
    var $_sTable;
    var $_sTableTrack;

    function __construct(&$aSystem)
    {
        $this->_aSystem = &$aSystem;
        $this->_sTable = $this->_aSystem['table_cmts'];
        $this->_sTableTrack = $this->_aSystem['table_track'];
        parent::__construct();
    }

    function getTableName ()
    {
        return $this->_sTable;
    }

    function getComments ($iId, $iCmtParentId = 0, $iAuthorId = 0, $sCmtOrder = 'ASC', $iStart = 0, $iCount = -1)
    {
        global $sHomeUrl;
        $iTimestamp = time();
        $sFields = "'' AS `cmt_rated`,";
        $sJoin = '';
        if ($iAuthorId) {
            $sFields = '`r`.`cmt_rate` AS `cmt_rated`,';
            $sJoin = "LEFT JOIN {$this->_sTableTrack} AS `r` ON (`r`.`cmt_system_id` = ".$this->_aSystem['system_id']." AND `r`.`cmt_id` = `c`.`cmt_id` AND `r`.`cmt_rate_author_id` = $iAuthorId)";
        }

        $aCmts = $this->getAll("SELECT
                $sFields
                `c`.`cmt_id`,
                `c`.`cmt_parent_id`,
                `c`.`cmt_object_id`,
                `c`.`cmt_author_id`,
                `c`.`cmt_text`,
                `c`.`cmt_mood`,
                `c`.`cmt_rate`,
                `c`.`cmt_rate_count`,
                `c`.`cmt_replies`,
                UNIX_TIMESTAMP(`c`.`cmt_time`) AS `cmt_time_ts`,
                ($iTimestamp - UNIX_TIMESTAMP(`c`.`cmt_time`)) AS `cmt_secs_ago`,
                `p`.`NickName` AS `cmt_author_name`
            FROM {$this->_sTable} AS `c`
            LEFT JOIN `Profiles` AS `p` ON (`p`.`ID` = `c`.`cmt_author_id`)
            $sJoin
            WHERE `c`.`cmt_object_id` = ? AND `c`.`cmt_parent_id` = ?
            ORDER BY `c`.`cmt_time` " . (strtoupper($sCmtOrder) == 'ASC' ? 'ASC' : 'DESC') . ($iCount != -1 ? ' LIMIT ' . $iStart . ', ' . $iCount : ''),
            [
                $iId,
                $iCmtParentId
            ]
        );

        //LEFT JOIN `media` AS `m` ON (`m`.`med_id` = `p`.`Avatar` AND `m`.`med_status` = 'active')

        foreach($aCmts as $k => $aCmt) {
            $aCmts[$k]['cmt_text'] = str_replace("[ray_url]", $sHomeUrl, $aCmt['cmt_text']);
            $aCmts[$k]['cmt_ago'] = defineTimeInterval($aCmt['cmt_time_ts']);
        }

        return $aCmts;
    }

    function getComment ($iId, $iCmtId, $iAuthorId = 0)
    {
        global $sHomeUrl;

        $iTimestamp = time();
        $sFields = "'' AS `cmt_rated`,";
        $sJoin = '';
        if ($iAuthorId) {
            $sFields = '`r`.`cmt_rate` AS `cmt_rated`,';
            $sJoin = "LEFT JOIN {$this->_sTableTrack} AS `r` ON (`r`.`cmt_system_id` = ".$this->_aSystem['system_id']." AND `r`.`cmt_id` = `c`.`cmt_id` AND `r`.`cmt_rate_author_id` = $iAuthorId)";
        }
        $aComment = $this->getRow("SELECT
                $sFields
                `c`.`cmt_id`,
                `c`.`cmt_parent_id`,
                `c`.`cmt_object_id`,
                `c`.`cmt_author_id`,
                `c`.`cmt_text`,
                `c`.`cmt_mood`,
                `c`.`cmt_rate`,
                `c`.`cmt_rate_count`,
                `c`.`cmt_replies`,
                UNIX_TIMESTAMP(`c`.`cmt_time`) AS `cmt_time_ts`,
                ($iTimestamp - UNIX_TIMESTAMP(`c`.`cmt_time`)) AS `cmt_secs_ago`,
                `p`.`NickName` AS `cmt_author_name`
            FROM {$this->_sTable} AS `c`
            LEFT JOIN `Profiles` AS `p` ON (`p`.`ID` = `c`.`cmt_author_id`)
            $sJoin
            WHERE `c`.`cmt_object_id` = ? AND `c`.`cmt_id` = ?
            LIMIT 1", [$iId, $iCmtId]);

		if(!empty($aComment) && is_array($aComment)) {
	        $aComment['cmt_text'] = str_replace("[ray_url]", $sHomeUrl, $aComment['cmt_text']);
	        $aComment['cmt_ago'] = defineTimeInterval($aComment['cmt_time_ts']);
		}

        return $aComment;
    }

    function getCommentSimple ($iId, $iCmtId)
    {
        $iTimestamp = time();
        return $this->getRow("
            SELECT
                *, ($iTimestamp - UNIX_TIMESTAMP(`c`.`cmt_time`)) AS `cmt_secs_ago`
            FROM {$this->_sTable} AS `c`
            WHERE `cmt_object_id` = ? AND `cmt_id` = ?
            LIMIT 1", [$iId, $iCmtId]);
    }

    function addComment ($iId, $iCmtParentId, $iAuthorId, $sText, $iMood)
    {
        if (!$this->query("INSERT INTO {$this->_sTable} SET
            `cmt_parent_id` = '$iCmtParentId',
            `cmt_object_id` = '$iId',
            `cmt_author_id` = '$iAuthorId',
            `cmt_text` = '$sText',
            `cmt_mood` = '$iMood',
            `cmt_time` = NOW()"))
        {
            return false;
        }

        $iRet = $this->lastId();

        if ($iCmtParentId)
            $this->query ("UPDATE {$this->_sTable} SET `cmt_replies` = `cmt_replies` + 1 WHERE `cmt_id` = '$iCmtParentId' LIMIT 1");

        return $iRet;
    }

    function removeComment ($iId, $iCmtId, $iCmtParentId)
    {
        if (!$this->query("DELETE FROM {$this->_sTable} WHERE `cmt_object_id` = '$iId' AND `cmt_id` = '$iCmtId' LIMIT 1"))
            return false;

        $this->query ("UPDATE {$this->_sTable} SET `cmt_replies` = `cmt_replies` - 1 WHERE `cmt_id` = '$iCmtParentId' LIMIT 1");

        return true;
    }

    function updateComment ($iId, $iCmtId, $sText, $iMood)
    {
        return $this->query("UPDATE {$this->_sTable} SET `cmt_text` = '$sText', `cmt_mood` = '$iMood'  WHERE `cmt_object_id` = '$iId' AND `cmt_id` = '$iCmtId' LIMIT 1");
    }

    function rateComment ($iSystemId, $iCmtId, $iRate, $iAuthorId, $sAuthorIp)
    {
        $iTimestamp = time();
        if ($this->query("INSERT IGNORE INTO {$this->_sTableTrack} SET
            `cmt_system_id` = '$iSystemId',
            `cmt_id` = '$iCmtId',
            `cmt_rate` = '$iRate',
            `cmt_rate_author_id` = '$iAuthorId',
            `cmt_rate_author_nip` = INET_ATON('$sAuthorIp'),
            `cmt_rate_ts` = $iTimestamp"))
        {
            $this->query("UPDATE {$this->_sTable} SET `cmt_rate` = `cmt_rate` + $iRate, `cmt_rate_count` = `cmt_rate_count` + 1 WHERE `cmt_id` = '$iCmtId' LIMIT 1");
            return true;
        }

        return false;
    }

    function deleteAuthorComments ($iAuthorId)
    {
        $aObjectsIds = array();
        $isDelOccured = 0;
        $a = $this->getAll ("SELECT `cmt_id`, `cmt_parent_id`, `cmt_object_id` FROM {$this->_sTable} WHERE `cmt_author_id` = ? AND `cmt_replies` = 0", [$iAuthorId]);
        foreach ($a as $r) {
            $this->query ("DELETE FROM {$this->_sTable} WHERE `cmt_id` = '{$r['cmt_id']}'");
            $this->query ("UPDATE {$this->_sTable} SET `cmt_replies` = `cmt_replies` - 1 WHERE `cmt_id` = '{$r['cmt_parent_id']}'");
            $aObjectsIds[$r['cmt_object_id']] = $r['cmt_object_id'];
            $isDelOccured = 1;
        }

        $this->query ("UPDATE {$this->_sTable} SET `cmt_author_id` = 0 WHERE `cmt_author_id` = '$iAuthorId' AND `cmt_replies` != 0");

        if ($isDelOccured) {
            foreach ($aObjectsIds as $iObjectId) {
                $iCount = $this->getObjectCommentsCount ($iObjectId);
                $this->updateTriggerTable($iObjectId, $iCount);
            }
            $this->query ("OPTIMIZE TABLE {$this->_sTable}");
        }
    }

    function deleteObjectComments ($iObjectId)
    {
        $this->query ("DELETE FROM {$this->_sTable} WHERE `cmt_object_id` = '$iObjectId'");
        $this->query ("OPTIMIZE TABLE {$this->_sTable}");
    }

    function getObjectCommentsCount ($iObjectId, $iParentId = -1)
    {
        return $this->getOne ("SELECT COUNT(*) FROM `" . $this->_sTable ."` WHERE `cmt_object_id`='" . $iObjectId . "'" . ($iParentId != -1 ? " AND `cmt_parent_id`='" . $iParentId . "'" : ""));
    }

    function updateTriggerTable($iId, $iCount)
    {
        if (empty($this->_aSystem['trigger_table']))
            return true;
        return $this->query("UPDATE `{$this->_aSystem['trigger_table']}` SET `{$this->_aSystem['trigger_field_comments']}` = '$iCount' WHERE `{$this->_aSystem['trigger_field_id']}` = '$iId' LIMIT 1");
    }

    function maintenance()
    {
        $iTimestamp = time();
        $iDeletedRecords = $this->query("DELETE FROM {$this->_sTableTrack} WHERE `cmt_rate_ts` < ($iTimestamp - " . (int)BX_OLD_CMT_VOTES . ")");
        if ($iDeletedRecords)
            $this->query("OPTIMIZE TABLE {$this->_sTableTrack}");
        return $iDeletedRecords;
    }
}
