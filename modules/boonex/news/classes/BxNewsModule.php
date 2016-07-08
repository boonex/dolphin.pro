<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextModule');

require_once('BxNewsCalendar.php');
require_once('BxNewsCmts.php');
require_once('BxNewsVoting.php');
require_once('BxNewsSearchResult.php');
require_once('BxNewsData.php');

/**
 * News module by BoonEx
 *
 * This module is needed to manage site news.
 *
 *
 * Profile's Wall:
 * no spy events
 *
 *
 *
 * Spy:
 * no spy events
 *
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 *
 * Service methods:
 *
 * Get post block.
 * @see BxNewsModule::servicePostBlock
 * BxDolService::call('news', 'post_block');
 * @note is needed for internal usage.
 *
 * Get edit block.
 * @see BxNewsModule::serviceEditBlock
 * BxDolService::call('news', 'edit_block', array($mixed));
 * @note is needed for internal usage.
 *
 * Get administration block.
 * @see BxNewsModule::serviceAdminBlock
 * BxDolService::call('news', 'admin_block', array($iStart, $iPerPage, $sFilterValue));
 * @note is needed for internal usage.
 *
 * Get block with all news ordered by the time of posting.
 * @see BxNewsModule::serviceArchiveBlock
 * BxDolService::call('news', 'archive_block', array($iStart, $iPerPage));
 * @note is needed for internal usage.
 *
 * Get block with news marked as featured.
 * @see BxNewsModule::serviceFeaturedBlock
 * BxDolService::call('news', 'featured_block', array($iStart, $iPerPage));
 * @note is needed for internal usage.
 *
 * Get block with news ordered by their rating.
 * @see BxNewsModule::serviceTopRatedBlock
 * BxDolService::call('news', 'top_rated_block', array($iStart, $iPerPage));
 * @note is needed for internal usage.
 *
 * Get block with all news ordered by their popularity(number of views).
 * @see BxNewsModule::servicePopularBlock
 * BxDolService::call('news', 'popular_block', array($iStart, $iPerPage));
 * @note is needed for internal usage.
 *
 *
 * Alerts:
 * Alerts type/unit - 'news'
 * The following alerts are rised
 *
 *  post - news is added
 *      $iObjectId - news id
 *      $iSenderId - admin's id
 *
 *  edit - news was modified
 *      $iObjectId - news id
 *      $iSenderId - admin's id
 *
 *  featured - news was marked as featured
 *      $iObjectId - news id
 *      $iSenderId - admin's id
 *
 *  publish - news was published
 *      $iObjectId - news id
 *      $iSenderId - admin's id
 *
 *  unpublish - news was unpublished
 *      $iObjectId - news id
 *      $iSenderId - admin's id
 *
 *  delete - news was deleted
 *      $iObjectId - news id
 *      $iSenderId - admin's id
 *
 */
class BxNewsModule extends BxDolTextModule
{
    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        //--- Define Membership Actions ---//
        defineMembershipActions(array('news delete'), 'ACTION_ID_');
    }

    /**
     * Service methods
     */
    function serviceNewsRss($iLength = 0)
    {
        return $this->actionRss($iLength);
    }

    /**
     * Action methods
     */
    function actionGetNews($sSampleType = 'all', $iStart = 0, $iPerPage = 0)
    {
        return $this->actionGetEntries($sSampleType, $iStart, $iPerPage);
    }

    /**
     * Private methods.
     */
    function _createObjectCalendar($iYear, $iMonth)
    {
        return new BxNewsCalendar($iYear, $iMonth, $this->_oDb, $this->_oConfig);
    }
    function _createObjectCmts($iId)
    {
        return new BxNewsCmts($this->_oConfig->getCommentsSystemName(), $iId);
    }
    function _createObjectVoting($iId)
    {
        return new BxNewsVoting($this->_oConfig->getVotesSystemName(), $iId);
    }
    function _isDeleteAllowed($bPerform = false)
    {
        if(!isLogged())
            return false;

        if(isAdmin())
            return true;

        $aCheckResult = checkAction(getLoggedId(), ACTION_ID_NEWS_DELETE, $bPerform);
        return $aCheckResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
    }
}
