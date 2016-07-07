<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxAdsPageHome extends BxDolPageView
{
    var $oModule;
    function __construct($oModule)
    {
        parent::__construct('ads_home');
        $this->oModule = $oModule;
    }

    function getBlockCode_last()
    {
        return $this->oModule->GenAllAds('last', true);
    }

    function getBlockCode_featured()
    {
        bx_import('SearchUnit', $this->oModule->_aModule);
        $oTmpAdsSearch = new BxAdsSearchUnit();
        $oTmpAdsSearch->sSelectedUnit = 'ad_of_day';
        $oTmpAdsSearch->aCurrent['paginate']['forcePage'] = 1;
        $oTmpAdsSearch->aCurrent['paginate']['perPage'] = 1;
        $oTmpAdsSearch->aCurrent['restriction']['featuredStatus']['value'] = 1;
        $sTopAdOfAllDayValue = $oTmpAdsSearch->displayResultBlock();
        return $oTmpAdsSearch->aCurrent['paginate']['totalNum'] > 0 ? $sTopAdOfAllDayValue : '';
    }

    function getBlockCode_categories()
    {
        return $this->oModule->genCategoriesBlock();
    }
}
