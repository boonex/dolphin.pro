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
        BxDolVoting::__construct( $sSystem, $iId, $iInit );
    }

    function getSmallVoting ($iCanRate = 1, $iVoteRateOverride = false)
    {
        if ($iCanRate != 0) {
            if (!$this->checkAction()) $iCanRate = 0;
        }
        return $this->getVoting($iCanRate, $this->_iSizeStarSmallX, $this->_iSizeStarSmallY, 'small', 0, true, $iVoteRateOverride);
    }

    function getManySmallVoting($iCanRate = 1, $iID = 0, $isShowCount = true, $iVoteRateOverride = false)
    {
        if ($iCanRate != 0) {
            if (!$this->checkAction()) $iCanRate = 0;
        }
        return $this->getVoting($iCanRate, $this->_iSizeStarSmallX, $this->_iSizeStarSmallY, 'small', $iID, $isShowCount, $iVoteRateOverride);
    }

    function getBigVoting ($iCanRate = 1, $iVoteRateOverride = false)
    {
        if ($iCanRate != 0) {
            if (!$this->checkAction()) $iCanRate = 0;
        }
        return $this->getVoting($iCanRate, $this->_iSizeStarBigX, $this->_iSizeStarBigY, 'big', 0, true, $iVoteRateOverride);
    }

    function getJustVotingElement($iCanRate, $iPossibleID = 0, $iVoteRateOverride = false)
    {
        return $this->getManySmallVoting($iCanRate, $iPossibleID, false, $iVoteRateOverride);
    }

    function getVoting($iCanRate, $iSizeX, $iSizeY, $sName, $iPossibleID = 0, $isShowCount = true, $iVoteRateOverride = false)
    {
        $iMax = $this->getMaxVote();
        $iWidth = $iSizeX * $iMax;
        $sSystemName = $this->getSystemName();
        $iObjId = $iPossibleID ? $iPossibleID : $this->getId();
        $sDivId = $this->getSystemName() . $sName;
        if ($iPossibleID > 0)
            $sDivId .= $iPossibleID;

        $sRet = '<div class="votes_'.$sName.'" id="' . $sDivId . '">';

        if ($iCanRate) {
            $sRet .= <<<EOF
<script language="javascript">
    var oVoting{$sDivId} = new BxDolVoting('{$GLOBALS['site']['url']}', '{$sSystemName}', '{$iObjId}', '{$sDivId}', '{$sDivId}Slider', {$iSizeX}, {$iMax});
</script>
EOF;
        }

        $sRet .= '<div class="votes_gray_'.$sName.'" style="width:' . $iWidth . 'px;">';

        // clickable/hoverable vote buttons
        if ($iCanRate) {
            $sRet .= '<div class="votes_buttons">';
            for ($i=1 ; $i<=$iMax ; ++$i)
                $sRet .= '<a href="javascript:'.$i.';void(0);" onmouseover="oVoting'.$sDivId.'.over('.$i.');" onmouseout="oVoting'.$sDivId.'.out();" onclick="oVoting'.$sDivId.'.vote('.$i.')"><i class="votes_button_'.$sName.' sys-icon">&#61446;</i></a>';
            $sRet .= '</div>';
        }
        $iVoteRate = (false === $iVoteRateOverride ? $this->getVoteRate() : $iVoteRateOverride);

        // gray stars
        $sRet .= '<div class="votes_gray_'.$sName.'" style="width:' . $iWidth . 'px;">';
        for ($i=1 ; $i<=$iMax ; ++$i)
            $sRet .= '<i class="votes_button_'.$sName.' sys-icon star-o"></i>';
        $sRet .= '</div>';

        // active stars
        $sRet .= '<div id="'.$sDivId.'Slider" class="votes_active_'.$sName.'" style="width:' . round($iVoteRate * ($iMax ? $iWidth / $iMax : 0)) . 'px;">';
        for ($i=1 ; $i<=$iMax ; ++$i)
            $sRet .= '<i class="votes_button_'.$sName.' sys-icon star"></i>';
        $sRet .= '</div>';

        $sRet .= '</div>';

        // vot count
        if ($isShowCount)
            $sRet .= '<b>'.$this->getVoteCount(). ' ' . _t('_votes') . '</b>';
        $sRet .= '<div class="clear_both"></div>';

        $sRet .= '</div>';

        return $sRet;
    }

    function getExtraJs ()
    {
        $GLOBALS['oSysTemplate']->addJs('BxDolVoting.js');
    }
}
