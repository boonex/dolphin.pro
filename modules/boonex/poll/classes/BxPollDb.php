<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolModuleDb.php' );

class BxPollDb extends BxDolModuleDb
{
    var $_oConfig;
    var $_sTable;
    var $sTablePrefix;

    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct();

        $this -> _oConfig       = $oConfig;
        $this -> _sTable        = $oConfig -> sTableName;
        $this -> sTablePrefix   = $oConfig -> sTablePrefix;
    }

    /**
     * Function will return all active pools ;
     *
     * @param  : $sLimit    (string) - contain part of SQL query;
     * @param  : $iMemberID (integer) - logged member's Id ;
     * @param  : $bAllPolls (boolean) - return all polls aprroved and not ;
     * @return : (array) - array with all active pools;
                    [ id_poll ]     - (integer) poll's Id;
                    [ id_profile ]  - (integer) poll's owner Id;
                    [ poll_date ]    - (string)  poll's date creation;
                    [ sec ]         - (integer) poll's date creation in seconds;
                    [ poll_approval ] - (integer) poll's status aproved or not;
     */
    function getAllPolls($sLimit, $iMemberID = 0, $bAllPolls = false, $iApproval = 1, $sExtraSql = null, $sOrder = '`poll_date` DESC')
    {
        $sLimit = $this -> escape($sLimit, false);
        $iMemberID = (int) $iMemberID;
        $iApproval = (int) $iApproval;
        $sExtraSql = $this -> escape($sExtraSql, false);
        $sOrder	= $this -> escape($sOrder, false);

        // ** init some needed variables ;

        $sAddonSql       = null;
        $aWhereCondition = array();

        $aWhereCondition[0] = " AND `poll_status`   = 'active' ";
        $aWhereCondition[1] = " AND `poll_approval` = {$iApproval}";
        $aWhereCondition[2] = " AND `id_profile`    = {$iMemberID} ";

        // if need the only member's pools ;
        if ( !$bAllPolls ) {
            if ( $iMemberID  ) {
                $sAddonSql  = $aWhereCondition[1] . $aWhereCondition[2];
            } else {
                $sAddonSql  = $aWhereCondition[0] . $aWhereCondition[1];
            }
        }

        $sQuery =
        "
            SELECT
                `id_poll`,
                `id_profile`,
                `poll_date`,
                (UNIX_TIMESTAMP() - `poll_date`) AS 'sec',
                `poll_approval`,
                `poll_featured`
            FROM
                `{$this -> _sTable}`
            WHERE
                1
                {$sAddonSql}
                {$sExtraSql}
            ORDER BY
                {$sOrder}
            {$sLimit}
        ";

        return $this -> getAll($sQuery);
    }

    /**
     * Function will get number of featured polls
     *
     * @param  : $iStatus     (integer) - poll's featured status;
     * @param  : $bOnlyPublic (boolean) - if isset this param than will return only public polls;
     * @return : (integer) - number of featured polls;
     */
    function getFeaturedCount($iStatus = 1, $bOnlyPublic = false)
    {
        $iStatus = (int) $iStatus;
        settype($bOnlyPublic, 'boolean');

        $sExtraSql = $bOnlyPublic ? ' AND `allow_view_to` = ' . BX_DOL_PG_ALL : null;
        $sQuery = "SELECT COUNT(*) FROM  `{$this -> _sTable}` WHERE `poll_featured` = {$iStatus} {$sExtraSql}";
        return $this -> getOne($sQuery);
    }

    /**
     * Function will generate all featured polls;
     *
     * @param : $bOnlyPublic (boolean) - if isset this param than will return only public polls;
     * @param : $sLimit      (string) - sql query (limit of returning rows)
     */
    function getAllFeaturedPolls($sLimit, $iStatus = 1, $bOnlyPublic = false)
    {
        $sLimit = $this -> escape($sLimit, false);
        $iStatus = (int) $iStatus;
        settype($bOnlyPublic, 'boolean');

        $sExtraSql = $bOnlyPublic ? ' AND `allow_view_to` = ' . BX_DOL_PG_ALL : null;
        $sQuery = "SELECT *, UNIX_TIMESTAMP()-`poll_date` AS `poll_ago` FROM `{$this -> _sTable}` WHERE `poll_status`='active' AND `poll_approval`='1' AND `poll_featured`='{$iStatus}' {$sExtraSql} ORDER BY `poll_date` DESC {$sLimit}";
        return $this -> getAll($sQuery);
    }

    /**
     * Function will return the number of active pools;
     *
     * @param : $iMemberID (integer) - logged member's Id ;
     * @param : $bAllPools (boolean) - if isset this param that will find all
                        member's poll's (approved and not) ;
     * @return : (integer) - number of active pools;
     */
    function getPollsCount($iMemberID = 0, $bAllPools = false, $iApproval = 1, $sExtraSql = null)
    {
        $iMemberID = (int) $iMemberID;
//        $bOnlyPublic = (bool) $bOnlyPublic;
        $iApproval = (int) $iApproval;
        $sExtraSql = $this -> escape($sExtraSql, false);

        // ** init some needed variables ;

        $sAddonSql       = null;
        $aWhereCondition = array();

        $aWhereCondition[0] = " AND `poll_status`   = 'active' ";
        $aWhereCondition[1] = " AND `poll_approval` = {$iApproval} ";
        $aWhereCondition[2] = " AND `id_profile`    = {$iMemberID} ";

        // if need the only member's pools ;
        $sIsEmpty = true;
        if ( $iMemberID  ) {
            if ( !$bAllPools ) {
                // only approved  member's polls ;
                $sAddonSql  = $aWhereCondition[1] . $aWhereCondition[2];
            } else {
                $sAddonSql  = $aWhereCondition[2];
            }
        } else {
            if ( !$bAllPools ) {
                $sAddonSql  = $aWhereCondition[0] . $aWhereCondition[1];
            }
        }

        $sQuery =
        "
        SELECT
            COUNT(*)
        FROM
            `{$this -> _sTable}`
        WHERE
            1
            {$sAddonSql}
            {$sExtraSql}
        ";

        return $this -> getOne($sQuery);
    }


    /**
     * Function will get count of unapproved polls;
     *
     * @param  : $iProfileId (integer) - profile's Id;
     * @return : (integer) - number of polls;
     */
    function getUnApprovedPolls($iProfileId)
    {
        $iProfileId = (int) $iProfileId;

        $sQuery = "SELECT COUNT(*) FROM `{$this -> _sTable}` WHERE `poll_approval` = 0 AND `id_profile` = {$iProfileId}";
        return $this -> getOne($sQuery);
    }

    /**
     * Function will return array with all poll's questions ;
     *
     * @param  : $iPollId (integer) - poll's Id ;
     * @return : (array) array with all questions ;
                    [ id_poll ]     - (integer) poll's Id;
                    [ id_profile ]  - (integer) poll's owner Id;
                    [ poll_date ]    - (string)  poll's date creation;
                    [ sec ]         - (integer) poll's date creation in seconds;
     */
    function getPollInfo($iPollId)
    {
        $iPollId = (int) $iPollId;

        $sQuery ="SELECT *, UNIX_TIMESTAMP()-`poll_date` AS 'poll_ago' FROM `{$this -> _sTable}` WHERE `id_poll` = {$iPollId}";
        return $this -> getAll($sQuery);
    }

    /**
     * Function will set the number of votes ;
     *
     * @param  : $iPollId     (integer)     - poll's Id;
     * @param  : $sVotes      (string)       - votes string;
     * @param  : $iVotesCount (integer) - number of all votes;
     * @return : (integer) - number of affected rows ;
     */
    function setVotes( $iPollId, $sVotes, $iVotesCount )
    {
        $iPollId = (int) $iPollId;
        $sVotes = process_db_input($sVotes, BX_TAGS_STRIP);
        $iVotesCount = (int) $iVotesCount;

        $sQuery =
        "
            UPDATE
                `{$this -> _sTable}`
            SET
                `poll_results`      = '{$sVotes}',
                `poll_total_votes`  = {$iVotesCount}
            WHERE
                `id_poll` = {$iPollId}
        ";

        return $this -> query($sQuery);
    }

    /**
     * Function will set poll's status , featured;
     *
     * @param  : $iPollId (integer) - poll's Id ;
     * @return : (integer) - number of affected rows ;
     */
    function setOption($iPollId, $sAction = 'approval')
    {
        $iPollId = (int) $iPollId;

        $sQuery =
        "
            UPDATE
                `{$this -> _sTable}`
            SET
                `poll_{$sAction}` = IF (`poll_{$sAction}` = 1, 0, 1)
            WHERE
               `id_poll` = {$iPollId}
        ";

        return $this -> query($sQuery);
    }

    /**
     * Function will create new poll ;
     *
     * @param : $aPoolInfo (array) - contain some poll's information;
                    [ owner_id ] - (integer) poll's owner Id ;
                    [ question ] - (string)  poll's question ;
                    [ answers ]  - (string)  poll's answers (string separated by tag BX_POLL_ANS_DIVIDER);
                    [ results ]  - (string)  poll's results (string separated by ';');
                    [ tags ]     - (string)  poll's tags (string separated by 'space');
     * $isAdmin : (boolean) - is isset this value that is admin mode;
     * @return : (integer)      - code of operation;
     */
    function createPoll($aPoolInfo, $isAdmin = false)
    {
        $isAdmin = (bool) $isAdmin;

        if( !is_array($aPoolInfo) ) {
            return;
        }

        // process recived array
        foreach($aPoolInfo as $sKey => $sValue) {
            $aPoolInfo[$sKey] = process_db_input($sValue, BX_TAGS_NO_ACTION
                , BX_SLASHES_AUTO);
        }

        // ** init some needed variables ;

        $iPollsCount = -1;
        $iAutoActive =  1;
        $iResponse   =  0;

        if ($aPoolInfo['owner_id']) {
            $iPollsCount = $this -> getPollsCount( $aPoolInfo['owner_id'], true );
            $iAutoActive = ($this -> _oConfig -> iAutoActivate) ? 1 : 0;

            if (!$this -> _oConfig -> iAlowMembersPolls) {
                return 0;
            }
        }

        if ($iPollsCount < $this -> _oConfig -> iAlowPollNumber || $isAdmin) {
            $iCurrentDate = date('U');
            $sQuery =
            "
                INSERT INTO
                    `{$this -> _sTable}`
                SET
                    `id_profile`        = {$aPoolInfo['owner_id']},
                    `poll_question`     = '{$aPoolInfo['question']}',
                    `poll_answers`      = '{$aPoolInfo['answers']}',
                    `poll_results`      = '{$aPoolInfo['results']}',
                    `poll_status`       = 'active',
                    `poll_approval`     = {$iAutoActive},
                    `poll_date`         = {$iCurrentDate},
                    `poll_tags`         = '{$aPoolInfo['tags']}',
                    `allow_comment_to`  = {$aPoolInfo['allow_comment']},
                    `allow_vote_to`     = {$aPoolInfo['allow_vote']},
                    `poll_categories`   = '{$aPoolInfo['category']}',
                    `allow_view_to`     = '{$aPoolInfo['allow_view']}'
            ";

            $this -> query($sQuery);
            $iResponse = 1;
        } else {
            $iResponse = 2;
        }

        return $iResponse;
    }

    /**
     * Function will edit poll ;
     *
     * @param : $aPoolInfo (array) - contain some poll's information;
                    [ question ] - (string)  poll's question ;
                    [ answers ]  - (string)  poll's answers (string separated by tag BX_POLL_ANS_DIVIDER);
                    [ status ]   - (string)  poll's status as active or not;
                    [ approve ]  - (boolean) poll's approve or not;
                    [ Id ]       - (integer) poll's Id;
                    [ tags ]     - (string)  poll's tags (string separated by 'space');
     * @return : (integer) - number of affected rows;
     */
    function editPoll($aPoolInfo)
    {
        if( !is_array($aPoolInfo) ) {
            return;
        }

        // procces recived array
        foreach($aPoolInfo as $sKey => $sValue) {
            if($sKey != 'answers' ) {
                $aPoolInfo[$sKey] = process_db_input($sValue, BX_TAGS_STRIP);
            } else {
                $aPoolInfo[$sKey] = process_db_input($sValue);
            }
        }

        // ** init some neede variables ;

        $sAddonSql = null;

        if ( isset($aPoolInfo['approve']) ) {
            $sAddonSql = ( $aPoolInfo['approve'] )
                ? ',`poll_approval` = 1'
                : ',`poll_approval` = 0';
        }

        $sQuery =
        "
            UPDATE
              `{$this -> _sTable}`
            SET
                `poll_question`     = '{$aPoolInfo['question']}',
                `poll_answers`      = '{$aPoolInfo['answers']}',
                `poll_status`       = '{$aPoolInfo['status']}',
                `poll_tags`         = '{$aPoolInfo['tags']}',
                `allow_comment_to`  = {$aPoolInfo['allow_comment']},
                `allow_vote_to`     = {$aPoolInfo['allow_vote']},
                `poll_categories`   = '{$aPoolInfo['category']}',
                `allow_view_to`     = '{$aPoolInfo['allow_view']}'
                {$sAddonSql}
            WHERE
                `id_poll` = {$aPoolInfo['Id']}
        ";

       return $this -> query($sQuery);
    }

    /**
     * Function will delete poll ;
     *
     * @param  : $iPollId (integer) - poll's Id;
     * @return : (integer) - number of affected rows;
     */
    function deletePoll($iPollId)
    {
        $iPollId = (int) $iPollId;
        $this -> clearAttachetData($iPollId);
        $sQuery = "DELETE FROM  `{$this -> _sTable}` WHERE `id_poll` = {$iPollId}";
        return $this -> query($sQuery);
    }

    /**
     * Function will delete all poll's attachet data;
     * from cmts, votings....
     *
     * @iPollId (integer) - poll's id;
     */
    function clearAttachetData($iPollId)
    {
        $iPollId = (int) $iPollId;

        // delete from comments;
        $sQuery =
        "
            DELETE
                `{$this -> sTablePrefix}cmts`,
                `{$this -> sTablePrefix}cmts_track`
            FROM
                `{$this -> sTablePrefix}cmts`
            LEFT JOIN
                `{$this -> sTablePrefix}cmts_track`
            ON
                `{$this -> sTablePrefix}cmts_track`.`cmt_id` = `{$this -> sTablePrefix}cmts`.`cmt_id`
            WHERE
                `{$this -> sTablePrefix}cmts`.`cmt_object_id` = {$iPollId}
        ";
        db_res($sQuery);

        // delete from votings;
        $sQuery =
        "
            DELETE
                `{$this -> sTablePrefix}rating`,
                `{$this -> sTablePrefix}voting_track`
            FROM
                `{$this -> sTablePrefix}rating`
            LEFT JOIN
                `{$this -> sTablePrefix}voting_track`
            ON
                `{$this -> sTablePrefix}voting_track`.`id` = `{$this -> sTablePrefix}rating`.`id`
            WHERE
                `{$this -> sTablePrefix}rating`.`id` = {$iPollId}
        ";
        db_res($sQuery);
    }

    /**
     * Function will find all polls by month;
     *
     * @param : $iYear      (integer) - needed year;
     * @param : $iMonth     (integer) - needed month;
     * @param : $iNextYear  (integer) - next year;
     * @param : $iNextMonth (integer) - next month;
     *
     * @return (array);
     */
    function getPollsByMonth ($iYear, $iMonth, $iNextYear, $iNextMonth)
    {
        $iYear = (int) $iYear;
        $iMonth = (int) $iMonth;
        $iNextYear = (int) $iNextYear;
        $iNextMonth = (int) $iNextMonth;

        return $this->getAll ("SELECT `id_poll`, DAYOFMONTH(FROM_UNIXTIME(`poll_date`)) AS `Day`
            FROM `{$this -> _sTable}`
            WHERE `poll_date` >= UNIX_TIMESTAMP('{$iYear}-{$iMonth}-1') AND `poll_date` < UNIX_TIMESTAMP('{$iNextYear}-{$iNextMonth}-1') AND `poll_approval` = 1");
    }

    /**
     * Function will return global category number;
     *
     * @return : (integer) - category's number;
     */
    function getSettingsCategory($sCatName)
    {
        $sCatName = process_db_input($sCatName, BX_TAGS_STRIP);
        return $this -> getOne('SELECT `kateg` FROM `sys_options` WHERE `Name` = "' . $sCatName . '"');
    }
}
