<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxChatPlusPage extends BxDolPageView
{
    function __construct($sPageName)
    {
        parent::__construct($sPageName);
    }

    function getChatBlockMenu($iBlockID, $aMenu)
    {
        if (!$aMenu || !($oModule = BxDolModule::getInstance('BxChatPlusModule')))
            return '';

        reset($aMenu);
        $sTitle = key($aMenu);
        $a = current($aMenu);
        
        return $oModule->_oTemplate->parseHtmlByName('chat_block_menu.html', array(
            'block_id' => $iBlockID,
            'href' => $a['href'],
            'target' => isset($a['target']) ? $a['target'] : '',
            'title' => $sTitle,
        ));
    }
}
