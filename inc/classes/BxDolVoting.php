<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolVotingQuery.php' );

define( 'BX_PERIOD_PER_VOTE', 7 * 86400 );
define( 'BX_OLD_VOTES', 365*86400 ); // votes older than this number of seconds will be deleted automatically

/**
 * Votings for any content
 *
 * Related classes:
 *  BxDolVotingQuery - votings database queries
 *  BxBaseVotingView - votings base representation
 *  BxTemplVotingView - custom template representation
 *
 * AJAX votings for any content.
 * Big and small votings stars are supported
 *
 * To add votings section to your site you need to add a record to 'sys_objects_vote' table:
 *
 * ID - autoincremented id for internal usage
 * ObjectName - your unique module name, with vendor prefix, lowercase and spaces are underscored
 * TableRating - table name where sumarry votigs are stored
 * TableTrack - table name where each vote is stored
 * RowPrefix - row prefix for TableRating
 * MaxVotes - max vote number, 5 by default
 * PostName - post variable name with rating
 * IsDuplicate - number of seconds to not allow duplicate vote (for some bad reason it is define here)
 * IsOn - is this vote object enabled
 * ClassName - custom class name for HotOrNot @see BxDolRate
 * ClassFile - custom class path for HotOrNot
 * TriggerTable - table to be updated upon each vote
 * TriggerFieldRate - TriggerTable table field with average rate
 * TriggerFieldRateCount - TriggerTable table field with votes count
 * TriggerFieldId - TriggerTable table field with unique record id, primary key
 * OverrideClassName - your custom class name, if you overrride default class
 * OverrideClassFile - your custom class path
 *
 * You can refer to BoonEx modules for sample record in this table.
 *
 *
 *
 * Example of usage:
 * After filling in the table you can show big votings in any place, using the following code:
 *
 * bx_import('BxTemplVotingView');
 * $o = new BxTemplVotingView ('value of ObjectName field', $iYourEntryId);
 * if (!$o->isEnabled()) return '';
 *     echo $o->getBigVoting (1); // 1 - rate is allowed
 *
 * And small votings, using the following code:
 *
 * $o = new BxTemplVotingView ('value of ObjectName field', $iYourEntryId);
 * if (!$o->isEnabled()) return '';
 *     echo $o->getSmallVoting (0); // 0 - rate is not allowed, like readony votings
 *
 * In some cases votes are already in database and there is no need to execute additional query to get ratings,
 * so you can use the following code:
 *
 * $o = new BxTemplVotingView ('value of ObjectName field', 0);
 * foreach ($aRecords as $aData)
 *     echo $o->getJustVotingElement(0, $aData['ID'], $aData['voting_rate']);
 *
 * Please note that you never need to use BxDolVoting class directly, use BxTemplVotingView instead.
 * Also if you override votings class with your own then make it child of BxTemplVotingView class.
 *
 *
 *
 * Memberships/ACL:
 * vote - ACTION_ID_VOTE
 *
 *
 *
 * Alerts:
 * Alerts type/unit - every module has own type/unit, it equals to ObjectName
 * The following alerts are rised
 *
 * rate - comment was posted
 *      $iObjectId - entry id
 *      $iSenderId - rater user id
 *      $aExtra['rate'] - rate
 *
 */
class BxDolVoting
{
    var $_iId = 0;	// item id to be rated
    var $_iCount = 0; // number of votes
    var $_fRate = 0; // average rate
    var $_sSystem = 'profile'; // current rating system name

    var $_aSystem = array (); // current rating system array

    var $_oQuery = null;

    /**
     * Constructor
     */
    function __construct( $sSystem, $iId, $iInit = 1)
    {
        $this->_aSystems =& $this->getSystems();

        $this->_sSystem = $sSystem;
        if (isset($this->_aSystems[$sSystem]))
            $this->_aSystem = $this->_aSystems[$sSystem];
        else
            return;

        $this->_oQuery = new BxDolVotingQuery($this->_aSystem);

        if ($iInit)
            $this->init($iId);
    }

	/**
     * get voting object instanse
     * @param $sSys voting object name 
     * @param $iId associated content id
     * @param $iInit perform initialization
     * @return null on error, or ready to use class instance
     */ 
    static function getObjectInstance($sSys, $iId, $iInit = true) 
    {
        $aSystems = self::getSystems ();
        if (!isset($aSystems[$sSys]))
            return null;

        bx_import('BxTemplVotingView');
        $sClassName = 'BxTemplVotingView';
        if ($aSystems[$sSys]['override_class_name']) {
            require_once (BX_DIRECTORY_PATH_ROOT . $aSystems[$sSys]['override_class_file']);
            $sClassName = $aSystems[$sSys]['override_class_name'];  
        } 

        return new $sClassName($sSys, $iId, $iInit);
    }

    public static function & getSystems ()
    {
        if (isset($GLOBALS['bx_dol_voting_systems'])) {
            return $GLOBALS['bx_dol_voting_systems'];
        }

        $oCache = $GLOBALS['MySQL']->getDbCacheObject();

        $GLOBALS['bx_dol_voting_systems'] = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_objects_vote'));

        if (null === $GLOBALS['bx_dol_voting_systems']) {

            // cache is empty | load data from DB

            $sQuery  = "SELECT * FROM `sys_objects_vote`";
            $rResult = db_res($sQuery);

            $GLOBALS['bx_dol_voting_systems'] = array();
            while( $aRow = $rResult ->fetch() ) {
                $GLOBALS['bx_dol_voting_systems'][$aRow['ObjectName']] = array
                (
                    'table_rating'	=> $aRow['TableRating'],
                    'table_track'	=> $aRow['TableTrack'],
                    'row_prefix'	=> $aRow['RowPrefix'],
                    'max_votes'		=> $aRow['MaxVotes'],
                    'post_name'		=> $aRow['PostName'],
                    'is_duplicate'	=> is_int($aRow['IsDuplicate']) ? $aRow['IsDuplicate'] : constant($aRow['IsDuplicate']),
                    'is_on'			=> $aRow['IsOn'],

                    'className'     => $aRow['className'],
                    'classFile'     => $aRow['classFile'],

                    'trigger_table'            => $aRow['TriggerTable'], // table with field to update on every rating change
                    'trigger_field_rate'       => $aRow['TriggerFieldRate'], // table field name with rating
                    'trigger_field_rate_count' => $aRow['TriggerFieldRateCount'], // table field name with rating count
                    'trigger_field_id'         => $aRow['TriggerFieldId'], // table field name with object id

                    'override_class_name' => $aRow['OverrideClassName'], // new class to override
                    'override_class_file' => $aRow['OverrideClassFile'], // class file path
                );
            }

            // write data into cache file

            $oCache = $GLOBALS['MySQL']->getDbCacheObject();
            $oCache->setData ($GLOBALS['MySQL']->genDbCacheKey('sys_objects_vote'), $GLOBALS['bx_dol_voting_systems']);
        }

        return $GLOBALS['bx_dol_voting_systems'];
    }

    function init ($iId)
    {
        if(!$iId)
			$iId = $this->_iId;

        if(!$this->isEnabled())
        	return;

        if (!$this->_iId && $iId)
            $this->setId($iId);
    }

    function initVotes ()
    {
        if(!$this->isEnabled() || !$this->_oQuery)
			return;

        $aVote = $this->_oQuery->getVote ($this->getId());
        if(empty($aVote) || !is_array($aVote))
        	return;

        $this->_iCount = $aVote['count'];
        $this->_fRate = $aVote['rate'];
    }

    function makeVote ($iVote)
    {
        if(!$this->isEnabled() || $this->isDublicateVote() || !$this->checkAction())
        	return false;

        if($this->_sSystem == 'profile' && $this->getId() == getLoggedId())
            return false;

        $sVoterIdentification = isLogged() ? getLoggedId() : getVisitorIP();
        if(!$this->_oQuery->putVote ($this->getId(), $sVoterIdentification, $iVote))
        	return false;

		$this->checkAction(true);
		$this->_triggerVote();

		$oZ = new BxDolAlerts($this->_sSystem, 'rate', $this->getId(), getLoggedId(), array ('rate' => $iVote));
		$oZ->alert();

		return true;
    }

    function checkAction ($bPerformAction = false)
    {
        if (isset($this->_checkActionResult))
            return $this->_checkActionResult;

        $iId = getLoggedId();
        $aResult = checkAction($iId, ACTION_ID_VOTE, $bPerformAction);
        return ($this->_checkActionResult = ($aResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED));
    }

    function isDublicateVote ()
    {
        if (!$this->isEnabled()) return false;

        $sVoterIdentification = isLogged() ? getLoggedId() : getVisitorIP();
        return $this->_oQuery->isDublicateVote ($this->getId(), $sVoterIdentification);
    }

    function getId ()
    {
        return $this->_iId;
    }

    function isEnabled ()
    {
        return $this->_aSystem['is_on'];
    }

    function getMaxVote()
    {
        return $this->_aSystem['max_votes'];
    }

    function getVoteCount()
    {
        return $this->_iCount;
    }

    function getVoteRate()
    {
        return $this->_fRate;
    }

    function getSystemName()
    {
        return $this->_sSystem;
    }

    /**
     * set id to operate with votes
     */
    function setId ($iId)
    {
        if ($iId == $this->getId()) return;
        $this->_iId = $iId;
        $this->initVotes();
    }

    function getSqlParts ($sMailTable, $sMailField)
    {
        if ($this->isEnabled())
            return $this->_oQuery->getSqlParts ($sMailTable, $sMailField);
        else
            return array();
    }

    function isValidSystem ($sSystem)
    {
        return isset($this->_aSystems[$sSystem]);
    }

    function deleteVotings ($iId)
    {
        if (!(int)$iId) return false;
        $this->_oQuery->deleteVotings ($iId);
        return true;
    }

    function getTopVotedItem ($iDays, $sJoinTable = '', $sJoinField = '', $sWhere = '')
    {
        return $this->_oQuery->getTopVotedItem ($iDays, $sJoinTable, $sJoinField, $sWhere);
    }

    function getVotedItems ($sIp)
    {
        return $this->_oQuery->getVotedItems ($sIp);
    }

    /**
     * it is called on cron every day or similar period to clean old votes
     */
    function maintenance ()
    {
        $iDeletedRecords = 0;
        foreach ($this->_aSystems as $aSystem) {
            if (!$aSystem['is_on'])
                continue;
            $sPre = $aSystem['row_prefix'];
            $iDeletedRecords += $GLOBALS['MySQL']->query("DELETE FROM `{$aSystem['table_track']}` WHERE `{$sPre}date` < DATE_SUB(NOW(), INTERVAL " . BX_OLD_VOTES . " SECOND)");
            $GLOBALS['MySQL']->query("OPTIMIZE TABLE `{$aSystem['table_track']}`");
        }
        return $iDeletedRecords;
    }

    public function actionVote()
    {
    	if(!$this->isEnabled())
			return '{}';

        $iResult = $this->_getVoteResult();
        if($iResult === false) 
        	return '{}';
        
		if(!$this->makeVote($iResult))
        	return '{}';

		$this->initVotes();
        echo json_encode(array('rate' => $this->getVoteRate(), 'count' => $this->getVoteCount()));
    }

    protected function _getVoteResult ()
    {
        if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') !== 0 || bx_get($this->_aSystem['post_name']) === false)
			return false;

        $iVote = (int)bx_get($this->_aSystem['post_name']);
        if(!$iVote)
			return false;

        if($iVote > $this->getMaxVote())
			$iVote = $this->getMaxVote();

        if($iVote < 1)
			$iVote = 1;

        return $iVote;
    }

    protected function _triggerVote()
    {
        if (!$this->_aSystem['trigger_table'])
            return false;
        $iId = $this->getId();
        if (!$iId)
            return false;
        $this->initVotes ();
        $iCount = $this->getVoteCount();
        $fRate = $this->getVoteRate();
        return $this->_oQuery->updateTriggerTable($iId, $fRate, $iCount);
    }
}
