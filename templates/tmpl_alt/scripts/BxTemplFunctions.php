<?php
    require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseFunctions.php' );

    class BxTemplFunctions extends BxBaseFunctions
    {
        /**
         * class constructor
        */
        function BxTemplFunctions()
        {
            parent::BxBaseFunctions();
        }
    }
    $oFunctions = new BxTemplFunctions();
