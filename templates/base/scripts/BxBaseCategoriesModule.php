<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');
bx_import('BxBaseCategories');

class BxBaseCategoriesModule extends BxDolPageView
{
    var $_sPage;
    var $_sTitle;
    var $_sUrl;
    var $_aParam;

    function __construct($aParam, $sTitle, $sUrl)
    {
        $this->_sPage = 'categ_module';
        $this->_sTitle = $sTitle ? $sTitle : _t('_categ_users');
        $this->_sUrl = $sUrl;
        $this->_aParam = $aParam;
        parent::__construct($this->_sPage);
    }

    function getBlockCode_Common($iBlockId, $isDisableOrderPanel = false)
    {
        $oCateg = new BxBaseCategories();
        $oCateg->getTagObjectConfig();
        $aParam = array(
            'type' => $this->_aParam['type'],
            'common' => true
        );

        return $oCateg->display($aParam, $iBlockId, '', !(boolean)$isDisableOrderPanel, 1, $this->_sUrl);
    }

    function getBlockCode_All($iBlockId)
    {
        $oCateg = new BxBaseCategories();
        $oCateg->getTagObjectConfig();
        $this->_aParam['common'] = false;

        return array(
            $oCateg->display($this->_aParam, $iBlockId, '', true, getParam('categ_show_columns'), $this->_sUrl),
            array(),
            array(),
            $this->_sTitle
        );
    }
}
