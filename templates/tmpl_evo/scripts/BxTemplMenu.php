<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxBaseMenu');

/**
* @see BxBaseMenu;
*/
class BxTemplMenu extends BxBaseMenu
{
	var $aProfileOwnerSubmenu;

    /**
    * Class constructor;
    */
    function BxTemplMenu()
    {
        parent::BxBaseMenu();
    }
    function setCustomSubActions(&$aKeys, $sActionsType, $bSubMenuMode = true)
    {
    	parent::setCustomSubActions($aKeys, $sActionsType, $bSubMenuMode);

    	$this->sCustomActions = $GLOBALS['oSysTemplate']->parseHtmlByContent($this->sCustomActions, array(
    		'popup' => $GLOBALS['oFunctions']->transBox(
    			$GLOBALS['oSysTemplate']->parseHtmlByName('share_popup.html', array())
    		)
    	));
    }
    function genTopSubitems($iItemID)
    {
    	return '';
    }
    function genSubItems($iTItemID = 0)
    {
    	$sSubItems = parent::genSubItems($iTItemID);
    	if(empty($sSubItems))
    		return '';

    	$iSelected = (int)$this->aMenuInfo['currentCustom'] > 0 ? (int)$this->aMenuInfo['currentCustom'] : $this->getSubItemFirst($this->aMenuInfo['currentTop']);
    	$aSelected = $this->aTopMenu[$iSelected];

    	return $GLOBALS['oSysTemplate']->parseHtmlByName('navigation_menu_sub_header_submenu.html', array(
    		'link' => $this->replaceMetas($aSelected['Link']),
    		'onclick' => 'javascript:return oBxEvoTopMenu.showSubmenuSubmenu(this);',
    		'caption' => _t($aSelected['Caption']),
    		'submenu' => $sSubItems
    	));
    }

    function getSubItemFirst($iTItemID = 0)
    {
    	$iResult = 0;
    	foreach( $this->aTopMenu as $iItemID => $aItem ) {
            if( $aItem['Type'] != 'custom' )
                continue;
            if( $aItem['Parent'] != $iTItemID )
                continue;
            if( !$this->checkToShow( $aItem ) )
                continue;

			$iResult = $iItemID;
			break;
    	}

    	return $iResult;
    }

    function genSubHeaderCaption($aItem, $sCaption, $sTemplateFile = 'navigation_menu_sub_header_caption.html')
    {
    	return '';
    }

    function genSubHeaderLogin($sTemplateFile = 'login_join.html')
    {
    	$sContent = parent::genSubHeaderLogin($sTemplateFile);

    	return $GLOBALS['oSysTemplate']->parseHtmlByContent($sContent, array(
    		'popup' => $GLOBALS['oFunctions']->transBox(
    			$GLOBALS['oSysTemplate']->parseHtmlByName('share_popup.html', array())
    		)
    	)); 
    }

    function GenMoreElementBegin()
    {
        return $GLOBALS['oSysTemplate']->parseHtmlByName('navigation_menu_mm_item.html', array(
        	'link' => 'javascript:void(0)',
        	'bx_if:show_active' => array(
        		'condition' => false,
        		'content' => array()
        	),
        	'bx_if:show_onclick' => array(
        		'condition' => true,
        		'content' => array(
        			'onclick' => "$(this).parents('td.top:first').hide().siblings('td.top:hidden').show();"
        		)
        	),
        	'bx_if:show_target' => array(
        		'condition' => false,
        		'content' => array()
        	),
        	'bx_if:show_style' => array(
        		'condition' => false,
        		'content' => array()
        	),
        	'bx_if:show_picture' => array(
        		'condition' => false,
        		'content' => array()
        	),
        	'text' => _t('_sys_top_menu_more'),
        	'sub_menus' => ''
        ));
    }

    function GenMoreElementEnd()
    {
        return "";
    }
}

// Creating template navigation menu class instance
$oTopMenu = new BxTemplMenu();
