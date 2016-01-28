<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolModuleTemplate');

    class BxSpyTemplate extends BxDolModuleTemplate
    {
        /**
         * Class constructor
         */
        function __construct(&$oConfig, &$oDb)
        {
            parent::__construct($oConfig, $oDb);
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
         * Get spy wrapper code
         *
         * @param $sWrapperId string
         * @param $sCode string
         * @param $sPagination string
         * @return string - html presentation data
         */
        function getWrapper($sWrapperId, $sCode, $sPagination = '')
        {
            return  '<div id="' . $sWrapperId . '" class="bx-def-bc-margin">' . $sCode . '</div>'  . $sPagination;
        }

        /**
         * get stop notification code
         *
         * @return text
         */
        function getStopNotificationCode()
        {
            return '<script type="text/javascript">if( typeof oSpy != \'undefined\') {oSpy.stopActivity();}</script>';
        }
    }
