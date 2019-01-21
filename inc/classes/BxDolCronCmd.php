<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'membership_levels.inc.php' );
require_once('BxDolCron.php');

class BxDolCronCmd extends BxDolCron
{
    // - Functions -

    function finish()
    {
        global $site;
        global $MODE;

        if ( $MODE != "_LIVE_" ) {
            $output = ob_get_contents();
            ob_end_clean();

            if ( $MODE == "_MAIL_" && $output) {
                sendMail ($site['email'], "{$site['title']}: Periodic Report", $output, 0, array(), 'text');
            }
        }
    }

    function clean_database()
    {
        $db_clean_vkiss = (int) getParam("db_clean_vkiss");
        $db_clean_profiles = (int) getParam("db_clean_profiles");
        $db_clean_msg = (int) getParam("db_clean_msg");
        $db_clean_visits = (int) getParam("db_clean_members_visits");
        $db_clean_banners_info = (int) getParam("db_clean_banners_info");
        $db_clean_mem_levels = (int) getParam("db_clean_mem_levels");

        //clear from `sys_acl_levels_members`
        if (db_res("DELETE FROM `sys_acl_levels_members` WHERE `DateExpires` < NOW() - INTERVAL $db_clean_mem_levels DAY"))
            db_res("OPTIMIZE TABLE `sys_acl_levels_members`");

        //clear from `sys_banners_shows`
        if (db_res("DELETE FROM `sys_banners_shows` WHERE `Date` < UNIX_TIMESTAMP( NOW() - INTERVAL $db_clean_banners_info DAY )"))
            db_res("OPTIMIZE TABLE `sys_banners_shows`");

        //clear from `sys_banners_clicks`
        if (db_res("DELETE FROM `sys_banners_clicks` WHERE `Date` < UNIX_TIMESTAMP( NOW() - INTERVAL $db_clean_banners_info DAY )"))
            db_res("OPTIMIZE TABLE `sys_banners_clicks`");

        // clear from `sys_messages`
        if (db_res("DELETE FROM `sys_messages` WHERE FIND_IN_SET('sender', `Trash`) AND FIND_IN_SET('recipient', `Trash`)"))
            db_res("OPTIMIZE TABLE `sys_messages`");

        //clear from `sys_ip_members_visits`
        if (db_res("DELETE FROM `sys_ip_members_visits` WHERE `DateTime` < NOW() - INTERVAL $db_clean_visits DAY"))
            db_res("OPTIMIZE TABLE `sys_ip_members_visits`");

        // clear ban table
        if (db_res("DELETE FROM `sys_admin_ban_list` WHERE `DateTime` + INTERVAL `Time` SECOND < NOW()"))
            db_res("OPTIMIZE TABLE `sys_admin_ban_list`");

        // profile_delete
        if ( $db_clean_profiles > 0) {
            $res = db_res("SELECT `ID` FROM `Profiles` WHERE ((`DateLastNav` <> '0000-00-00 00:00:00' AND `DateLastNav` < NOW() - INTERVAL $db_clean_profiles DAY) OR (`DateLastNav` = '0000-00-00 00:00:00' AND `DateReg` < NOW() - INTERVAL $db_clean_profiles DAY)) AND (`Couple` > `ID` OR `Couple` = 0)");
            if ( $res ) {
                $db_clean_profiles_num = $res->rowCount();
                while ( $arr = $res->fetch() ) {
                    profile_delete($arr['ID']);
                }
                db_res("OPTIMIZE TABLE `Profiles`");
            }
        }

        if ( $db_clean_vkiss > 0 ) {
            $res = db_res("DELETE FROM `sys_greetings` WHERE `When` < NOW() - INTERVAL $db_clean_vkiss DAY");
            if ( $res ) {
                $db_clean_vkiss_num = db_affected_rows($res);
                db_res("OPTIMIZE TABLE `sys_greetings`");
            }
        }

        if ( $db_clean_msg > 0 ) {
            $res = db_res("DELETE FROM `sys_messages` WHERE `Date` < NOW() - INTERVAL $db_clean_msg DAY");
            if ( $res ) {
                $db_clean_msg_num = db_affected_rows($res);
                db_res("OPTIMIZE TABLE `sys_messages`");
            }
        }

        //--- Clean sessions ---//
        bx_import('BxDolSession');
        $oSession = BxDolSession::getInstance();
        $iSessions = $oSession->oDb->deleteExpired();

        // clean expired ip bans
        bx_import('BxDolAdminIpBlockList');
        $oBxDolAdminIpBlockList = new BxDolAdminIpBlockList();
        $iIps = $oBxDolAdminIpBlockList->deleteExpired();

        // clean old views
        bx_import('BxDolViews');
        $oBxViews = new BxDolViews('', 0);
        $iDeletedViews = $oBxViews->maintenance ();

        // clean old votes
        bx_import('BxDolVoting');
        $oBxVotes = new BxDolVoting('', 0);
        $iDeletedVotes = $oBxVotes->maintenance ();

        // clean comments ratings
        bx_import('BxDolCmts');
        $oBxCmts = new BxDolCmts('', 0);
        $iDeletedCommentVotes = $oBxCmts->maintenance ();

        echo "\n- Database cleaning -\n";
        echo "Deleted profiles: $db_clean_profiles_num\n";
        echo "Deleted virtual kisses: $db_clean_vkiss_num\n";
        echo "Deleted messages: $db_clean_msg_num\n";
        echo "Deleted sessions: $iSessions\n";
        echo "Deleted records from ip block list: $iIps\n";
        echo "Deleted views: $iDeletedViews\n";
        echo "Deleted votes: $iDeletedVotes\n";
        echo "Deleted comment votes: $iDeletedCommentVotes\n";
    }

    function del_old_all_files()
    {
        global $dir;

        $num_tmp = 0;
        $num_del = 0;

        $file_life = 86400;  // one day
        $dirToClean = array();
        $dirToClean[$dir['tmp']] = "/.*/";
        $dirToClean[$dir['cache']] = "/.*/";
        $dirToClean[BX_DIRECTORY_PATH_CACHE_PUBLIC] = "/^export/";

        foreach( $dirToClean as $value => $sRegExp) {
            if ( !( $lang_dir = opendir( $value ) ) ) {
                continue;
            } else {
                while ($lang_file = readdir( $lang_dir )) {
                    if (!preg_match($sRegExp, $lang_file))
                        continue;
                    $diff = time() - filectime( $value . $lang_file);
                    if ( $diff > $file_life && '.' != $lang_file && '..' != $lang_file && '.htaccess' !== $lang_file ) {
                        @unlink ($value . $lang_file);
                        ++$num_del;
                    }
                    ++$num_tmp;
                }
                closedir( $lang_dir );
            }
        }

        echo "\n- Temporary files check -\n";

        echo "Total temp files: $num_tmp\n";
        echo "Deleted temp files: $num_del\n";
    }

    function processAdminStats()
    {
        $iCounter = 0;
        $sMessage = "";

        $aStats = getSiteStatArray();
        foreach($aStats as $sName => $aStat) {
            if(empty($aStat['adm_query']))
                continue;

            $iNumber = (int)$GLOBALS['MySQL']->getOne($aStat['adm_query']);
            if($iNumber <= 0)
                continue;

            $sMessage .= '<p>' . $iNumber . ' ' . _t('_' . $aStat['capt'] . '_adm_stats') . ',</p>';
        }

        $aAdmins = $GLOBALS['MySQL']->getAll("SELECT * FROM `Profiles` WHERE `Role`&" . BX_DOL_ROLE_ADMIN . "<>0 AND `EmailNotify`='1'");
        if(!empty($sMessage) && !empty($aAdmins)) {
            $oEmailTemplate = new BxDolEmailTemplates();
            $aEmailParams = array(
                'MessageText' => $sMessage,
                'ViewLink' => BX_DOL_URL_ADMIN
            );

            foreach($aAdmins as $aAdmin) {
                $aMail = $oEmailTemplate->parseTemplate('t_AdminStats', $aEmailParams, $aAdmin['ID']);
                if(sendMail($aAdmin['Email'], $aMail['subject'], $aMail['body'], $aAdmin['ID'], array(), 'html', false, true))
                    $iCounter++;
            }
        }

        return $iCounter;
    }

    function processing()
    {
        global $MODE;

        // - Defaults -
        $MODE   = "_MAIL_";
        //$MODE = "_LIVE_";
        $DAY    = "_OBEY_";
        //$DAY  = "_FORCE_";
        define('NON_VISUAL_PROCESSING', 'YES');

        // - Always finish
        set_time_limit( 36000 );
        ignore_user_abort();

        // - Parameters check -
        for ( $i = 0; strlen( $argv[$i] ); $i++ ) {
            switch( $argv[$i] ) {
                case "--live": $MODE = "_LIVE_"; break;
                case "--mail": $MODE = "_MAIL_"; break;
                case "--force-day": $DAY = "_FORCE_"; break;
                case "--obey-day": $DAY = "_OBEY_"; break;
            }
        }

        if ( $MODE != "_LIVE_" )
            ob_start();

        $day = date( "d" );
        if ( getParam( "cmdDay" ) == $day && $DAY == "_OBEY_" ) {
            echo "Already done today, bailing out\n";
            $this->finish();
            return;
        }

        setParam( "cmdDay", $day );

        //========================================================================================================================

        // - Send Admin Stats notification
        $this->processAdminStats();

        // - Membership check -
        unmarkMembershipAsExpiringAll();

        echo "\n- Membership expiration letters -\n";

        $iExpireNotificationDays = (int)getParam("expire_notification_days");
        $bExpireNotifyOnce = getParam("expire_notify_once") == 'on';

        $iExpireLetters = 0;

        $aRow = $GLOBALS['MySQL']->getFirstRow("SELECT `ID` FROM `Profiles`");
        while(!empty($aRow)) {
            $aCurrentMem = getMemberMembershipInfo( $aRow['ID'] );
            // If expire_notification_days is -1 then notify after expiration
            if ( $aCurrentMem['ID'] == MEMBERSHIP_ID_STANDARD && $iExpireNotificationDays == -1 ) {
                // Calculate last UNIX Timestamp
                $iLastTimestamp = time() - 24 * 3600;
                $aLastMem = getMemberMembershipInfo( $aRow['ID'], $iLastTimestamp );
                if($aCurrentMem['ID'] != $aLastMem['ID']) {
                    $bMailResult = mem_expiration_letter($aRow['ID'], $aLastMem['Name'], -1);
                    if($bMailResult)
                        $iExpireLetters++;
                }
            }
            // If memberhip is not standard then check if it will change
            else if($aCurrentMem['ID'] != MEMBERSHIP_ID_STANDARD) {
            	// Calculate further UNIX Timestamp for expiration check
                $iFurtherTimestamp = time() + 24 * 3600;
                $aFurtherMem = getMemberMembershipInfo( $aRow['ID'], $iFurtherTimestamp );
                if($aCurrentMem['ID'] != $aFurtherMem['ID'] && $aFurtherMem['ID'] == MEMBERSHIP_ID_STANDARD)
					markMembershipAsExpiring($aRow['ID'], $aCurrentMem['ID'], $aCurrentMem['TransactionID']);

                // Calculate further UNIX Timestamp for notification letter
                $iFurtherTimestamp = time() + $iExpireNotificationDays * 24 * 3600;
                $aFurtherMem = getMemberMembershipInfo( $aRow['ID'], $iFurtherTimestamp );
                if($aCurrentMem['ID'] != $aFurtherMem['ID'] && $aFurtherMem['ID'] == MEMBERSHIP_ID_STANDARD) {
                    if(!$bExpireNotifyOnce || abs($iFurtherTimestamp - $aCurrentMem['DateExpires']) < 24 * 3600) {
                        $bMailResult = mem_expiration_letter( $aRow['ID'], $aCurrentMem['Name'], (int)(($aCurrentMem['DateExpires'] - time())/(24 * 3600)));
                        if($bMailResult)
                            $iExpireLetters++;
                    }
                }
            }

            $aRow = $GLOBALS['MySQL']->getNextRow();
        }

        echo "Send membership expire letters: $iExpireLetters letters\n";

        //========================================================================================================================

        // clear tmp folder --------------------------------------------------------------------------

        $this->del_old_all_files();

        // ----------------------------------------------------------------------------------
        $this->clean_database();

        $this->finish();
    }
}
