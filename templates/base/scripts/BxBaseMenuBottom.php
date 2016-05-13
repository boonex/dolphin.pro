<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolMenuBottom');

    /**
     * @see BxDolMenuBottom;
     */
    class BxBaseMenuBottom extends BxDolMenuBottom
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
            $sContent .= $this->getSwitcherLanguage();
            $sContent .= $this->getSwitcherTemplate();
            return $sContent;
        }

        function getSwitcherLanguage()
        {
            $sContent = '';

            $iLangsCount = count(getLangsArr());
            if($iLangsCount <= 1)
                return '';

            $sLangName = getCurrentLangName();

            $aTmplVars = array();
            $aTmplVars[] = array(
                'caption' => _t('_sys_bm_language', $sLangName),
                'link' => 'javascript:void(0)',
                'script' => 'onclick="javascript:showPopupLanguage()"',
                'target' => ''
            );

            $sContent .= $GLOBALS['oSysTemplate']->parseHtmlByName('extra_' . $this->sName . '_menu.html', array('bx_repeat:items' => $aTmplVars));
            $sContent .= $GLOBALS['oFunctions']->getLanguageSwitcher($sLangName);

            return $sContent;
        }

        function getSwitcherTemplate()
        {
            $sContent = '';
            if(getParam('enable_template') != 'on')
                return $sContent;

            $iTmplsCount = count(get_templates_array());
            if($iTmplsCount <= 1)
                return $sContent;

            $sTemplName = $GLOBALS['oSysTemplate']->getCode();

            $aTmplVars = array();
            $aTmplVars[] = array(
                'caption' => _t('_sys_bm_design', $sTemplName),
                'link' => 'javascript:void(0)',
                'script' => 'onclick="javascript:showPopupTemplate()"',
                'target' => ''
            );

            $sContent .= $GLOBALS['oSysTemplate']->parseHtmlByName('extra_' . $this->sName . '_menu.html', array('bx_repeat:items' => $aTmplVars));
            $sContent .= $GLOBALS['oFunctions']->getTemplateSwitcher($sTemplName);

            return $sContent;
        }
    }
