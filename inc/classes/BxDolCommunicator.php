<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPageView.php');
    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php' );
    require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolEmailTemplates.php' );

    class BxDolCommunicator extends BxDolPageView
    {
        // contain count of all requests ;
        var $iTotalRequestsCount;

        // contain some necessary data ;
        var $aCommunicatorSettings = array();

       /**
        * Class constructor ;
        *
        * @param	: $aCommunicatorSettings (array)  - contain some necessary data ;
        * 					[ member_id	] (integer) - logged member's ID;
        * 					[ communicator_mode ] (string) - page mode ;
        * 					[ person_switcher ] (string) - switch the person mode - from me or to me ;
        * 					[ sorting ] (string) - type of message's sort ;
        * 					[ page ] (integer) - contain number of current page ;
        * 					[ per_page ] (integer) - contain per page number for current page ;
        * 					[ alert_page ] (integer) - contain number of current alert's page
        */
        function __construct($aCommunicatorSettings)
        {
            $aCommunicatorSettings['member_id'] = (int) $aCommunicatorSettings['member_id'];
            $aCommunicatorSettings['page'] = (int) $aCommunicatorSettings['page'];
            $aCommunicatorSettings['per_page'] = (int) $aCommunicatorSettings['per_page'];
            $aCommunicatorSettings['alert_page'] = (int) $aCommunicatorSettings['alert_page'];

            $aCommunicatorSettings['communicator_mode'] = process_db_input($aCommunicatorSettings['communicator_mode'], BX_TAGS_STRIP);
            $aCommunicatorSettings['person_switcher'] = process_db_input($aCommunicatorSettings['person_switcher'], BX_TAGS_STRIP);
            $aCommunicatorSettings['sorting'] = process_db_input($aCommunicatorSettings['sorting'], BX_TAGS_STRIP);

            // call the parent constructor ;
            parent::__construct('communicator_page');
            $this -> aCommunicatorSettings = &$aCommunicatorSettings;

            // init some pagination parameters ;
            if ( $this -> aCommunicatorSettings['per_page'] < 1 )
                $this -> aCommunicatorSettings['per_page'] = 10 ;

            if ($this -> aCommunicatorSettings['per_page'] > 100 )
                $this -> aCommunicatorSettings['per_page'] = 100;

            if($aCommunicatorSettings['member_id'] != 0)
                $GLOBALS['oTopMenu']->setCurrentProfileID($aCommunicatorSettings['member_id']);
        }

        /**
         * Function will return array with needed requests ;
         *
         * @param   : $sTableName (string) - DB's table name;
         * @param   : $aRequestTypes (array) - contain language keys for differences person's mode ;
                        [ from ]  - needed if person mode = 'from' ;
                        [ to ]    - needed if person mode = 'to'   ;
         * @param   : $sAdditionalParam (string) - additional SQL query ;
         * @param   : $sAdditionalField (string) - additional table's field ;
         * @return  : (array) array with all requests ;
                        [ member_id ] -  member's ID ;
                        [ date ]      -  request's date ;
                        [ type ]      -  type of request ;
         */
        function getRequests( $sTableName, &$aRequestTypes, $sAdditionalParam = null, $sAdditionalField = null )
        {
            $sTableName = process_db_input($sTableName);
            $sAdditionalParam = process_db_input($sAdditionalParam);
            $sAdditionalField = process_db_input($sAdditionalField);

            // ** init some needed variables ;
            $aRequests = array();

            // init all sort criterias ;
            $aSortCriterias = array
            (
                // sort all requests by 'date' ;
                'date'        => "`{$sTableName}`.`When` ASC",

                // sort all requests by 'author' ;
                'author'      => '`Profiles`.`NickName`  ASC',

                // sort all requests by 'date' DESC;
                'date_desc'   => "`{$sTableName}`.`When` DESC",

                // sort all requests by 'author' DESC ;
                'author_desc' => '`Profiles`.`NickName`  DESC',
            );

            // define the sort parameter ;
            $sSortParameter = ( array_key_exists($this -> aCommunicatorSettings['sorting'], $aSortCriterias) )
                ? $aSortCriterias[$this -> aCommunicatorSettings['sorting']]
                : $aSortCriterias['date_desc'];

            // define the person mode ;
            switch( $this -> aCommunicatorSettings['person_switcher'] ) {
                case 'from' :
                    $sFieldName = '`ID`';
                break;
                case 'to'   :
                    $sFieldName = '`Profile`';
                break;
                default     :
                    $sFieldName = '`ID`';
            }

            // count of all requests ;
            $this -> iTotalRequestsCount = db_value
            ("
                SELECT
                    COUNT(*)
                FROM
                    `{$sTableName}`
                WHERE
                    `{$sTableName}`.{$sFieldName}  = {$this -> aCommunicatorSettings['member_id']}
                    {$sAdditionalParam}
            ");

            if ( $this -> iTotalRequestsCount ) {
                // lang keys ;
                $sRequestFrom  = $GLOBALS['MySQL'] -> escape($aRequestTypes['from']);
                $sRequestTo    = $GLOBALS['MySQL'] -> escape($aRequestTypes['to']);

                // define number of maximum rows for per page ;
                if( $this -> aCommunicatorSettings['page'] < 1 )
                    $this -> aCommunicatorSettings['page'] = 1;

                $sLimitFrom = ( $this -> aCommunicatorSettings['page'] - 1 ) * $this -> aCommunicatorSettings['per_page'];
                $sSqlLimit = "LIMIT {$sLimitFrom}, {$this -> aCommunicatorSettings['per_page']}";

                // define the additional table's field ;
                $sExtFieldName = ( $sAdditionalField ) ? ", `{$sTableName}`.`{$sAdditionalField}`" : null ;

                $sQuery =
                "
                    SELECT
                        IF(`{$sTableName}`.`ID` = {$this -> aCommunicatorSettings['member_id']},
                            `{$sTableName}`.`Profile`, `{$sTableName}`.`ID`) AS `iMemberID`,

                        IF(`{$sTableName}`.`ID` = {$this -> aCommunicatorSettings['member_id']}, $sRequestFrom, $sRequestTo)
                            AS `sType`,

                        DATE_FORMAT(`{$sTableName}`.`When`, '" . getLocaleFormat(BX_DOL_LOCALE_DATE, BX_DOL_LOCALE_DB) . "') AS `sDate`
                        {$sExtFieldName}
                    FROM
                        `{$sTableName}`
                    LEFT JOIN
                        `Profiles`
                    ON
                        `Profiles`.`ID` = IF(`{$sTableName}`.`ID` = {$this -> aCommunicatorSettings['member_id']},
                            `{$sTableName}`.`Profile`, `{$sTableName}`.`ID`)
                    WHERE
                        `{$sTableName}`.{$sFieldName} = {$this -> aCommunicatorSettings['member_id']}
                        {$sAdditionalParam}
                    ORDER BY
                       {$sSortParameter}
                       {$sSqlLimit}
                ";

                $rResult = db_res($sQuery);
                while( true == ($aRow = $rResult->fetch()) ) {
                    $sExtType = ( !empty($aRequestTypes['specific_key']) and $sAdditionalField )
                        ? ' ' . _t( $aRequestTypes['specific_key'], $aRow[$sAdditionalField] )
                        : null ;

                    $aRequests[] = array
                    (
                        'member_id' => $aRow['iMemberID'],
                        'date'      => $aRow['sDate'],
                        'type'      => $aRow['sType'] . $sExtType,
                    );
                }
            }

            return $aRequests;
        }

        /**
         * Function will execute the received method name ;
         *
         * @param   : $sCallback (string)  - name of needed function name ;
         * @param   : $sTableName (string) - DB table's name ;
         * @param   : $aMembersList (array)- received members list ;
         * @param   : $aParameters (array) - extended method parameters ;
         */
        function execFunction( $sCallback, $sTableName, &$aMembersList, $aParameters = array() )
        {
            $sTableName = process_db_input($sTableName);

            $aCallback = array($this, $sCallback);
            if ( is_callable($aCallback) and is_array($aMembersList) and !empty($aMembersList) ) {
                foreach( $aMembersList AS $iMemberID ) {
                    if ( is_numeric($iMemberID) ) {
                        $aExtendedParameters = array_merge( array($sTableName), array( $iMemberID), $aParameters );
                        call_user_func_array($aCallback, $aExtendedParameters );
                    }
                }
            }
        }

        /**
         * Function will delete request from received table's name ;
         *
         * @param   : $sTableName (string)  - DB table's name ;
         * @param   : $iFromOwner (integer) - swith mode to from owner or from recipent ;
         * @param   : $iMemberID (integer)  - member's ID ;
         * @return  : (integer) - number of affected rows;
         */
        function _deleteRequest( $sTableName, $iMemberID, $iFromOwner = 0, $iExtraDelete = 0 )
        {
            $sTableName = process_db_input($sTableName);
            $iMemberID = (int) $iMemberID;
            $iFromOwner = (int) $iFromOwner;
            $iExtraDelete = (int) $iExtraDelete;

            // define the table's field ;
            if ( !$iFromOwner ) {
                $iID       = $iMemberID;
                $iProfile  = $this -> aCommunicatorSettings['member_id'];
            } else {
                $iID       = $this -> aCommunicatorSettings['member_id'];
                $iProfile  = $iMemberID;
            }

            if ( $iExtraDelete ) {
                $sQuery =
                "
                    DELETE FROM
                        `{$sTableName}`
                    WHERE
                        (
                            `ID` = {$iMemberID}
                                AND
                            `Profile` = {$this -> aCommunicatorSettings['member_id']}
                        )
                           OR
                        (
                            `ID` = {$this -> aCommunicatorSettings['member_id']}
                                AND
                            `Profile` = {$iMemberID}
                        )
                ";

                $res = db_res($sQuery);
            } else {
                $sQuery = "DELETE FROM `{$sTableName}` WHERE `ID` = {$iID} AND `Profile` = {$iProfile}";
                $res = db_res($sQuery);
            }

            $iRet = db_affected_rows($res);

            switch ($sTableName) {
            case 'sys_friend_list':
                $oZ = new BxDolAlerts('friend', 'delete', $iID, $iProfile);
                $oZ -> alert();
                break;
            case 'sys_fave_list':
                $oZ = new BxDolAlerts('fave', 'delete', $iID, $iProfile);
                $oZ -> alert();
                break;
            case 'sys_block_list':
                $oZ = new BxDolAlerts('block', 'delete', $iID, $iProfile);
                $oZ -> alert();
                break;
            }

            return $iRet;
        }

        /**
         * Function will add request into received table's name ;
         *
         * @param   : $sTableName (string)  - DB table's name ;
         * @param   : $iMemberID (integer)  - member's ID ;
         * @return  : (integer) - number of affected rows;
         */
        function _addRequest( $sTableName, $iMemberID )
        {
            $sTableName = process_db_input($sTableName);
            $iMemberID = (int) $iMemberID;

            $sQuery =
           "
                SELECT
                    `ID`
                FROM
                    `{$sTableName}`
                WHERE
                    `ID` = {$this -> aCommunicatorSettings['member_id']}
                        AND
                    `Profile` = {$iMemberID}
           ";

            $res = null;
           // if pair non-existent ;
           if ( !db_value($sQuery) ) {
               $sQuery =
               "
                    INSERT INTO
                        `{$sTableName}`
                    SET
                        `ID` = {$this -> aCommunicatorSettings['member_id']},
                        `Profile` = {$iMemberID}
               ";

               $res = db_res($sQuery);
           }

           return db_affected_rows($res);
        }

        /**
         * Function will set status as `accepted` for friend request ;
         *
         * @param   : $sTableName (string)  - DB table's name ;
         * @param   : $iMemberID (integer) - member's ID ;
         */
        function _acceptFriendInvite($sTableName, $iMemberID)
        {
			$sTableName = process_db_input($sTableName, BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION);

            $iMemberID = (int)$iMemberID;
            $iAccepted = (int)db_value("SELECT `Check` FROM `{$sTableName}` WHERE `ID`={$iMemberID} AND `Profile`={$this -> aCommunicatorSettings['member_id']} LIMIT 1");
            if($iAccepted == 1)
            	return;

			db_res("UPDATE `{$sTableName}` SET `Check`=1 WHERE `ID`={$iMemberID} AND `Profile`={$this -> aCommunicatorSettings['member_id']}");
            	
            //--- Friend -> Accept for Alerts Engine --//
            $oZ = new BxDolAlerts('friend', 'accept', $iMemberID, $this -> aCommunicatorSettings['member_id']);
            $oZ -> alert();
            //--- Friend -> Accept for Alerts Engine --//

            //--- Send email notification ---//
            $oEmailTemplate = new BxDolEmailTemplates();
            $aTemplate = $oEmailTemplate->getTemplate('t_FriendRequestAccepted', $iMemberID);

            $aRecipient = getProfileInfo($iMemberID);
            sendMail($aRecipient['Email'], $aTemplate['Subject'], $aTemplate['Body'], '', array(
                'Recipient' => getNickName($aRecipient['ID']),
                'SenderLink' => getProfileLink($this -> aCommunicatorSettings['member_id']),
                'Sender' => getNickName($this -> aCommunicatorSettings['member_id']),
            ));
			//--- Send email notification ---//
        }

        function _getJsObject()
        {
            return 'oCommunicatorPage' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->aCommunicatorSettings['communicator_mode'])));
        }
    }
