<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxDolXMLRPCMessages
{

    function getMessagesInbox($sUser, $sPwd)
    {
        return BxDolXMLRPCMessages::_getMessages($sUser, $sPwd, true);
    }

    function getMessagesSent($sUser, $sPwd)
    {
        return BxDolXMLRPCMessages::_getMessages($sUser, $sPwd, false);
    }

    function getMessageInbox($sUser, $sPwd, $iMsgId)
    {
        return BxDolXMLRPCMessages::_getMessage($sUser, $sPwd, $iMsgId, true);
    }

    function getMessageSent($sUser, $sPwd, $iMsgId)
    {
        return BxDolXMLRPCMessages::_getMessage($sUser, $sPwd, $iMsgId, false);
    }

    function sendMessage($sUser, $sPwd, $sRecipient, $sSubj, $sText, $sSendTo)
    {
        $aRet = array ();
        if (!($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        bx_import('BxTemplMailBox');

        $sRecipient = process_db_input ($sRecipient, BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION);
        $aRecipient = db_arr("SELECT * FROM `Profiles` WHERE `NickName` = '$sRecipient'");
        if (!$aRecipient)
            return new xmlrpcval (BX_MAILBOX_SEND_UNKNOWN_RECIPIENT);

        $aMailBoxSettings = array ('member_id' => $iId);
        $oMailBox = new BxTemplMailBox('mail_page_compose', $aMailBoxSettings);

        $aComposeSettings = array (
            'send_copy' => 'recipient' == $sSendTo || 'both' == $sSendTo ? true : false,
            'notification' => false,
            'send_copy_to_me' => 'me' == $sSendTo || 'both' == $sSendTo ? true : false,
        );
        $oMailBox->sendMessage($sSubj, nl2br($sText), $aRecipient['ID'], $aComposeSettings);
        return new xmlrpcval ($oMailBox->iSendMessageStatusCode);
    }

    function _getMessage($sUser, $sPwd, $iMsgId, $isInbox)
    {
        $aRet = array ();
        if (!($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        $aMsgs = array ();
        $iMsgId = (int)$iMsgId;
        $sFieldsAdd = $isInbox ? ", `m`.`Sender` AS `AuthorID`" : ", `m`.`Recipient` AS `AuthorID` ";
        $aRow = db_arr ("SELECT
                `m`.`ID`, UNIX_TIMESTAMP(`m`.`Date`) AS `Date`, `m`.`Sender`, `m`.`Recipient`, `m`.`Subject`, `m`.`Text`, `m`.`New` $sFieldsAdd
            FROM `sys_messages` AS `m`
            WHERE `m`.`ID` = '$iMsgId'");
        if ($aRow) {
            $sIcon = BxDolXMLRPCUtil::getThumbLink($isInbox ? $aRow['Sender'] : $aRow['Recipient'], 'thumb');
            $aMsg = array (
                'ID' => new xmlrpcval($aRow['ID']),
                'Date' => new xmlrpcval(defineTimeInterval($aRow['Date'])),
                'Sender' => new xmlrpcval($aRow['Sender']),
                'Recipient' => new xmlrpcval($aRow['Recipient']),
                'Subject' => new xmlrpcval($aRow['Subject']),
                'Text' => new xmlrpcval($aRow['Text']),
                'New' => new xmlrpcval($aRow['New']),
                'Nick' => new xmlrpcval(getUsername($aRow['AuthorID'])),
                'UserTitleInterlocutor' => new xmlrpcval(getNickName($aRow['AuthorID'])),
                'Thumb' => new xmlrpcval($sIcon),
            );
            if ($isInbox && $aRow['New'])
                db_res("UPDATE `sys_messages` SET `New` = 0 WHERE `ID` = '$iMsgId'");
        } else {
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));
        }
        return new xmlrpcval ($aMsg, "struct");
    }

    function _getMessages($sUser, $sPwd, $isInbox)
    {
        $aRet = array ();
        if (!($iId = BxDolXMLRPCUtil::checkLogin ($sUser, $sPwd)))
            return new xmlrpcresp(new xmlrpcval(array('error' => new xmlrpcval(1,"int")), "struct"));

        $aMsgs = array ();

        if ($isInbox)
            $sWhere = "`Recipient` = '$iId' AND NOT FIND_IN_SET('recipient', `Trash`)";
        else
            $sWhere = "`Sender` = '$iId' AND NOT FIND_IN_SET('sender', `Trash`)";

        $sFieldsAdd = $isInbox ? ", `m`.`Sender` AS `AuthorID`" : ", `m`.`Recipient` AS `AuthorID` ";
        $r = db_res ("SELECT
                `m`.`ID`, UNIX_TIMESTAMP(`m`.`Date`) AS `Date`, `m`.`Sender`, `m`.`Recipient`, `m`.`Subject`, `m`.`New` $sFieldsAdd
            FROM `sys_messages` AS `m`
            INNER JOIN `Profiles` as `p` ON (`p`.`ID` = `m`.`Sender`)
            WHERE $sWhere
            ORDER BY `Date` DESC");
        while ($aRow = $r->fetch()) {
            $sIcon = BxDolXMLRPCUtil::getThumbLink($isInbox ? $aRow['Sender'] : $aRow['Recipient'], 'thumb');
            $aMsg = array (
                'ID' => new xmlrpcval($aRow['ID']),
                'Date' => new xmlrpcval(defineTimeInterval($aRow['Date'])),
                'Sender' => new xmlrpcval($aRow['Sender']),
                'Recipient' => new xmlrpcval($aRow['Recipient']),
                'Subject' => new xmlrpcval($aRow['Subject']),
                'New' => new xmlrpcval($aRow['New']),
                'Nick' => new xmlrpcval(getUsername($aRow['AuthorID'])),
                'UserTitleInterlocutor' => new xmlrpcval(getNickName($aRow['AuthorID'])),
                'Thumb' => new xmlrpcval($sIcon),
            );
            $aMsgs[] = new xmlrpcval($aMsg, 'struct');
        }
        return new xmlrpcval ($aMsgs, "array");
    }
}
