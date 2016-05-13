<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_ROOT . 'templates/base/scripts/BxBaseMailBox.php');

    class BxTemplMailBox extends BxBaseMailBox
    {
        /**
         * Class constructor;
         *
         * @param		: $sPageName (string)  - page name (need for page builder);
         * @param		: $aMailBoxSettings (array)  - contain some necessary data ;
         * 					[] member_id	(integer)- logged member's ID;
         * 					[] recipient_id (integer) - message recipient's ID ;
         * 					[] mailbox_mode (string) - inbox, outbox or trash switcher mode ;
         * 					[] sort_mode (string) 	 - message sort mode;
         * 					[] page (integer) 	 	 - number of current page ;
         * 					[] per_page (integer) 	 - number of messages for per page ;
         * 					[] messages_types (string) - all needed types of messages ;
         * 					[] contacts_mode (string)  - type of contacts (friends, faves, contacted) ;
         * 					[] contacts_page (integer) - number of current contact's page ;
         * 					[] message_id	 (integer) - number of needed message ;
         */

        function __construct($sPageName, &$aMailBoxSettings )
        {
            // call the parent constructor ;
            parent::__construct($sPageName, $aMailBoxSettings);
        }
    }
