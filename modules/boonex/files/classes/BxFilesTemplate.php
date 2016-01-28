<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolFilesTemplate');
bx_import('BxTemplVotingView');

class BxFilesTemplate extends BxDolFilesTemplate
{
    /**
     * Constructor
     */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    function getFileViewArea ($aInfo)
    {
    }

    function getBasicFileInfoForm (&$aInfo, $sUrlPref = '')
    {
        $aForm = parent::getBasicFileInfoForm($aInfo, $sUrlPref);

        if(!empty($aInfo['albumCaption']) && !empty($aInfo['albumUri']))
            $aForm['album'] = array(
                'type' => 'value',
                'value' => getLink($aInfo['albumCaption'], $sUrlPref . 'browse/album/' . $aInfo['albumUri'] . '/owner/' . getUsername($aInfo['medProfId'])),
                'caption' => _t('_bx_files_album')
            );

        return $aForm;
    }
}
