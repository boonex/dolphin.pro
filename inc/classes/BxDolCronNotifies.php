<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );
require_once('BxDolCron.php');

class BxDolCronNotifies extends BxDolCron
{
    function processing()
    {
        global $site;

        set_time_limit( 36000 );
        ignore_user_abort();

        $sResult = "";
        $iPerStart = (int)trim(getParam('msgs_per_start'));

        $iFullCount = (int)$GLOBALS['MySQL']->getOne('SELECT COUNT(*) FROM `sys_sbs_queue`');
        if($iFullCount) {
            $iProcess = $iFullCount < $iPerStart ? $iFullCount : $iPerStart;

            $sResult .= "\n- Start email send -\n";
            $sResult .= "Total queued emails: " . $iFullCount . "\n";
            $sResult .= "Ready for send: " . $iProcess . "\n";

            $aMails = $GLOBALS['MySQL']->getAll("SELECT `id`, `email`, `subject`, `body` FROM `sys_sbs_queue` ORDER BY `id` LIMIT 0, " . $iProcess);

            $iSent = 0;
            $aIds = array();
            foreach($aMails as $aMail) {
                $aIds[] = $aMail['id'];
                if(sendMail($aMail['email'], $aMail['subject'], $aMail['body']))
                    $iSent++;
                else
                    $sResult .= "Cannot send message to " . $aMail['email'] . "\n";
            }
            $GLOBALS['MySQL']->query("DELETE FROM `sys_sbs_queue` WHERE `id` IN ('" . implode("','", $aIds) . "')");

            $sResult .= "Processed emails: " . $iSent . "\n";
            sendMail($site['email'], $site['title'] . ": Periodic Report", $sResult, 0, array(), 'text');
        }
    }
}
