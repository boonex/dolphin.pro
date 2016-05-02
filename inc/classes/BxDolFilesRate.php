<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolRate');

class BxDolFilesRate extends BxDolRate
{
    var $oMedia;
    var $oConfig;

    function __construct($sType, &$oMedia)
    {
        parent::__construct($sType);

        $this->oMedia = $oMedia;
        $this->oMedia->aCurrent['restriction']['allow_view']['value'] = $this->oMedia->oModule->_checkVisible();

        $this->oConfig = $this->oMedia->oModule->_oConfig;

        $sMainPrefix = $this->oConfig->getMainPrefix();

        $this->aPageInfo = array(
            'header' => '_' . $sMainPrefix . '_rate_header',
            'header_text' => '_' . $sMainPrefix . '_rate_header_text',
        );
        $this->oMedia->oTemplate->addCss('rate_object.css');
    }

    function getRateObject ()
    {
        $aVotedItems = $this->getVotedItems();
        $this->oMedia->clearFilters(array('activeStatus', 'allow_view', 'album_status', 'albumType'), array('albumsObjects', 'albums'));
        $this->oMedia->aCurrent['restriction']['id'] = array(
            'value' => $aVotedItems,
            'field' => 'ID',
            'operator' => 'not in'
        );
        $this->oMedia->aCurrent['paginate']['perPage'] = 1;
        $this->oMedia->aCurrent['sorting'] = 'rand';
        $aData = $this->oMedia->getSearchData();
        return $aData;
    }

    function getBlockCode_RatedSet ()
    {
        $sMainPrefix = $this->oConfig->getMainPrefix();

        $this->oMedia->clearFilters(array('activeStatus', 'allow_view', 'album_status', 'albumType'), array('albumsObjects', 'albums'));
        $this->oMedia->aCurrent['join']['rateTrack'] = array(
            'type' => 'inner',
            'table' => $sMainPrefix . '_voting_track',
            'mainField' => 'ID',
            'onField' => 'gal_id',
            'joinFields' => array('gal_ip', 'gal_date')
        );

        $this->oMedia->aCurrent['paginate']['perPage'] = $this->oConfig->getGlParam('number_previous_rated');
        $this->oMedia->aCurrent['sorting'] = 'voteTime';
        $sIp = getVisitorIP();
        $this->oMedia->aCurrent['restriction']['ip'] = array(
            'value' => $sIp,
            'field' => 'gal_ip',
            'table' => $sMainPrefix . '_voting_track',
            'operator' => '='
        );
        $this->oMedia->sTemplUnit = 'browse_unit_rater';
        $sCode = $this->oMedia->displayResultBlock();
        if (!$this->oMedia->aCurrent['paginate']['totalNum'])
            $sCode = MsgBox(_t("_Empty"));

        return array($sCode, array(), array(), false);
    }

    function getBlockCode_RateObject ()
    {
        $sMainPrefix = $this->oConfig->getMainPrefix();

        $this->oMedia->oModule->_defineActions();
        $aCheck = checkAction($this->iViewer, $this->oMedia->oModule->_defineActionName('view'));
        if ($aCheck[CHECK_ACTION_RESULT] != CHECK_ACTION_RESULT_ALLOWED)
            $sCode = MsgBox(_t('_' . $sMainPrefix . '_forbidden'));
        else {
            $aData = $this->getRateObject();
            if(count($aData) > 0) {
                $oVotingView = new BxTemplVotingView ($this->sType, $aData[0]['id']);

                $aUnit = array(
                    'url' => BX_DOL_URL_ROOT . $this->oConfig->getBaseUri() . 'rate',
                    'fileBody' => $this->getRateFile($aData),
                    'ratePart'  => $oVotingView->isEnabled() ? $oVotingView->getBigVoting(): '',
                    'fileTitle' => $aData[0]['title'],
                    'fileUri' => $this->oMedia->getCurrentUrl('file', $aData[0]['id'], $aData[0]['uri']),
                    'fileWhen'  => defineTimeInterval($aData[0]['date']),
                    'fileFrom'  => getNickName($aData[0]['ownerId']),
                    'fileFromLink' => getProfileLink($aData[0]['ownerId']),
                );
                $sCode = $this->oMedia->oTemplate->parseHtmlByName('rate_object.html', $aUnit);
                checkAction($this->iViewer, $this->oMedia->oModule->_defineActionName('view'), true);
            } else
                $sCode = MsgBox(_t('_' . $sMainPrefix . '_no_file_for_rate'));
        }

        return array($sCode, array(), array(), false);
    }
}
