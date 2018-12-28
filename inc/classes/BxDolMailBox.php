<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_INC . 'profiles.inc.php' );
    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php');

    define ('BX_MAILBOX_SEND_SUCCESS', 0);
    define ('BX_MAILBOX_SEND_FAILED', 1);
    define ('BX_MAILBOX_SEND_WAIT', 3);
    define ('BX_MAILBOX_SEND_BLOCKED', 5);
    define ('BX_MAILBOX_SEND_RECIPIENT_NOT_ACTIVE', 10);
    define ('BX_MAILBOX_SEND_UNKNOWN_RECIPIENT', 1000);
    define ('BX_MAILBOX_SEND_FAILED_MEMBERSHIP_DISALLOW', 1001);

    class BxDolMailBox extends BxDolPageView
    {
        // send message error code
        var $iSendMessageStatusCode;

        // contain count of all messages ;
        var $iTotalMessageCount;

        // contain count of all contacts ;
        var $iTotalContactsCount;

        // number of contacts rows for per page;
        var $iContactsPerPage = 8;

        // contain some necessary data ;
        var $aMailBoxSettings = array();

        // contain all available sorting parameters ;
        var $aSortCriterias = array();

        // contain all messag types ;
        var $aRegisteredMessageTypes = array();

        // contain all available types of contact;
        var $aRegisteredContactTypes = array();

        // define all available types of history list ;
        var $aRegisteredArchivesTypes = array();

        // contain all needed types of messages ;
        var $aReceivedMessagesTypes = array();

        // can write another message (in minuts)!
        var $iWaitMinutes  = 1;

        /**
         * Class constructor;
         *
         * @param          : $sPageName (string)  - page name (need for page builder);
         * @param          : $aMailBoxSettings (array)    - contain some necessary data ;
         *                          [] member_id     (integer)  - logged member's ID;
         *                          [] recipient_id (integer)  - message recipient's ID ;
         *                          [] mailbox_mode (string)   - inbox, outbox or trash switcher mode ;
         *                          [] sort_mode (string)         - message sort mode;
         *                          [] page (integer)               - number of current page ;
         *                          [] per_page (integer)         - number of messages for per page ;
         *                          [] messages_types (string) - all needed types of messages ;
         *                          [] contacts_mode (string)  - type of contacts (friends, faves, contacted) ;
         *                          [] contacts_page (integer) - number of current contact's page ;
         *                          [] messageID      (integer) - number of needed message ;
         */
         function __construct( $sPageName, &$aMailBoxSettings )
         {
             $sPageName = process_db_input($sPageName);

             $aMailBoxSettings['member_id'] 		= (int) $aMailBoxSettings['member_id'];
            $aMailBoxSettings['recipient_id']	= (int) $aMailBoxSettings['recipient_id'];
            $aMailBoxSettings['page'] 			= (int) $aMailBoxSettings['page'];

            $aMailBoxSettings['per_page'] 		= (int) $aMailBoxSettings['per_page'];
            $aMailBoxSettings['contacts_page']	= (int) $aMailBoxSettings['contacts_page'];
            $aMailBoxSettings['messageID'] 		= (int) $aMailBoxSettings['messageID'];

            $aMailBoxSettings['mailbox_mode'] 	= process_db_input($aMailBoxSettings['mailbox_mode'], BX_TAGS_STRIP);
            $aMailBoxSettings['contacts_mode'] 	= process_db_input($aMailBoxSettings['contacts_mode'], BX_TAGS_STRIP);
            $aMailBoxSettings['sort_mode'] 		= process_db_input($aMailBoxSettings['sort_mode'], BX_TAGS_STRIP);

            if($aMailBoxSettings['messages_types']) {
                $aMailBoxSettings['messages_types'] = process_db_input($aMailBoxSettings['messages_types'],BX_TAGS_STRIP);
            }

            parent::__construct($sPageName);

            $this -> aMailBoxSettings = $aMailBoxSettings;

            if ( $this -> aMailBoxSettings['per_page'] < 1 )
                $this -> aMailBoxSettings['per_page'] = 10 ;

            if ($this -> aMailBoxSettings['per_page'] > 100 )
                $this -> aMailBoxSettings['per_page'] = 100;

            $this -> aSortCriterias = array
            (
                'date'            => '`sys_messages`.`Date` ASC',
                'date_desc'       => '`sys_messages`.`Date` DESC',

                'subject'         => '`sys_messages`.`Subject` ASC',
                'subject_desc'    => '`sys_messages`.`Subject` DESC',

                'type'            => '`sys_messages`.`Type` ASC',
                'type_desc'       => '`sys_messages`.`Type` DESC',

                'author'          => '`Profiles`.`NickName` ASC',
                'author_desc'     => '`Profiles`.`NickName` DESC',
               );

                $oCache = $GLOBALS['MySQL']->getDbCacheObject();
                $this -> aRegisteredMessageTypes = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_messages_types'));
                if (null === $this -> aRegisteredMessageTypes) {

                    // generate types from DB ;
                    $sQuery  = "SHOW COLUMNS FROM  `sys_messages` LIKE 'Type'";
                    $rResult = db_res($sQuery);

                    $aMatches = array();
                    while( true == ($aRow = $rResult->fetch()) ) {
                         preg_match_all("/\'([^\']*)\'/", $aRow['Type'], $aMatches);
                         if ( is_array($aMatches[1]) and !empty($aMatches[1]) ) {
                              foreach($aMatches[1] AS $sKey ) {
                                   $this -> aRegisteredMessageTypes[] = $sKey;
                              }
                         }
                    }

                    // write data into cache file
                    $oCache = $GLOBALS['MySQL']->getDbCacheObject();
                    $oCache->setData ($GLOBALS['MySQL']->genDbCacheKey('sys_messages_types'), $this -> aRegisteredMessageTypes);
               }

               // get all needed messages types (need for SQL queries);
               if ( $this -> aMailBoxSettings['messages_types'] and !empty($this -> aMailBoxSettings['messages_types']) ) {
                    // try to define type of messages ;
                    $aTypes = explode(',', $this -> aMailBoxSettings['messages_types']);
                    if ( is_array($aTypes) and !empty($aTypes) ) {
                         foreach($aTypes AS $sKey) {
                              $this -> aReceivedMessagesTypes[] = $sKey;
                         }
                    }
               } elseif ( !isset($this -> aMailBoxSettings['messages_types']) ) {
                    // set all types by default ;
                    $sMessageTypes = null;
                    foreach($this -> aRegisteredMessageTypes AS $sKey) {
                         $this -> aReceivedMessagesTypes[] = process_db_input($sKey);
                         $sMessageTypes .= $sKey . ',';
                    }
                    $this -> aMailBoxSettings['messages_types'] = $sMessageTypes;
               }

               // define all available types of contacts ;
               $this -> aRegisteredContactTypes = array
               (
                    'Friends'          => 'getFriendsList',
                    'Faves'               => 'getFavesList',
                    'Contacted'          => 'getContactedList',
               );

               // define all available types of history list ;
               $this -> aRegisteredArchivesTypes = array
               (
                    'From'     => 'getArchivesList',
                    'To'     => 'getArchivesList',
               );

               // if the viewer is the owner of this message;
               // Set message's status as read ;
               if ( $this -> aMailBoxSettings['messageID'] ) {
                       $aMessageInfo = db_arr("SELECT `Recipient`, `New` FROM `sys_messages` WHERE `ID` = {$this -> aMailBoxSettings['messageID']}");
                    if ( $aMessageInfo['Recipient'] == $this -> aMailBoxSettings['member_id'] and $aMessageInfo['New'] == '1' ) {
                         db_res("UPDATE `sys_messages` SET `New` = '0' WHERE `ID` = {$this -> aMailBoxSettings['messageID']}");
                    }
               }
          }

          /**
          * Function will get array with all member's mail messages;
          *
          * @param           : $sSqlLimit (string) - rows limit for sql query ;
          * @return          : array;
          */
          function getArchivesList($sSqlLimit)
          {
                  $sSqlLimit = process_db_input($sSqlLimit);

               // init some needed variables ;

               $aMessages            = array();
               $iSecondPersonID = 0;

               if ( $this -> aMailBoxSettings['messageID'] ) {
                    // try to define message's owner ;
                    $iSecondPersonID = (int) db_value
                    (
                         "
                              SELECT
                                   IF (`Sender` = {$this -> aMailBoxSettings['member_id']},  `Recipient`, `Sender` ) AS `iSecondPerson`
                              FROM
                                   `sys_messages`
                              WHERE
                                   `ID` = {$this -> aMailBoxSettings['messageID']}
                                        AND
                                   (
                                        `Sender` = {$this -> aMailBoxSettings['member_id']}
                                             OR
                                        `Recipient` = {$this -> aMailBoxSettings['member_id']}
                                   )
                         "
                    );

                    // generate all messages ;
                    if ( $iSecondPersonID  ) {
                         $iSenderID      = ($this -> aMailBoxSettings['contacts_mode'] == 'To')
                              ? $this -> aMailBoxSettings['member_id']
                              : $iSecondPersonID ;

                         $iRecipientID = ($this -> aMailBoxSettings['contacts_mode'] == 'To')
                              ? $iSecondPersonID
                              : $this -> aMailBoxSettings['member_id'];

                         $sFieldName = ($this -> aMailBoxSettings['contacts_mode'] == 'To')
                              ? 'sender'
                              : 'recipient';

                         $this -> iTotalContactsCount = db_value
                         (
                              "
                                   SELECT
                                        COUNT(*)
                                   FROM
                                        `sys_messages`
                                   WHERE
                                        `Sender` = {$iSenderID}
                                             AND
                                        `Recipient` = {$iRecipientID}
                                             AND
                                        NOT FIND_IN_SET('{$sFieldName}', `Trash`)
                              "
                         );

                         $sQuery =
                         "
                              SELECT
                                   DATE_FORMAT(`Date`, '" . getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB) . "') AS `When`,
                                   `Sender`, `Text`, `Subject`, `New`, `ID`
                              FROM
                                   `sys_messages`
                              WHERE
                                   `Sender` = {$iSenderID}
                                        AND
                                   `Recipient` = {$iRecipientID}
                                        AND
                                   NOT FIND_IN_SET('{$sFieldName}', `Trash`)
                              ORDER BY
                                   `Date` DESC
                              {$sSqlLimit}
                         ";

                         $rResult = db_res($sQuery);
                         while( true == ($aRow = $rResult->fetch()) ) {
                              $aMessages[] = $aRow;
                         }
                    }
               }

               return $aMessages;
          }

          /**
           * Function will get array with all member's friends ;
           *
           * @param         : $sSqlLimit (string) - rows limit for sql query ;
           * @return        : array;
           */
          function getFriendsList( $sSqlLimit )
          {
                  $sSqlLimit = process_db_input($sSqlLimit);

               // init some needed variables ;

               $aFriendsList = array();

               $this -> iTotalContactsCount = getFriendNumber($this -> aMailBoxSettings['member_id']);

               $sQuery     =
               "
                    SELECT p.*,
                    DATE_FORMAT(f.`When`, '" . getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB) . "' ) AS 'When'
                    FROM (
                    SELECT `ID` AS `ID`, `When` FROM `sys_friend_list` WHERE `Profile`='{$this->aMailBoxSettings['member_id']}' AND `Check` =1
                    UNION
                    SELECT `Profile` AS `ID`, `When` FROM `sys_friend_list` WHERE `ID` = '{$this->aMailBoxSettings['member_id']}' AND `Check` =1
) AS `f`
                    INNER JOIN `Profiles` AS `p` ON (`p`.`ID` = `f`.`ID`)
                    ORDER BY f.`When` DESC
                    {$sSqlLimit}
                ";
               $rResult = db_res($sQuery);
               while( true == ($aRow = $rResult->fetch()) ) {
                    $aFriendsList[] = $aRow;
               }
               return $aFriendsList;
          }

          /**
           * Function will get array with all member's faves ;
           *
           * @param         : $sSqlLimit (string) - rows limit for sql query ;
           * @return        : array;
           */
          function getFavesList( $sSqlLimit )
          {
                  $sSqlLimit = process_db_input($sSqlLimit);

               // init some needed variables ;

               $aFavesList = array();

               $this -> iTotalContactsCount = db_value
               (
                         "
                         SELECT
                              COUNT(*)
                         FROM
                              `sys_fave_list`
                         WHERE
                         `sys_fave_list`.`Profile` = {$this -> aMailBoxSettings['member_id']}
                              OR
                         `sys_fave_list`.`ID` = {$this -> aMailBoxSettings['member_id']}
                         "
               );

               $sQuery     =
               "
                    SELECT
                         `Profiles`.*,
                         DATE_FORMAT(`sys_fave_list`.`When`, '" . getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB) . "') AS `When`
                    FROM
                         `sys_fave_list`
                    INNER JOIN
                         `Profiles`
                    ON
                         `Profiles`.`ID` = `sys_fave_list`.`Profile` AND `sys_fave_list`.`ID` = {$this -> aMailBoxSettings['member_id']}
                              OR
                         `Profiles`.`ID` = `sys_fave_list`.`ID` AND `sys_fave_list`.`Profile` = {$this -> aMailBoxSettings['member_id']}
                    WHERE
                         `sys_fave_list`.`Profile` = {$this -> aMailBoxSettings['member_id']}
                              OR
                         `sys_fave_list`.`ID` = {$this -> aMailBoxSettings['member_id']}
                    ORDER BY
                         `sys_fave_list`.`When` DESC
                    {$sSqlLimit}
               ";

               $rResult = db_res($sQuery);
               while( true == ($aRow = $rResult->fetch()) ) {
                    $aFavesList[] = $aRow;
               }

               return $aFavesList;
          }

          /**
           * Function will get array with all member's contacted persons ;
           *
           * @param         : $sSqlLimit (string) - limit of returned rows ;
           * @return        : array;
           */
          function getContactedList( $sSqlLimit )
          {
                  $sSqlLimit = process_db_input($sSqlLimit);

               // init some needed variables ;
               $aContactedList = array();

               $sQuery =
               "
               SELECT
                    COUNT( DISTINCT if(`Recipient` = {$this -> aMailBoxSettings['member_id']}, `Sender`, `Recipient`) ) AS `Contacts`
               FROM
                    `sys_messages`
               WHERE
                    (`Sender` = {$this -> aMailBoxSettings['member_id']}
                        OR
                    `Recipient` = {$this -> aMailBoxSettings['member_id']})
               ";

               // number of contacts ;
               $this -> iTotalContactsCount = db_value($sQuery);

               $sQuery     =
               "
                    SELECT
                         DISTINCT `Profiles`.*,
                         DATE_FORMAT(`sys_messages`.`Date`, '" . getLocaleFormat(BX_DOL_LOCALE_DATE_SHORT, BX_DOL_LOCALE_DB) . "') AS `When`
                    FROM
                         `sys_messages`
                    INNER JOIN
                         `Profiles`
                    ON
                         `Profiles`.`ID` = IF (`sys_messages`.`Recipient` = {$this -> aMailBoxSettings['member_id']}, `sys_messages`.`Sender`, `sys_messages`.`Recipient`)
                    WHERE
                         `Sender` = {$this -> aMailBoxSettings['member_id']}
                              OR
                         `Recipient` = {$this -> aMailBoxSettings['member_id']}
                    GROUP BY
                         `Profiles`.`NickName`
                    ORDER BY
                         `sys_messages`.`Date` DESC
                    {$sSqlLimit}
               ";

               $rResult = db_res($sQuery);
               while( true == ($aRow = $rResult->fetch()) ) {
                    $aContactedList[] = $aRow;
               }

               return $aContactedList;
          }

          /**
           * function will get count of inbox messages ;
           * @return           : (integer) - number of messages;
           */
          function getInboxMessagesCount()
          {
               return db_value
               ("
                    SELECT
                         COUNT(*)
                    FROM
                         `sys_messages`
                    WHERE
                    (
                         `Recipient` = {$this -> aMailBoxSettings['member_id']}
                              AND
                         NOT FIND_IN_SET('Recipient', `Trash`)
                     )
                          AND
                     `New` = '1'
               ");
          }

          /**
           * Function will get array with messages ;
           *
           * @return   : hash array with messages ;
           */
          function getMessages()
          {
               // init some needed variables ;
               $sWhereParameter = null;
               $sTypeMessages     = null;

               // define the sort parameter ;
               $sSortParameter = ( array_key_exists($this -> aMailBoxSettings['sort_mode'], $this -> aSortCriterias) )
                ? $this -> aSortCriterias[$this -> aMailBoxSettings['sort_mode']]
                : $this -> aSortCriterias['date_desc'];

               // define mailbox mode ;
               switch ($this -> aMailBoxSettings['mailbox_mode']) {
                    case 'inbox' :
                         $sWhereParameter = " AND `sys_messages`.`Recipient` = {$this -> aMailBoxSettings['member_id']} AND NOT FIND_IN_SET('Recipient', `sys_messages`.`Trash`) ";
                    break;

                    case 'inbox_new' :
                         $sWhereParameter = " AND `sys_messages`.`Recipient`={$this -> aMailBoxSettings['member_id']} AND NOT FIND_IN_SET('Recipient', `sys_messages`.`Trash`) AND `New`='1' ";
                    break;

                    case 'outbox' :
                         $sWhereParameter = " AND `sys_messages`.`Sender` = {$this -> aMailBoxSettings['member_id']} AND NOT FIND_IN_SET('Sender', `sys_messages`.`Trash`)";
                    break;

                    case 'trash' :
                         $sWhereParameter =
                         "
                              AND
                              (
                                   (
                                        `sys_messages`.`Sender` = {$this -> aMailBoxSettings['member_id']}
                                             AND
                                        FIND_IN_SET('Sender', `sys_messages`.`Trash`)
                                   )
                                   OR
                                   (
                                        `sys_messages`.`Recipient` = {$this -> aMailBoxSettings['member_id']}
                                             AND
                                        FIND_IN_SET('Recipient', `sys_messages`.`Trash`)
                                   )
                              )
                              AND
                              (
                                    (
                                        `sys_messages`.`Sender` = {$this -> aMailBoxSettings['member_id']}
                                             AND
                                        NOT FIND_IN_SET('Sender', `sys_messages`.`TrashNotView`)
                                   )
                                   OR
                                   (
                                        `sys_messages`.`Recipient` = {$this -> aMailBoxSettings['member_id']}
                                             AND
                                        NOT FIND_IN_SET('Recipient', `sys_messages`.`TrashNotView`)
                                   )
                              )
                         ";
                    break;
               }

               // define messages types
               if (is_array($this -> aReceivedMessagesTypes) and !empty($this -> aReceivedMessagesTypes)) {
                    foreach( $this -> aReceivedMessagesTypes AS $sKey ) {
                         if ($sKey)
                              $sTypeMessages .= "`sys_messages`.`Type` = '{$sKey}' OR";
                    }
                    $sTypeMessages = 'AND (' . preg_replace("/OR$/", '', $sTypeMessages). ')';
               } else
                    return;

               // if defined all needed parameters ;
               if (  $sWhereParameter ) {
                    // count of all messages ;
                    $this -> iTotalMessageCount = db_value
                    ("
                         SELECT
                              COUNT(`sys_messages`.`ID`)
                         FROM
                              `sys_messages`
                         WHERE
                              1
                              {$sWhereParameter}
                              {$sTypeMessages}
                    ");

                    if ( $this -> iTotalMessageCount ) {
                         // define number of maximum message for per page ;
                         if( $this -> aMailBoxSettings['page'] < 1 )
                              $this -> aMailBoxSettings['page'] = 1;

                         $sLimitFrom = ( $this -> aMailBoxSettings['page'] - 1 ) * $this -> aMailBoxSettings['per_page'];
                         $sSqlLimit = "LIMIT {$sLimitFrom}, {$this -> aMailBoxSettings['per_page']}";

                         $sFieldName = $this -> aMailBoxSettings['mailbox_mode'] == 'outbox'
                             ? 'Recipient'
                             : 'Sender';

                         $sQuery =
                         "
                              SELECT
                                   `sys_messages`.`ID`, DATE_FORMAT(`sys_messages`.`Date`, '" . getLocaleFormat(BX_DOL_LOCALE_DATE, BX_DOL_LOCALE_DB) . "') AS `Date`,
                                   UNIX_TIMESTAMP() - UNIX_TIMESTAMP(`sys_messages`.`Date`) AS `DateUTS`,
                                   UNIX_TIMESTAMP(`sys_messages`.`Date`) AS `DateTS`,
                                   `sys_messages`.`Sender`, `sys_messages`.`Recipient`, `sys_messages`.`Text`,
                                   `sys_messages`.`Subject`, `sys_messages`.`New`, `sys_messages`.`Type`
                              FROM
                                   `sys_messages`
                              INNER JOIN
                                   `Profiles`
                              ON
                                   `Profiles`.`ID` = `sys_messages`.`{$sFieldName}`
                              WHERE
                                   1
                                   {$sWhereParameter}
                                   {$sTypeMessages}
                              ORDER BY {$sSortParameter}
                              {$sSqlLimit}
                         ";

                         $aMessages = array();
                         $rResult = db_res($sQuery);

                         // generate array with messages ;
                         while( true == ($aRow = $rResult->fetch()) ) {
                          $aMessages[] = $aRow;
                         }

                         // return generated array ;
                         return $aMessages;
                    }
               }
          }

          /**
           * Function will set mark message with received mode ;
           *
           * @param          : $iMessageID (integer) - message's ID ;
           * @param          : $iMarkMode (integer) - 0 if message not new, else 1 ;
           * @return         : (integer) - number of affected rows ;
           */
          function setMarkMessage( $iMessageID, $iMarkMode )
          {
              $iMessageID = (int) $iMessageID;
              $iMarkMode = (int) $iMarkMode;

           $sQuery =
           "
                UPDATE
                     `sys_messages`
                SET
                     `New` = '{$iMarkMode}'
                WHERE
                     `ID` = {$iMessageID}
                          AND
                      `Recipient` = {$this -> aMailBoxSettings['member_id']}
            ";
               $res = db_res($sQuery);

               return db_affected_rows($res);
          }

          /**
           * Function will set the message in trash mode ;
           *
           * @param         : $iMessageID (integer) - message's Id ;
           * @paaram $sField string
           * @return        : (integer) - number of affected rows ;
           */
          function setTrashedMessage($iMessageID, $sField = 'Trash')
          {
                  $iMessageID = (int) $iMessageID;
                $sField = process_db_input($sField, BX_TAGS_STRIP, BX_SLASHES_NO_ACTION);

               // try to define mailbox mode and trash mode;
               $sQuery = "SELECT `Sender`, `Recipient`, `{$sField}` FROM `sys_messages` WHERE `ID` = {$iMessageID}";
               $rResult = db_res($sQuery);

               while( true == ($aRow = $rResult->fetch()) ) {
                    $sTrashValue = $sFieldName = ( $aRow['Sender'] == $this -> aMailBoxSettings['member_id'] )
                         ? 'Sender'
                         : 'Recipient' ;

                    if ( $aRow['Recipient'] == $aRow['Sender']  ) {
                         $sTrashValue = 'sender,recipient';
                    } else if ( $aRow[$sField] ) {
                         $sTrashValue = $sTrashValue . ',' . $aRow[$sField];
                    }
               }

               // set trashed ;
               $sQuery =
               "
                         UPDATE
                              `sys_messages`
                         SET
                              `{$sField}` = '" . strtolower($sTrashValue) . "'
                         WHERE
                           `ID` = {$iMessageID}
                          AND
                      `{$sFieldName}` = {$this -> aMailBoxSettings['member_id']}
               ";

               $res = db_res($sQuery);

               return db_affected_rows($res);
          }

          /**
           * Function will restore message from trash ;
           *
           * @param    : $iMessageID (integer) - message's Id ;
           * @return   : (integer) - number of affected rows ;
           */
          function setRestoredMessage( $iMessageID )
          {
                  $iMessageID = (int) $iMessageID;

               // init some needed variables ;
               $iMessageOwner = 0;

               $sQuery = "SELECT `Sender`, `Recipient`, `Trash`     FROM `sys_messages` WHERE `ID` = {$iMessageID}";
               $rResult = db_res($sQuery);

               while( true == ($aRow = $rResult->fetch()) ) {
                    if ( $aRow['Recipient'] == $aRow['Sender']  ) {
                         $sTrashMode      = null;
                         $iMessageOwner  = (int) $aRow['Sender'];
                         break;
                    } else if ( $aRow['Sender'] == $this -> aMailBoxSettings['member_id'] ) {
                         $iMessageOwner  = (int) $aRow['Sender'];
                         $sTrashMode      = preg_replace("/sender|\,/", '', $aRow['Trash']);
                         break;
                    } else if ( $aRow['Recipient'] == $this -> aMailBoxSettings['member_id'] ) {
                         $iMessageOwner  = (int) $aRow['Recipient'];
                         $sTrashMode      = preg_replace("/recipient|\,/", '', $aRow['Trash']);
                         break;
                    }
               }

               // get restore message ;
               if ( $iMessageOwner ) {
                    $sQuery =
                    "
                         UPDATE
                              `sys_messages`
                         SET
                              `Trash` = '{$sTrashMode}'
                         WHERE
                              `ID` = {$iMessageID}
                                   AND
                              (
                                   `Sender` = {$iMessageOwner}
                                        OR
                                   `Recipient` = {$iMessageOwner}
                              )
                    ";
                    $res = db_res($sQuery);

                    return db_affected_rows($res);
               }
          }

          /**
           * Function will send the compose message ;
           *
           * @param          : $sMessageSubject     (string) - message's subject ;
           * @param          : $sMessageBody        (string) - message's body ;
           * @param          : $vRecipientID        (variant)- message's recipient ID or NickName;
           * @param          : $aComposeSettings    (array)  - contain all needed settings for compose message ;
           * 					[ send_copy	] (bolean)     - allow to send message to phisical recipient's email ;
           * 					[ notification ] (boolean) - allow to send notification to the recipient's email ;
           * 					[ send_copy_to_me ] (boolean) - allow to send message to phisical sender's email ;
           * @return         : signaling information with Html ;
           */
          function sendMessage( $sMessageSubject, $sMessageBody, $vRecipientID, &$aComposeSettings, $isSimulateSending = false )
          {
               $sMessageSubject = process_db_input($sMessageSubject, BX_TAGS_STRIP);
               $sMessageSubjectCopy = $GLOBALS['MySQL']->unescape ($sMessageSubject);

               $sMessageBody = process_db_input($sMessageBody, BX_TAGS_VALIDATE);
               $sCopyMessage = $GLOBALS['MySQL']->unescape ($sMessageBody);

               if(!$isSimulateSending && (!$sMessageSubject || !$sMessageBody)) {
                        $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_FAILED;
                        return MsgBox( _t('_please_fill_next_fields_first') );
               }

               // init some needed variables ;
               $sReturnMessage   = null;
               $sComposeUrl      =  BX_DOL_URL_ROOT . 'mail.php?mode=compose';

               // try to define member's ID ;
               $iRecipientID        = (int) getId($vRecipientID);
               if(!$iRecipientID) {
                    $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_UNKNOWN_RECIPIENT;
                    return MsgBox ( _t('_Profile not found') );
               }

               $aRecipientInfo      = getProfileInfo($iRecipientID);
               $oEmailTemplate      = new BxDolEmailTemplates();
               $bAllowToSend        = true;

               $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_FAILED;

                // ** check permission for recipient member ;

                // Check if member is blocked ;
                $sQuery =
                "
                     SELECT
                          `ID`, `Profile`
                     FROM
                          `sys_block_list`
                     WHERE
                          `Profile` = {$this -> aMailBoxSettings['member_id']}
                               AND
                          `ID` = '{$iRecipientID}'
                " ;

                if (!isAdmin($this -> aMailBoxSettings['member_id']) && db_arr($sQuery)) {
                    $sReturnMessage = MsgBox( _t('_FAILED_TO_SEND_MESSAGE_BLOCK') );
                    $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_BLOCKED;
                    $bAllowToSend = false;
                }

                    // antispam check ;
                    $sQuery =
                    "
                         SELECT
                              `ID`
                         FROM
                              `sys_messages`
                         WHERE
                              `Sender` = {$this -> aMailBoxSettings['member_id']}
                                   AND
                              date_add(`Date`, INTERVAL {$this -> iWaitMinutes} MINUTE) > Now()
                    ";

                    if ( db_arr($sQuery) ) {
                        $sReturnMessage = MsgBox( _t('_You have to wait for PERIOD minutes before you can write another message!', $this -> iWaitMinutes, $sComposeUrl) );
                        $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_WAIT;
                        $bAllowToSend   = false;
                    }

                // additional antispam check ;
                if (bx_is_spam($sCopyMessage)) {
                    $sReturnMessage = MsgBox( sprintf(_t("_sys_spam_detected"), BX_DOL_URL_ROOT . 'contact.php') );
                    $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_FAILED;
                    $bAllowToSend = false;
                }

                // check if member not active ;
                if ( $aRecipientInfo['Status'] != 'Active' ) {
                    $sReturnMessage = MsgBox( _t('_FAILED_TO_SEND_MESSAGE_NOT_ACTIVE', $sComposeUrl) );
                    $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_RECIPIENT_NOT_ACTIVE;
                    $bAllowToSend   = false;
                }

                // chek membership level;
                if(!$this -> isSendMessageAlowed($this -> aMailBoxSettings['member_id'], $isSimulateSending ? false : true) ) {
                    $sReturnMessage = MsgBox( _t('_FAILED_TO_SEND_MESSAGE_MEMBERSHIP_DISALLOW') );
                    $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_FAILED_MEMBERSHIP_DISALLOW;
                    $bAllowToSend   = false;
                }

                // ** allow to send message ;
                if ( !$isSimulateSending && $bAllowToSend ) {
                    $sQuery =
                    "
                        INSERT INTO
                            `sys_messages`
                        SET
                            `Sender`       = {$this -> aMailBoxSettings['member_id']},
                            `Recipient`    = {$iRecipientID},
                            `Subject`      =  '{$sMessageSubject}',
                            `Text`         =  '{$sMessageBody}',
                            `Date`         = NOW(),
                            `New`          = '1',
                            `Type`         = 'letter'
                    ";

                    if ( db_res($sQuery ) ) {
                        $sReturnMessage = MsgBox( _t('_MESSAGE_SENT', $sComposeUrl, getProfileLink($iRecipientID), $aRecipientInfo['NickName']));
                        $this -> iSendMessageStatusCode = BX_MAILBOX_SEND_SUCCESS;

                        //--- create system event
                        bx_import('BxDolAlerts');
                        $aAlertData = array(
                            'msg_id'          => db_last_id(),
                            'subject'         => $sMessageSubjectCopy,
                            'body'            => $sCopyMessage,
                            'send_copy'       => $aComposeSettings['send_copy'],  //boolean
                            'notification'    => $aComposeSettings['notification'],  //boolean
                            'send_copy_to_me' => $aComposeSettings['send_copy_to_me'],  //boolean
                        );

                        $oZ = new BxDolAlerts('profile', 'send_mail_internal'
                            , $this -> aMailBoxSettings['member_id'], $iRecipientID, $aAlertData);
                        $oZ -> alert();

                        // ** check the additional parameters ;

                        // send message to phisical recipient's email ;
                        if ( $aComposeSettings['send_copy'] ) {
                          $aTemplate = $oEmailTemplate -> getTemplate( 't_Message', $iRecipientID ) ;
                          $aPlus = array();
                          $aPlus['MessageText']         = replace_full_uris( $sCopyMessage );
                          $aPlus['ProfileReference']    = getNickName($this -> aMailBoxSettings['member_id']);
                          $aPlus['ProfileUrl']          = getProfileLink($this -> aMailBoxSettings['member_id']);
                          sendMail( $aRecipientInfo['Email'], $sMessageSubjectCopy, $aTemplate['Body'], $iRecipientID, $aPlus );
                        }

                        // send notification to the recipient's email ;
                        if ( $aComposeSettings['notification'] ) {
                          $aTemplate = $oEmailTemplate -> getTemplate( 't_Compose', $iRecipientID ) ;
                          $aPlus['ProfileReference'] = getNickName($this -> aMailBoxSettings['member_id']);
                          $aPlus['ProfileUrl']       = getProfileLink($this -> aMailBoxSettings['member_id']);
                          sendMail( $aRecipientInfo['Email'], $aTemplate['Subject'], $aTemplate['Body'], $iRecipientID, $aPlus );
                        }

                        // allow to send message to phisical sender's email;
                        if ( $aComposeSettings['send_copy_to_me'] ) {
                          $aSenderInfo  = getProfileInfo($this -> aMailBoxSettings['member_id']);
                          $aTemplate    = $oEmailTemplate -> getTemplate( 't_MessageCopy', $this -> aMailBoxSettings['member_id'] ) ;

                          $aPlus['your subject here'] = $sMessageSubjectCopy;
                          $aPlus['your message here'] = replace_full_uris( $sCopyMessage );

                          sendMail( $aSenderInfo['Email'], $aTemplate['Subject']
                              , $aTemplate['Body'], $this -> aMailBoxSettings['member_id'], $aPlus );
                        }
                    } else {
                        $sReturnMessage = MsgBox( _t('_FAILED_TO_SEND_MESSAGE') );
                        $this->iSendMessageStatusCode = BX_MAILBOX_SEND_FAILED;
                    }
                }

               return $sReturnMessage;
        }

        /**
         * Function will get count of sent messsages ;
         *
         * @param  : $iMemberId (integer)       - logged member's id;
         * @param  : $sMessageStatus (string)   - needed message's status (possible values - '1', '0');
         * @return : (integer) - number of sent messages;
         */
        static function getCountSentMessages($iMemberId, $sMessageStatus = null)
        {
            $iMemberId = (int) $iMemberId;
            $sMessageStatus = process_db_input($sMessageStatus);

            $sExtraQuery = ( $sMessageStatus ) ? " AND `New` = '{$sMessageStatus}'" : null;

            $sQuery =
            "
                SELECT
                    COUNT(*)
                FROM
                    `sys_messages`
                WHERE
                    `Sender` = {$iMemberId}
                        AND
                    NOT FIND_IN_SET('Sender', `Trash`)
                        {$sExtraQuery}
            ";

            return db_value($sQuery);
        }

        /**
         * Function will get count of inbox messages ;
         *
         * @param  : $iMemberId (integer)       - logged member's id;
         * @param  : $sMessageStatus (string)   - needed message's status (possible values - '1', '0');
         * @return : (integer) - number of inbox messages;
         */
        static function getCountInboxMessages($iMemberId, $sMessageStatus = null)
        {
            $iMemberId = (int) $iMemberId;
            $sMessageStatus = process_db_input($sMessageStatus);

            $sExtraQuery = ( $sMessageStatus ) ? " AND `New` = '{$sMessageStatus}'" : null;

            $sQuery =
            "
                SELECT
                    COUNT(*)
                FROM
                    `sys_messages`
                WHERE
                    `Recipient` = {$iMemberId}
                        AND
                    NOT FIND_IN_SET('Recipient', `Trash`)
                        {$sExtraQuery}
            ";

            return db_value($sQuery);
        }

        /**
         * Function will get count of trashed messages ;
         *
         * @param  : $iMemberId (integer)       - logged member's id;
         * @param  : $sMessageStatus (string)   - needed message's status (possible values - '1', '0');
         * @return : (integer) - number of trashed messages;
         */
        static function getCountTrashedMessages($iMemberId, $sMessageStatus = null)
        {
            $iMemberId = (int) $iMemberId;
            $sMessageStatus = process_db_input($sMessageStatus);

            $sExtraQuery = ( $sMessageStatus ) ? " AND `New` = '{$sMessageStatus}'" : null;

            $sQuery =
            "
                SELECT
                    COUNT(*)
                FROM
                    `sys_messages`
                WHERE
                    (
                        (
                            `Sender` = {$iMemberId}
                                AND
                            FIND_IN_SET('Sender', `Trash`)
                        )
                            OR
                        (
                            `Recipient` = {$iMemberId}
                                AND
                            FIND_IN_SET('Recipient', `Trash`)
                        )
                    )
                      AND
                    (
                        (
                            `sys_messages`.`Sender` = {$iMemberId}
                                AND
                             NOT FIND_IN_SET('Sender', `sys_messages`.`TrashNotView`)
                         )
                             OR
                         (
                             `sys_messages`.`Recipient` = {$iMemberId}
                                AND
                             NOT FIND_IN_SET('Recipient', `sys_messages`.`TrashNotView`)
                         )
                    )
                    {$sExtraQuery}
            ";

            return db_value($sQuery);
        }

        /**
         * Function will check membership level for current type if users;
         *
         * @param : $iMemberId (integer) - member's Id;
         * @param : $isPerformAction (boolean) - if isset this parameter that function will amplify the old action's value;
         */
        function isSendMessageAlowed($iMemberId, $isPerformAction = false)
        {
          $iMemberId = (int) $iMemberId;

          $this -> _defineActions();
          $aCheck = checkAction($iMemberId, BX_SEND_MESSAGES, $isPerformAction);
          return $aCheck[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED;
        }

        function _defineActions()
        {
            defineMembershipActions( array('send messages') );
        }
    }
