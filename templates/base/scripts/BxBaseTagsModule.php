<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');
bx_import('BxTemplTags');

class BxBaseTagsModule extends BxDolPageView
{
    var $_sPage;
    var $_sTitle;
    var $_sUrl;
    var $_aParam;

    function __construct($aParam, $sTitle, $sUrl)
    {
        $this->_sPage = 'tags_module';
        $this->_sTitle = $sTitle ? $sTitle : _t('_all_tags');
        $this->_sUrl = $sUrl;
        $this->_aParam = $aParam;
        parent::__construct($this->_sPage);
    }

    function getBlockCode_Recent($iBlockId)
    {
        $oTags = new BxTemplTags();
        $oTags->getTagObjectConfig();

        return $oTags->display(
            array(
                'type' => $this->_aParam['type'],
                'orderby' => 'recent',
                'limit' => getParam('tags_show_limit')
            ),
            $iBlockId, '', $this->_sUrl
        );
    }

    function getBlockCode_All($iBlockId)
    {
        $oTags = new BxTemplTags();
        $oTags->getTagObjectConfig();

        if (!isset($this->_aParam['pagination']))
            $this->_aParam['pagination'] = getParam('tags_perpage_browse');

        return array(
            $oTags->display($this->_aParam, $iBlockId, '', $this->_sUrl),
            array(),
            array(),
            $this->_sTitle
        );
    }
}
