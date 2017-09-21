<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesTemplate');

class BxSoundsTemplate extends BxDolFilesTemplate
{
    function __construct (&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    function getFileConcept ($iFileId, $aExtra = array())
    {
        $sOverride = false;
        $oAlert = new BxDolAlerts($this->_oConfig->getMainPrefix(), 'display_player', $iFileId, getLoggedId(), array('extra' => $aExtra, 'override' => &$sOverride));
        $oAlert->alert();
        if ($sOverride)
            return $sOverride;

        $iFileId = (int)$iFileId;
        return '<div class="viewFile" style="width:100%">' . getApplicationContent('mp3', 'player', array('id' => $iFileId, 'user' => (int)$_COOKIE['memberID'], 'password' => clear_xss($_COOKIE['memberPassword'])), true) . '</div>';
    }

    function getViewFile (&$aInfo)
    {
        $oVotingView = new BxTemplVotingView('bx_' . $this->_oConfig->getUri(), $aInfo['medID']);
        $iWidth = (int)$this->_oConfig->getGlParam('file_width');
        if ($aInfo['prevItem'] > 0)
            $aPrev = $this->_oDb->getFileInfo(array('fileId'=>$aInfo['prevItem']), true, array('medUri', 'medTitle'));
        if ($aInfo['nextItem'] > 0)
            $aNext = $this->_oDb->getFileInfo(array('fileId'=>$aInfo['nextItem']), true, array('medUri', 'medTitle'));
        $aUnit = array(
            'file' => $this->getFileConcept($aInfo['medID']),
            'width' => $iWidth,
            'fileUrl' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aInfo['medUri'],
            'fileTitle' => $aInfo['medTitle'],
            'fileDescription' => nl2br($aInfo['medDesc']),
            'rate' => $oVotingView->isEnabled() ? $oVotingView->getBigVoting(1, $aInfo['Rate']): '',
            'favInfo' => isset($aInfo['favCount']) ? $aInfo['favCount'] : '',
            'viewInfo' => $aInfo['medViews'],
            'albumUri' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/album/' . $aInfo['albumUri'] . '/owner/' . $aInfo['NickName'],
            'albumCaption' => $aInfo['albumCaption'],
            'bx_if:prev' => array(
                'condition' => $aInfo['prevItem'] > 0,
                'content' => array(
                    'linkPrev'  => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aPrev['medUri'],
                    'titlePrev' => $aPrev['medTitle'],
                    'percent' => $aInfo['nextItem'] > 0 ? 50 : 100,
                )
            ),
            'bx_if:next' => array(
                'condition' => $aInfo['nextItem'] > 0,
                'content' => array(
                    'linkNext'  => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aNext['medUri'],
                    'titleNext' => $aNext['medTitle'],
                    'percent' => $aInfo['prevItem'] > 0 ? 50 : 100,
                )
            ),
        );
        return $sCode = $this->parseHtmlByName('view_unit.html', $aUnit);
    }

    function getCompleteFileInfoForm (&$aInfo, $sUrlPref = '')
    {
        $aMain = $this->getBasicFileInfoForm($aInfo, $sUrlPref);
        if ($aInfo['AllowAlbumView'] == BX_DOL_PG_ALL)
        {
            $aAdd = array(
                'embed' => array(
                    'type' => 'text',
                    'value' => $this->getEmbedCode($aInfo['medID']),
                    'attrs' => array(
                      'onclick' => 'this.focus(); this.select();',
                      'readonly' => 'readonly',
                    ),
                    'caption'=> _t('_Embed')
                ),
            );
            $aMain = array_merge($aMain, $aAdd);
        }
        return $aMain;
    }

    function getEmbedCode ($iFileId)
    {
        $sOverride = false;
        $oAlert = new BxDolAlerts($this->_oConfig->getMainPrefix(), 'embed_code', $iFileId, getLoggedId(), array('override' => &$sOverride));
        $oAlert->alert();
        if ($sOverride)
            return $sOverride;

        $iFileId = (int)$iFileId;
        return getEmbedCode('mp3', 'player', array('id'=>$iFileId));
    }
}
