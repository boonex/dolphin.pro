<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolModule');
bx_import('BxTemplVotingView');

class BxWallVoting extends BxTemplVotingView
{
	var $_oModule;

    function BxWallVoting($sSystem, $iId, $iInit = 1)
    {
        parent::BxTemplVotingView($sSystem, $iId, $iInit);

        $this->_oModule = BxDolModule::getInstance('BxWallModule');
    }

    function getVotingElement($bCount = true)
    {
    	if(!$this->isVotingAllowed())
    		return '';

    	$sName = $this->getSystemName();
    	$iObjId = $this->getId();

		$sHtmlId = $sName . '_like' . $iObjId;
		$sHtmlIdSlider = $sHtmlId . '_slider' . $iObjId;
		$sJsObject = $this->_oModule->_oConfig->getJsObject('voting') . $this->_toName($sHtmlId);

    	return $this->_oModule->_oTemplate->parseHtmlByName('voting.html', array(
    		'html_id' => $sHtmlId,
    		'html_id_slider' => $sHtmlIdSlider,
    		'js_object' => $sJsObject,
    		'name' => $sName,
    		'object_id' => $iObjId,
    		'size_x' => $this->_iSizeStarSmallX,
    		'max' => $this->getMaxVote(),
    		'bx_if:show_count' => array(
    			'condition' => $bCount,
    			'content' => array(
    				'count' => $this->getVoteCount()
    			)
    		)
    	));
    }

	function isVotingAllowed($isPerformAction = false)
    {
        return $this->checkAction($isPerformAction);
    }

    protected function _toName($s)
    {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $s)));
    }
}
