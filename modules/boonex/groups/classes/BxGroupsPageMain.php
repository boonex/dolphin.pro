<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigPageMain');

class BxGroupsPageMain extends BxDolTwigPageMain
{
    function __construct(&$oMain)
    {
        $this->sSearchResultClassName = 'BxGroupsSearchResult';
        $this->sFilterName = 'bx_groups_filter';
        parent::__construct('bx_groups_main', $oMain);
    }

    function getBlockCode_LatestFeaturedGroup()
    {
        $aDataEntry = $this->oDb->getLatestFeaturedItem ();
        if (!$aDataEntry)
            return false;

        $aAuthor = getProfileInfo($aDataEntry['author_id']);

        $sImageUrl = '';
        $sImageTitle = '';
        $a = array ('ID' => $aDataEntry['author_id'], 'Avatar' => $aDataEntry['thumb']);
        $aImage = BxDolService::call('photos', 'get_image', array($a, 'file'), 'Search');

        bx_groups_import('Voting');
        $oRating = new BxGroupsVoting ('bx_groups', $aDataEntry['id']);

        $aVars = array (
            'bx_if:image' => array (
                'condition' => !$aImage['no_image'] && $aImage['file'],
                'content' => array (
                    'image_url' => !$aImage['no_image'] && $aImage['file'] ? $aImage['file'] : '',
                    'image_title' => !$aImage['no_image'] && $aImage['title'] ? $aImage['title'] : '',
                    'group_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'view/' . $aDataEntry['uri'],
                ),
            ),
            'group_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'view/' . $aDataEntry['uri'],
            'group_title' => $aDataEntry['title'],
            'author_title' => _t('_From'),
            'author_username' => getNickName($aAuthor['ID']),
            'author_url' => getProfileLink($aAuthor['ID']),
            'rating' => $oRating->isEnabled() ? $oRating->getJustVotingElement (true, $aDataEntry['id']) : '',
            'fans_count' => $aDataEntry['fans_count'],
            'country_city' => $this->oMain->_formatLocation($aDataEntry, false, true),
        );
        return $this->oTemplate->parseHtmlByName('latest_featured_group', $aVars);
    }

    function getBlockCode_Recent()
    {
        return $this->ajaxBrowse('recent', $this->oDb->getParam('bx_groups_perpage_main_recent'));
    }
}
