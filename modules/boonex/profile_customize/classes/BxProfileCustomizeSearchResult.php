<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxTemplSearchResult');

class BxProfileCustomizeSearchResult extends BxTemplSearchResult
{
    var $aCurrent = array(
        'name' => 'bx_profile_customize',
        'title' => '_bx_profile_customize',
        'table' => 'bx_profile_custom_units',
        'ownFields' => array('id', 'name', 'caption', 'css_name', 'type'),
        'searchFields' => array(),
        'restriction' => array(
            'type' => array('value' => '', 'field' => 'type', 'operator' => '='),
        ),
        'ident' => 'id'
    );
    var $aPermalinks;

    var $_oModule;
    var $_sType;

    function __construct($sType, $oModule = null)
    {
        parent::__construct();

        if(!empty($oModule))
            $this->_oModule = $oModule;
        else
            $this->_oModule = &BxDolModule::getInstance('BxProfileCustomizeModule');

        $this->aCurrent['restriction']['type']['value'] = $sType;
        $this->_sType = $sType;
    }

    function displaySearchUnit($aData)
    {
        return $this->_oModule->_oTemplate->parseHtmlByName('admin_unit.html', array(
            'caption' => $aData['caption'],
            'value' => $aData['id'],
            'edit_url' => BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'administration/' . $this->_sType . '/' . $aData['id'],
            'edit_str' => _t('_bx_profile_customize_edit')
        ));
    }

    function displayResultBlock()
    {
        $sResult = parent::displayResultBlock();

        return $sResult;
    }

    function getAlterOrder ()
    {
        return array(
            'order' => " ORDER BY `id`"
        );
    }
}
