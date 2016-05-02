<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigModuleDb');

/*
 * Events module Data
 */
class BxEventsDb extends BxDolTwigModuleDb
{
    /*
     * Constructor.
     */
    function __construct(&$oConfig)
    {
        parent::__construct($oConfig);

        $this->_sTableMain = 'main';
        $this->_sTableShoutbox = 'shoutbox';
        $this->_sTableMediaPrefix = '';
        $this->_sFieldId = 'ID';
        $this->_sFieldAuthorId = 'ResponsibleID';
        $this->_sFieldUri = 'EntryUri';
        $this->_sFieldTitle = 'Title';
        $this->_sFieldDescription = 'Description';
        $this->_sFieldTags = 'Tags';
        $this->_sFieldThumb = 'PrimPhoto';
        $this->_sFieldStatus = 'Status';
        $this->_sFieldFeatured = 'Featured';
        $this->_sFieldCreated = 'Date';
        $this->_sFieldJoinConfirmation = 'JoinConfirmation';
        $this->_sFieldFansCount = 'FansCount';
        $this->_sTableFans = 'participants';
        $this->_sTableAdmins = 'admins';
        $this->_sFieldAllowViewTo = 'allow_view_event_to';
        $this->_sFieldCommentCount = 'CommentsCount';
    }

    function getUpcomingEvent ($isFeatured)
    {
        $sWhere = '';
        if ($isFeatured)
            $sWhere = " AND `{$this->_sFieldFeatured}` = '1' ";
        return $this->getRow("SELECT * FROM `" . $this->_sPrefix . "main` 
        WHERE `EventEnd` > ? AND `Status` = ? AND `{$this->_sFieldAllowViewTo}` = ? $sWhere ORDER BY `Featured` DESC, `EventStart` ASC LIMIT 1", [time(), 'approved', BX_DOL_PG_ALL]);
    }

    function getEntriesByMonth ($iYear, $iMonth, $iNextYear, $iNextMonth)
    {
        $aEvents = array ();
        $iDays = cal_days_in_month(CAL_GREGORIAN, $iMonth, $iYear);
        for ($iDay=1 ; $iDay <= $iDays ; ++$iDay) {
            $a = $this->getAll ("SELECT *, $iDay AS `Day`
                FROM `" . $this->_sPrefix . "main`
                WHERE ((`EventEnd` >= UNIX_TIMESTAMP('$iYear-$iMonth-$iDay 00:00:00')) AND (`EventStart` <= UNIX_TIMESTAMP('$iYear-$iMonth-$iDay 23:59:59')))
                    AND `Status` = 'approved'");
            if ($a)
                $aEvents = array_merge($aEvents, $a);
        }
        return $aEvents;
    }

    function deleteEntryByIdAndOwner ($iId, $iOwner, $isAdmin)
    {
        if ($iRet = parent::deleteEntryByIdAndOwner ($iId, $iOwner, $isAdmin)) {
            $this->query ("DELETE FROM `" . $this->_sPrefix . "participants` WHERE `id_entry` = $iId");
            $this->deleteEntryMediaAll ($iId, 'images');
            $this->deleteEntryMediaAll ($iId, 'videos');
            $this->deleteEntryMediaAll ($iId, 'sounds');
            $this->deleteEntryMediaAll ($iId, 'files');
        }
        return $iRet;
    }

}
