<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextDb');

class BxFdbDb extends BxDolTextDb
{
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);
    }
    function getEntries($aParams = array())
    {
        switch($aParams['sample_type']) {
            case 'id':
                $sMethod = 'getRow';
                $sSelectClause = "`te`.`content` AS `content`, ";
                $sWhereClause = " AND `te`.`id`='" . $aParams['id'] . "'";
                $sOrderClause = "`te`.`date` DESC";
                $sLimitClause = "LIMIT 1";
                break;
            case 'uri':
                $sMethod = 'getRow';
                $sSelectClause = "`te`.`content` AS `content`, ";
                $sWhereClause = " AND `te`.`uri`='" . $aParams['uri'] . "'";
                $sOrderClause = "`te`.`date` DESC";
                $sLimitClause = "LIMIT 1";
                break;
            case 'view':
                $sMethod = 'getAll';
                $sSelectClause = "`te`.`content` AS `content`, ";
                $sWhereClause = " AND `te`.`uri`='" . $aParams['uri'] . "' AND `te`.`status`='" . BX_TD_STATUS_ACTIVE . "'";
                $sOrderClause = "`te`.`date` DESC";
                $sLimitClause = "LIMIT 1";
                break;
            case 'search_unit':
                $sMethod = 'getAll';
                $sSelectClause = "SUBSTRING(`te`.`content`, 1, " . $this->_oConfig->getSnippetLength() . ") AS `content`, ";
                $sWhereClause = " AND `te`.`uri`='" . $aParams['uri'] . "'";
                $sOrderClause = "`te`.`date` DESC";
                $sLimitClause = "LIMIT 1";
                break;
            case 'archive':
                $sMethod = 'getAll';
                $sSelectClause = "SUBSTRING(`te`.`content`, 1, " . $this->_oConfig->getSnippetLength() . ") AS `content`, ";
                $sWhereClause = " AND `te`.`status`='" . BX_TD_STATUS_ACTIVE . "'";
                $sOrderClause = "`te`.`date` DESC";
                $sLimitClause = "LIMIT " . $aParams['start'] . ', ' . $aParams['count'];
                break;
            case 'owner':
                $sMethod = 'getAll';
                $sSelectClause = "SUBSTRING(`te`.`content`, 1, " . $this->_oConfig->getSnippetLength() . ") AS `content`, ";
                $sWhereClause = " AND `te`.`author_id`='" . $aParams['sample_params']['owner_id'] . "'";
                $sOrderClause = "`te`.`date` DESC";
                $sLimitClause = "LIMIT " . $aParams['start'] . ', ' . $aParams['count'];
                break;
            case 'admin':
                $sMethod = 'getAll';
                $sSelectClause = "SUBSTRING(`te`.`content`, 1, " . $this->_oConfig->getSnippetLength() . ") AS `content`, ";
                $sWhereClause = !empty($aParams['filter_value']) ? " AND (`tp`.`NickName` LIKE '%" . $aParams['filter_value'] . "%' OR `te`.`caption` LIKE '%" . $aParams['filter_value'] . "%' OR `te`.`content` LIKE '%" . $aParams['filter_value'] . "%' OR `te`.`tags` LIKE '%" . $aParams['filter_value'] . "%')" : "";
                $sOrderClause = "`te`.`date` DESC";
                $sLimitClause = "LIMIT " . $aParams['start'] . ', ' . $aParams['count'];
                break;
            case 'all':
                $sMethod = 'getAll';
                $sSelectClause = "SUBSTRING(`te`.`content`, 1, " . $this->_oConfig->getSnippetLength() . ") AS `content`, ";
                $sWhereClause = " AND `te`.`status`='" . BX_TD_STATUS_ACTIVE . "'";
                $sOrderClause = "`te`.`date` DESC";
                break;
        }
        $sSql = "SELECT
                   " . $sSelectClause . "
                   `te`.`id` AS `id`,
                   `tp`.`ID` AS `author_id`,
                   `tp`.`NickName` AS `author_username`,
                   `te`.`caption` AS `caption`,
                   `te`.`tags` AS `tags`,
                   `te`.`uri` AS `uri`,
                   `te`.`allow_comment_to` AS `allow_comment_to`,
                   `te`.`allow_vote_to` AS `allow_vote_to`,
                   `te`.`date` AS `date`,
                   DATE_FORMAT(FROM_UNIXTIME(`te`.`date`), '" . $this->_oConfig->getDateFormat() . "') AS `date_uf`,
                   UNIX_TIMESTAMP() - `te`.`date` AS `ago`,
                   `te`.`status` AS `status`
                FROM `" . $this->_sPrefix . "entries` AS `te`
                LEFT JOIN `Profiles` AS `tp` ON `te`.`author_id`=`tp`.`ID`
                WHERE 1 " . $sWhereClause . "
                ORDER BY " . $sOrderClause . " " . $sLimitClause;

        $aResult = $this->$sMethod($sSql);

        if(!in_array($aParams['sample_type'], array('id', 'uri', 'view')))
           for($i = 0; $i < count($aResult); $i++)
               $aResult[$i]['content'] = strip_tags($aResult[$i]['content']);

        return $aResult;
    }

    function getCount($aParams = array())
    {
        if(!isset($aParams['sample_type']))
            $aParams['sample_type'] = '';

        switch($aParams['sample_type']) {
            case 'owner':
                $sWhereClause = "`author_id`='" . $aParams['sample_params']['owner_id'] . "' AND `status`='" . BX_TD_STATUS_ACTIVE . "'";
                break;
            case 'admin':
                $sWhereClause = !empty($aParams['filter_value']) ? "(`caption` LIKE '%" . $aParams['filter_value'] . "%' OR `content` LIKE '%" . $aParams['filter_value'] . "%' OR `tags` LIKE '%" . $aParams['filter_value'] . "%')" : "1";
                break;
            default:
                $sWhereClause = "`status`='" . BX_TD_STATUS_ACTIVE . "'";
                break;
        }
        $sSql = "SELECT COUNT(`id`) FROM `" . $this->_sPrefix . "entries` WHERE " . $sWhereClause . " LIMIT 1";
        return (int)$this->getOne($sSql);
    }
}
