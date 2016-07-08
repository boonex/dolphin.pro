<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigPageMain');

class BxStorePageMain extends BxDolTwigPageMain
{
    function __construct(&$oMain)
    {
        $this->sSearchResultClassName = 'BxStoreSearchResult';
        $this->sFilterName = 'bx_store_filter';
        parent::__construct('bx_store_main', $oMain);
    }

    function getBlockCode_LatestFeaturedProduct()
    {
        $aDataEntry = $this->oDb->getLatestFeaturedItem ();
        if (!$aDataEntry)
            return false;

        $aAuthor = getProfileInfo($aDataEntry['author_id']);

        $sImageUrl = '';
        $sImageTitle = '';
        $a = array ('ID' => $aDataEntry['author_id'], 'Avatar' => $aDataEntry['thumb']);
        $aImage = BxDolService::call('photos', 'get_image', array($a, 'file'), 'Search');

        bx_store_import('Voting');
        $oRating = new BxStoreVoting ('bx_store', $aDataEntry['id']);

        $aVars = array (
            'bx_if:image' => array (
                'condition' => !$aImage['no_image'] && $aImage['file'],
                'content' => array (
                    'image_url' => !$aImage['no_image'] && $aImage['file'] ? $aImage['file'] : '',
                    'image_title' => !$aImage['no_image'] && $aImage['title'] ? $aImage['title'] : '',
                    'product_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'view/' . $aDataEntry['uri'],
                ),
            ),
            'product_url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'view/' . $aDataEntry['uri'],
            'product_title' => $aDataEntry['title'],
            'author_title' => _t('_From'),
            'author_username' => getNickName($aAuthor['ID']),
            'author_url' => getProfileLink($aAuthor['ID']),
            'rating' => $oRating->isEnabled() ? $oRating->getJustVotingElement (true, $aDataEntry['id']) : '',
            'created' => defineTimeInterval($aDataEntry['created']),
            'price_range' => $this->oMain->_formatPriceRange($aDataEntry),
        );
        return $this->oTemplate->parseHtmlByName('latest_featured_product', $aVars);
    }

    function getBlockCode_Recent()
    {
        return $this->ajaxBrowse('recent', $this->oDb->getParam('bx_store_perpage_main_recent'));
    }
}
