<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_ROOT . 'templates/base/scripts/BxBaseBrowse.php');

    class BxTemplBrowse extends BxBaseBrowse
    {
        /**
         * Class constructor ;
         *
         * @param 		: $aFilteredSettings (array) ;
         * 					: 	sex (string) - set filter by sex,
         *					: 	age (string) - set filter by age,
         *					: 	country (string) - set filter by country,
         *					: 	photos_only (string) - set filter 'with photo only',
         *					: 	online_only (string) - set filter 'online only',
         * @param		: $aDisplaySettings (array) ;
         * 					: page (integer) - current page,
         * 					: per_page (integer) - number ellements for per page,
         * 					: sort (string) - sort parameters for SQL instructions,
         * 					: mode (mode) - switch mode to extended and simple,
         * @param		: $sPageName (string) - need for page builder ;
         */
        function __construct( &$aFilteredSettings, &$aDisplaySettings, $sPageName )
        {
            // call the parent constructor ;
            parent::__construct( $aFilteredSettings, $aDisplaySettings, $sPageName );
        }
    }
