<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

   bx_import('BxDolModuleTemplate');

    class BxFaceBookConnectTemplate extends BxDolModuleTemplate
    {
        /**
         * Class constructor
         */
        function BxFaceBookConnectTemplate(&$oConfig, &$oDb)
        {
            parent::BxDolModuleTemplate($oConfig, $oDb);
        }

        function pageCodeAdminStart()
        {
            ob_start();
        }

        function adminBlock ($sContent, $sTitle, $aMenu = array())
        {
            return DesignBoxAdmin($sTitle, $sContent, $aMenu);
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

        /**
         * Function will include the js file ;
         *
         * @param  : $sName (string) - name of needed file ;
         * @return : (text) ;
         */
        function addJs($sName)
        {
            return '<script type="text/javascript" src="' . $this -> _oConfig -> getHomeUrl() . 'js/' . $sName . '" language="javascript"/></script>';
        }

        /**
         * Function will generate default dolphin's page;
         *
         * @param  : $sPageCaption   (string) - page's title;
         * @param  : $sPageContent   (string) - page's content;
         * @param  : $sPageIcon      (string) - page's icon;
         * @return : (text) html presentation data;
         */
        function getPage($sPageCaption, $sPageContent, $sPageIcon = 'facebook')
        {
            global $_page;
            global $_page_cont;

            $iIndex = 55;

            $_page['name_index']	= $iIndex;

            // set module's icon;
            $GLOBALS['oTopMenu'] -> setCustomSubIconUrl( false === strpos($sPageIcon, '.') ? $sPageIcon : $this -> getIconUrl($sPageIcon) );
            $GLOBALS['oTopMenu'] -> setCustomSubHeader($sPageCaption);

            $_page['header']        = $sPageCaption ;
            $_page['header_text']   = $sPageCaption ;
            $_page['css_name']      = 'face_book_connect.css';

            $_page_cont[$iIndex]['page_main_code'] = DesignBoxContent($sPageCaption, $sPageContent, 11);
            PageCode($this);
        }
    }
