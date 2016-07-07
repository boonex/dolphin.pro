<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

 class BxDolUpdateMembersCache extends BxDolAlertsResponse
 {
    // system event
    function response($o)
    {

        $sProfileStatus = null;
        $iProfileId = $o->iObject;

        if ( $iProfileId )
            $sProfileStatus = db_value
            (
                "
                    SELECT
                        `Status`
                    FROM
                        `Profiles`
                    WHERE
                        `ID` = {$iProfileId}
                "
            );

        if ( $sProfileStatus == 'Active' ) {

            if ('profile' == $o->sUnit)
            switch ($o->sAction) {

                case 'join':
                case 'edit':
                case 'delete':
                    // clean cache
                    $GLOBALS['MySQL']->cleanCache('sys_browse_people');
                break;

            }

        }
    }

 }
