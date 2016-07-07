<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxDolTextPageMain extends BxDolPageView
{
    var $_sPageName;
    var $_oObject;

    function __construct($sPageName, &$oObject)
    {
        parent::__construct($sPageName);

        $this->_oObject = $oObject;
    }
    function getBlockCode_Featured()
    {
        return array($this->_oObject->serviceFeaturedBlock(), array(), array(), true);
    }
    function getBlockCode_Latest()
    {
        $sUri = $this->_oObject->_oConfig->getUri();
        $sBaseUri = $this->_oObject->_oConfig->getBaseUri();
        $aTopMenu = array(
            'get-rss' => array('href' => BX_DOL_URL_ROOT . $sBaseUri . 'act_rss/', 'target' => '_blank', 'title' => _t('_' . $sUri . '_get_rss'), 'icon' => 'rss'),
        );

        return array($this->_oObject->serviceArchiveBlock(), $aTopMenu, array(), true, 'getBlockCaptionMenu');
    }
    function getBlockCode_Categories($iBlockId)
    {
        return array($this->_oObject->serviceCategoriesBlock($iBlockId), array(), array(), true);
    }
    function getBlockCode_Tags($iBlockId)
    {
        return array($this->_oObject->serviceTagsBlock($iBlockId), array(), array(), true);
    }
    function getBlockCode_Calendar($iBlockId)
    {
        return array($this->_oObject->serviceGetCalendarBlock($iBlockId, array('mini_mode' => true)), array(), array(), true);
    }
}
