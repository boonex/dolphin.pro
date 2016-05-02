<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolModuleTemplate');

    class BxPollTemplate extends BxDolModuleTemplate
    {
        /**
         * Constructor
         */
        function __construct(&$oConfig, &$oDb)
        {
            parent::__construct($oConfig, $oDb);
        }

        // function of output
        function pageCode ($aPage = array(), $aPageCont = array(), $aCss = array())
        {
            if (!empty($aPage)) {
                foreach ($aPage as $sKey => $sValue)
                    $GLOBALS['_page'][$sKey] = $sValue;
            }
            if (!empty($aPageCont)) {
                foreach ($aPageCont as $sKey => $sValue)
                    $GLOBALS['_page_cont'][$aPage['name_index']][$sKey] = $sValue;
            }
            if (!empty($aCss))
                $this->addCss($aCss);

            PageCode($this);
        }

        function adminBlock ($sContent, $sTitle, $aMenu = array())
        {
            return DesignBoxAdmin($sTitle, $sContent, $aMenu);
        }

        function pageCodeAdminStart()
        {
            ob_start();
        }

        function pageCodeAdmin ($sTitle)
        {
            global $_page;
            global $_page_cont;

            $_page['name_index'] = 9;

            $_page['header'] = $sTitle ? $sTitle : $GLOBALS['site']['title'];
            $_page['header_text'] = $sTitle;

            $_page_cont[$_page['name_index']]['page_main_code'] = ob_get_clean();

            PageCodeAdmin();
        }

        function defaultPage($sTitle, $sContent, $iPageIndex = 7)
        {
            global $_page;
            global $_page_cont;

            $_page['name_index'] = $iPageIndex;

            $_page['header'] = $sTitle ? $sTitle : $GLOBALS['site']['title'];
            $_page['header_text'] = $sTitle;

            $_page_cont[$_page['name_index']]['page_main_code'] = $sContent;

            PageCode();
        }
    }
