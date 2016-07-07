<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolDb.php' );

/**
 * @see BxDolVoting
 */
class BxDolVotingQuery extends BxDolDb
{
    var $_aSystem; // current voting system

    function __construct(&$aSystem)
    {
        $this->_aSystem = &$aSystem;
        parent::__construct();
    }

    function  getVote ($iId)
    {
        $sPre = $this->_aSystem['row_prefix'];
        $sTable = $this->_aSystem['table_rating'];

        return $this->getRow("SELECT `{$sPre}rating_count` as `count`, (`{$sPre}rating_sum` / `{$sPre}rating_count`) AS `rate` FROM {$sTable} WHERE `{$sPre}id` = ? LIMIT 1", [$iId]);
    }

    function  putVote ($iId, $sIp, $iRate)
    {
        $sPre = $this->_aSystem['row_prefix'];

        $sTable = $this->_aSystem['table_rating'];

        if ($this->getOne("SELECT `{$sPre}id` FROM $sTable WHERE `{$sPre}id` = '$iId' LIMIT 1")) {
            $ret = $this->query ("UPDATE {$sTable} 	SET `{$sPre}rating_count` = `{$sPre}rating_count` + 1, `{$sPre}rating_sum` = `{$sPre}rating_sum` + '$iRate' WHERE `{$sPre}id` = '$iId'");
        } else {
            $ret = $this->query ("INSERT INTO {$sTable} SET `{$sPre}id` = '$iId', `{$sPre}rating_count` = '1', `{$sPre}rating_sum` = '$iRate'");

        }
        if (!$ret) return $ret;

        $sTable = $this->_aSystem['table_track'];
        return $this->query ("INSERT INTO {$sTable} SET `{$sPre}id` = '$iId', `{$sPre}ip` = '$sIp', `{$sPre}date` = NOW()");
    }

    function isDublicateVote ($iId, $sIp)
    {
        $sPre = $this->_aSystem['row_prefix'];
        $sTable = $this->_aSystem['table_track'];
        $iSec = $this->_aSystem['is_duplicate'];

        return $this->getOne ("SELECT `{$sPre}id` FROM {$sTable} WHERE `{$sPre}ip` = '$sIp' AND `{$sPre}id` = '$iId' AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`{$sPre}date`) < $iSec");

    }

    function getSqlParts ($sMailTable, $sMailField)
    {
        if ($sMailTable)
            $sMailTable .= '.';

        if ($sMailField)
            $sMailField = $sMailTable.$sMailField;

        $sPre = $this->_aSystem['row_prefix'];
        $sTable = $this->_aSystem['table_rating'];

        return array (
            'fields' => ",$sTable.`{$sPre}rating_count` as `voting_count`, ($sTable.`{$sPre}rating_sum` / $sTable.`{$sPre}rating_count`) AS `voting_rate` ",
            //'fields' => ",34 as `voting_count`, 2.5 AS `voting_rate` ",
            'join' => " LEFT JOIN $sTable ON ({$sTable}.`{$sPre}id` = $sMailField) "
        );
    }

    function deleteVotings ($iId)
    {
        $sPre = $this->_aSystem['row_prefix'];

        $sTable = $this->_aSystem['table_track'];
        $this->query ("DELETE FROM {$sTable} WHERE `{$sPre}id` = '$iId'");

        $sTable = $this->_aSystem['table_rating'];
        return $this->query ("DELETE FROM {$sTable} WHERE `{$sPre}id` = '$iId'");
    }

    function getTopVotedItem ($iDays, $sJoinTable = '', $sJoinField = '', $sWhere = '')
    {
        $sPre = $this->_aSystem['row_prefix'];
        $sTable = $this->_aSystem['table_track'];

        $sJoin = $sJoinTable && $sJoinField ? " INNER JOIN $sJoinTable ON ({$sJoinTable}.{$sJoinField} = $sTable.`{$sPre}id`) " : '';

        return $this->getOne ("SELECT $sTable.`{$sPre}id`, COUNT($sTable.`{$sPre}id`) AS `voting_count` FROM {$sTable} $sJoin WHERE TO_DAYS(NOW()) - TO_DAYS($sTable.`{$sPre}date`) <= $iDays $sWhere GROUP BY $sTable.`{$sPre}id` HAVING `voting_count` > 2 ORDER BY `voting_count` DESC LIMIT 1");
    }

    function getVotedItems ($sIp)
    {
        $sPre = $this->_aSystem['row_prefix'];
        $sTable = $this->_aSystem['table_track'];
        $iSec = $this->_aSystem['is_duplicate'];
        return $this->getAll ("SELECT `{$sPre}id` FROM {$sTable} WHERE `{$sPre}ip` = '$sIp' AND UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`{$sPre}date`) < $iSec ORDER BY `{$sPre}date` DESC");
    }

    function updateTriggerTable($iId, $fRate, $iCount)
    {
        return $this->query("UPDATE `{$this->_aSystem['trigger_table']}` SET `{$this->_aSystem['trigger_field_rate']}` = '$fRate', `{$this->_aSystem['trigger_field_rate_count']}` = '$iCount' WHERE `{$this->_aSystem['trigger_field_id']}` = '$iId'");
    }
}
