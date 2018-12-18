<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxTemplSearchResultText');

class BxDolTextSearchResult extends BxTemplSearchResultText
{
    var $aCurrent = array(
        'name' => '',
        'title' => '',
        'table' => '',
        'ownFields' => array('uri'),
        'searchFields' => array('caption', 'content', 'tags', 'categories'),
        'restriction' => array(
            'active1' => array('value' => '1', 'field' => 'status', 'operator' => '<>'),
            'active2' => array('value' => '2', 'field' => 'status', 'operator' => '<>'),
            'caption' => array('value' => '', 'field' => 'caption', 'operator' => 'like'),
            'content' => array('value' => '', 'field' => 'content', 'operator' => 'like'),
            'tag' => array('value' => '', 'field' => 'tags', 'operator' => 'against'),
            'category' => array('value' => '', 'field' => 'categories', 'operator' => 'against')
        ),
        'paginate' => array('perPage' => 4, 'page' => 1, 'totalNum' => 10, 'totalPages' => 1),
        'sorting' => 'last'
    );

    var $_oModule;

    function __construct(&$oModule)
    {
        parent::__construct();

        $this->_oModule = $oModule;

        $this->aCurrent['name'] = $this->_oModule->_oConfig->getSearchSystemName();
        $this->aCurrent['title'] = '_' . $this->_oModule->_oConfig->getUri() . '_lcaption_search_object';
        $this->aCurrent['table'] = $this->_oModule->_oDb->getPrefix() . 'entries';
    }

    function displaySearchUnit($aData)
    {
        $aEntries = $this->_oModule->_oDb->getEntries(array(
            'sample_type' => 'search_unit',
            'uri' => $aData['uri']
        ));
        $aEvent = array_shift($aEntries);

        $aParams = array(
            'sample_type' => 'search_unit',
            'viewer_type' => $this->_oModule->_oTextData->getViewerType()
        );
        return $this->_oModule->_oTemplate->displayItem($aParams, $aEvent);
    }

    function displayResultBlock()
    {
        $sResult = parent::displayResultBlock();

        $sModuleUri = $this->_oModule->_oConfig->getUri();
        if($this->aCurrent['paginate']['totalNum'] == 0)
            $sResult = MsgBox(_t('_' . $sModuleUri . '_msg_no_results'));

        return $this->_oModule->_oTemplate->parseHtmlByName('default_margin.html', array('content' => $sResult));
    }

    function addCustomParts()
    {
        parent::addCustomParts();

        $this->_oModule->_oTemplate->addCss(array('view.css'));
    }

    function getAlterOrder()
    {
        return array('order' => 'ORDER BY `when` DESC');
    }
}
