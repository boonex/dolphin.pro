<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolVoting');

/**
 * @see BxDolVoting
 */
class BxBaseVotingView extends BxDolVoting
{
    var $_iSizeStarBigX = 18;
    var $_iSizeStarBigY = 18;
    var $_iSizeStarSmallX = 12;
    var $_iSizeStarSmallY = 12;

    function __construct( $sSystem, $iId, $iInit = 1 )
    {
        parent::__construct( $sSystem, $iId, $iInit );
    }

    function getSmallVoting($iCanRate = 1, $iRateOverride = false)
    {
        if($iCanRate != 0 && !$this->checkAction())
			$iCanRate = 0;

        return $this->getVoting('small', 0, array(
        	'show_count' => true,
        	'can_rate' => $iCanRate,
        	'override_rate' => $iRateOverride
        ));
    }

    function getBigVoting($iCanRate = 1, $iRateOverride = false)
    {
        if($iCanRate != 0 && !$this->checkAction())
			$iCanRate = 0;

        return $this->getVoting('big', 0, array(
        	'show_count' => true,
        	'can_rate' => $iCanRate,
        	'override_rate' => $iRateOverride
        ));
    }

    function getJustVotingElement($iCanRate = 1, $iObjectId = 0, $iRateOverride = false)
    {
    	return $this->getVoting('small', $iObjectId, array(
        	'show_count' => false,
    		'can_rate' => $iCanRate,
        	'override_rate' => $iRateOverride
        ));
    }

    function getVoting($sType, $iObjectId = 0, $aParams = array())
    {
    	$iMax = $this->getMaxVote();
    	list($iSizeX, $iSizeY) = $this->_getSizeByType($sType);
        $iWidth = $iSizeX * $iMax;

        if(empty($iObjectId))
        	$iObjectId = $this->getId();

        $sHtmlId = $this->_sSystem . '_voting_' . $sType . '_' . $iObjectId;
        $sJsObject = bx_gen_method_name($sHtmlId);

        $sRet = '<div class="votes_'.$sType.'" id="' . $sHtmlId . '">';

        $iCanRate = !empty($aParams['can_rate']);
        if ($iCanRate) {
        	$aJsParams = array(
				'sSystem' => $this->_sSystem,
        		'iObjId' => $iObjectId,
	        	'sBaseUrl' => BX_DOL_URL_ROOT,
	        	'iSize' => $iSizeX,
	        	'iMax' => $iMax,
        		'sHtmlId' => $sHtmlId,
        	);
            $sRet .= $GLOBALS['oSysTemplate']->_wrapInTagJsCode('var ' . $sJsObject . ' = new BxDolVoting(' . json_encode($aJsParams) . ');');
        }

        $sRet .= '<div class="votes_gray_'.$sType.'" style="width:' . $iWidth . 'px;">';

        // clickable/hoverable vote buttons
        if ($iCanRate) {
            $sRet .= '<div class="votes_buttons">';
            for ($i=1 ; $i<=$iMax ; ++$i)
                $sRet .= '<a href="javascript:void(0);" onmouseover="' . $sJsObject . '.over(' . $i . ');" onmouseout="' . $sJsObject . '.out();" onclick="' . $sJsObject . '.vote(' . $i . ')"><i class="votes_button_' . $sType . ' sys-icon">&#61446;</i></a>';
            $sRet .= '</div>';
        }

        // gray stars
        $sRet .= '<div class="votes_gray_'.$sType.'" style="width:' . $iWidth . 'px;">';
        for ($i=1 ; $i<=$iMax ; ++$i)
            $sRet .= '<i class="votes_button_'.$sType.' sys-icon star-o"></i>';
        $sRet .= '</div>';

        // active stars
        $iVoteRate = !empty($aParams['override_rate']) ? (int)$aParams['override_rate'] : $this->getVoteRate();
        $sRet .= '<div class="votes_slider votes_active_'.$sType.'" style="width:' . round($iVoteRate * ($iMax ? $iWidth / $iMax : 0)) . 'px;">';
        for ($i=1 ; $i<=$iMax ; ++$i)
            $sRet .= '<i class="votes_button_'.$sType.' sys-icon star"></i>';
        $sRet .= '</div>';

        $sRet .= '</div>';

        // vot count
        if (!empty($aParams['show_count']))
            $sRet .= '<span class="votes_count">' . _t('_n_votes', $this->getVoteCount()) . '</span>';

        $sRet .= '<div class="clear_both"></div>';
        $sRet .= '</div>';

        return $sRet;
    }

    function getExtraJs ()
    {
        $GLOBALS['oSysTemplate']->addJs('BxDolVoting.js');
    }

    protected function _getSizeByType($sType)
    {
    	$aResult = array();

    	switch($sType) {
    		case 'small':
    			$aResult = array($this->_iSizeStarSmallX, $this->_iSizeStarSmallY);
    			break;

    		case 'big':
    			$aResult = array($this->_iSizeStarBigX, $this->_iSizeStarBigY);
    			break;
    	}

    	return $aResult;
    }
}
