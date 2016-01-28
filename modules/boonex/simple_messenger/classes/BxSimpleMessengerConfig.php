<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolConfig.php');

    class BxSimpleMessengerConfig extends BxDolConfig
    {
        // contain Db table's name ;
        var $sTablePrefix;
        var $iUpdateTime;
        var $iVisibleMessages;
        var $iCountRetMessages;
        var $iCountAllowedChatBoxes;
        var $sOutputBlock;
        var $sOutputBlockPrefix;
        var $bSaveChatHistory;
        var $iBlinkCounter;
        var $sMessageDateFormat;

        /**
         * Class constructor;
         */
        function __construct( $aModule )
        {
            parent::__construct($aModule);

            // define the tables prefix ;
            $this -> sTablePrefix = $this -> getDbPrefix();

            // time (in seconds) script checks for messages ;
            $this -> iUpdateTime       = getParam('simple_messenger_update_time');

            // number of visible messages into chat box ;
            $this -> iVisibleMessages  = getParam('simple_messenger_visible_messages');

            // limit of returning messages in message box;
            $this -> iCountRetMessages = 10;

            // flashing signals amount of the non-active window ;
            $this -> iBlinkCounter = getParam('simple_messenger_blink_counter');

            // save messenger's chat history ;
            $this -> bSaveChatHistory = false;

            // contains block's id where the list of messages will be generated ;
            $this -> sOutputBlock = 'extra_area';

            // contain history block's prefix (need for defines the last message);
            $this -> sOutputBlockPrefix = 'messages_history_';

            // number of allowed chat boxes;
            $this -> iCountAllowedChatBoxes  = getParam('simple_messenger_allowed_chatbox');

            $this -> sMessageDateFormat = getLocaleFormat(BX_DOL_LOCALE_DATE, BX_DOL_LOCALE_DB);
        }
    }
