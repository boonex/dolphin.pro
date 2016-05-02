<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import ('BxDolTwigPageMain');

class BxEventsPageMain extends BxDolTwigPageMain
{
    function __construct(&$oEventsMain)
    {
        parent::__construct('bx_events_main', $oEventsMain);
        $this->sSearchResultClassName = 'BxEventsSearchResult';
        $this->sFilterName = 'bx_events_filter';
    }

    function getBlockCode_UpcomingPhoto()
    {
        $aEvent = $this->oDb->getUpcomingEvent (getParam('bx_events_main_upcoming_event_from_featured_only') ? true : false);
        if (!$aEvent)
            return false;

        $aAuthor = getProfileInfo($aEvent['ResponsibleID']);

        $a = array ('ID' => $aEvent['ResponsibleID'], 'Avatar' => $aEvent['PrimPhoto']);
        $aImage = BxDolService::call('photos', 'get_image', array($a, 'file'), 'Search');

        bx_events_import('Voting');
        $oRating = new BxEventsVoting ('bx_events', (int)$aEvent['ID']);

        $sEventUrl = BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'view/' . $aEvent['EntryUri'];

        $aVars = array (
            'bx_if:image' => array (
                'condition' => !$aImage['no_image'] && $aImage['file'],
                'content' => array (
                    'image_url' => !$aImage['no_image'] && $aImage['file'] ? $aImage['file'] : '',
                    'image_title' => !$aImage['no_image'] && $aImage['title'] ? $aImage['title'] : '',
                    'event_url' => $sEventUrl,
                ),
            ),
            'event_url' => $sEventUrl,
            'event_title' => $aEvent['Title'],
            'event_start_in' => $this->oMain->_formatDateInBrowse($aEvent),
            'author_title' => _t('_From'),
            'author_username' => getNickName($aAuthor['ID']),
            'author_url' => getProfileLink($aAuthor['ID']),

            'rating' => $oRating->isEnabled() ? $oRating->getJustVotingElement (true, $aEvent['ID']) : '',
            'participants' => $aEvent['FansCount'],
            'country_city' => $this->oMain->_formatLocation($aEvent, true, true),
            'place' => $aEvent['Place'],
        );
        return $this->oTemplate->parseHtmlByName('main_event', $aVars);
    }

    function getBlockCode_UpcomingList()
    {
        return $this->ajaxBrowse('upcoming', $this->oDb->getParam('bx_events_perpage_main_upcoming'));
    }

    function getBlockCode_PastList()
    {
        return $this->ajaxBrowse('past', $this->oDb->getParam('bx_events_perpage_main_past'));
    }

    function getBlockCode_RecentlyAddedList()
    {
        return $this->ajaxBrowse('recent', $this->oDb->getParam('bx_events_perpage_main_recent'));
    }
}
