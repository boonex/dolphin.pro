<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSocialSharing');

/**
 * @see BxDolSocialSharing
 */
class BxBaseSocialSharing extends BxDolSocialSharing
{
    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }

    public function getCode ($sUrl, $sTitle, $aCustomVars = false)
    {
        $this->_addOpenGraphInfo($sTitle, isset($aCustomVars['img_url']) ? $aCustomVars['img_url'] : '');

        $aLang = bx_lang_info();

        // define markers for replacments
        $aMarkers = array (
            'url' => $sUrl,
            'url_encoded' => rawurlencode($sUrl),
            'lang' => $GLOBALS['sCurrentLanguage'],
            'locale' => $this->_getLocaleFacebook($aLang['LanguageCountry']),
            'twit' => _t('_sys_social_sharing_twit'),
            'title' => $sTitle,
            'title_encoded' => rawurlencode($sTitle),
        );

        if (!empty($aCustomVars) && is_array($aCustomVars))
            $aMarkers = array_merge($aMarkers, $aCustomVars);

        // alert
        $sOverrideOutput = null;
        bx_import('BxDolAlerts');
        $oAlert = new BxDolAlerts('system', 'social_sharing_display', '', '', array (
            'buttons' => &$this->_aSocialButtons,
            'markers' => &$aMarkers,
            'override_output' => &$sOverrideOutput,
        ));
        $oAlert->alert();

        // return custom code if there is one
        if ($sOverrideOutput)
            return $sOverrideOutput;

        // return empty string of there is no buttons
        if (empty($this->_aSocialButtons))
            return '';

        // prepare buttons
        $aButtons = array();
        foreach ($this->_aSocialButtons as $aButton) {
            $sButton = $this->_replaceMarkers($aButton['content'], $aMarkers);
            if (preg_match('/{[A-Za-z0-9_]+}/', $sButton)) // if not all markers are replaced skip it
                continue;
            $aButtons[] = array ('button' => $sButton);
        }

        // output
        $aTemplateVars = array (
            'bx_repeat:buttons' => $aButtons,
        );
        return $GLOBALS['oSysTemplate']->parseHtmlByName('social_sharing.html', $aTemplateVars);
    }

    protected function _addOpenGraphInfo($sTitle, $sImageUrl = '') 
    {
        $GLOBALS['oSysTemplate']->setOpenGraphInfo(array('title' => $sTitle));
        if ($sImageUrl)
            $GLOBALS['oSysTemplate']->setOpenGraphInfo(array('image' => $sImageUrl));
    }
}
