<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxBaseMenuService');

/**
* @see BxBaseMenuService;
*/
class BxTemplMenuService extends BxBaseMenuService
{
    /**
    * Class constructor;
    */
    function __construct()
    {
        parent::__construct();
    }

	function getItems()
	{
		$sContent = parent::getItems();

		return $GLOBALS['oSysTemplate']->parseHtmlByContent($sContent, array(
			'bx_if:show_profile_link' => array(
				'condition' => $this->aMenuInfo['memberID'] != 0,
				'content' => array(
					'link' => getProfileLink($this->aMenuInfo['memberID']),
					'title' => getNickName($this->aMenuInfo['memberID'])
				)
			)
		));
	}
}
