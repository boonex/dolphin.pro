<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');
bx_import('BxDolEmailTemplates');

class BxDolSubscriptionQuery extends BxDolDb
{
    var $_sPrefix;
    var $_oSubscription;

    /**
     * constructor
     */
    function __construct(&$oSubscription)
    {
        parent::__construct();

        $this->_oSubscription = &$oSubscription;
        $this->_sPrefix = 'sys_sbs_';
    }
    function isSubscribed($aParams)
    {
        $iType = BX_DOL_SBS_TYPE_MEMBER;
        if(!isset($aParams['user_id']) || (int)$aParams['user_id'] == 0) {
            $aParams['user_id'] = 0;
            if(!empty($aParams['name']) && !empty($aParams['email']))
                $aParams['user_id'] = (int)(int)$this->getOne("SELECT `id` FROM `" . $this->_sPrefix . "users` WHERE `name`='" . $aParams['name'] . "' AND `email`='" . $aParams['email'] . "' LIMIT 1");

            $iType = BX_DOL_SBS_TYPE_VISITOR;
        }

        $sSql = "SELECT
                    `tse`.`id` AS `id`
                FROM `" . $this->_sPrefix . "entries` AS `tse`
                LEFT JOIN `" . $this->_sPrefix . "types` AS `tst` ON `tse`.`subscription_id`=`tst`.`id`
                WHERE
                    `tst`.`unit`='" . $aParams['unit'] . "' AND
                    " . (!empty($aParams['action']) ? "`tst`.`action`='" . $aParams['action'] . "' AND " : "") . "
                    `tse`.`subscriber_id`='" . $aParams['user_id'] . "' AND
                    `tse`.`subscriber_type`='" . $iType . "'" .
                    ((int)$aParams['object_id'] != 0 ? " AND `tse`.`object_id`='" . $aParams['object_id'] . "'" : "");
        return !empty($aParams['user_id']) && (int)$this->getOne($sSql) > 0;
    }
    function getSubscription($sUnit, $sAction)
    {
        $sSql = "SELECT
               `id` AS `id`,
               `unit` AS `unit`,
               `action` AS `action`,
               `template` AS `template`,
               `params` AS `params`
            FROM `" . $this->_sPrefix . "types`
            WHERE `unit`= ? AND `action`= ?
            LIMIT 1";
        return $this->getRow($sSql, [$sUnit, $sAction]);
    }
    function getSubscriptions($sUnit, $sAction = '')
    {
        $sSql = "SELECT
               `id` AS `id`,
               `unit` AS `unit`,
               `action` AS `action`,
               `template` AS `template`,
               `params` AS `params`
            FROM `" . $this->_sPrefix . "types`
            WHERE `unit`= ? " . (!empty($sAction) ? " AND `action`='" . $sAction . "'" : "");
        return $this->getAll($sSql, [$sUnit]);
    }
    function getSubscriptionsByUser($iUserId)
    {
        $sSql = "SELECT
               `tt`.`id` AS `id`,
               `tt`.`unit` AS `unit`,
               `tt`.`action` AS `action`,
               `tt`.`params` AS `params`,
               (SELECT
                        GROUP_CONCAT(`ste`.`id` ORDER BY `ste`.`id`)
                    FROM `" . $this->_sPrefix . "entries` AS `ste`
                    LEFT JOIN `" . $this->_sPrefix . "types` AS `stt` ON `ste`.`subscription_id`=`stt`.`id`
                    WHERE `stt`.`unit`=`tt`.`unit` AND `ste`.`object_id`=`te`.`object_id`
                    GROUP BY `stt`.`unit`, `ste`.`object_id`) AS `entry_id`,
               `te`.`object_id` AS `object_id`
            FROM `" . $this->_sPrefix . "entries` AS `te`
            LEFT JOIN `" . $this->_sPrefix . "types` AS `tt` ON `te`.`subscription_id`=`tt`.`id`
            WHERE `tt`.`action`='' AND `te`.`subscriber_id`= ? AND `te`.`subscriber_type`='" . BX_DOL_SBS_TYPE_MEMBER . "'
            ORDER BY `tt`.`unit`, `te`.`object_id`";
        return $this->getAll($sSql, [$iUserId]);
    }
    function addSubscription($aParams)
    {
        switch($aParams['type']) {
            case BX_DOL_SBS_TYPE_VISITOR:
                $sUserName = process_db_input($aParams['user_name'], BX_TAGS_STRIP);
                $sUserEmail = process_db_input($aParams['user_email'], BX_TAGS_STRIP);
                if(empty($sUserName) || empty($sUserEmail) || !(bool)preg_match('/^([a-z0-9\+\_\-\.]+)@([a-z0-9\+\_\-\.]+)$/i', $sUserEmail))
                    return array('code' => 4, 'message' => _t('_sys_txt_sbs_empty_name_email'));

                $iUserId = (int)$this->getOne("SELECT `id` FROM `" . $this->_sPrefix . "users` WHERE `email`='" . $sUserEmail . "' LIMIT 1");
                if($iUserId != 0)
                    break;

                $mixedResult = $this->query("INSERT INTO `" . $this->_sPrefix . "users`(`name`, `email`, `date`) VALUES('" . $sUserName . "', '" . $sUserEmail . "', UNIX_TIMESTAMP())");
                if($mixedResult === false)
                    return array('code' => 1, 'message' => _t('_sys_txt_sbs_cannot_save_visitor'));

                $iUserId = (int)$this->lastId();
                break;
            case BX_DOL_SBS_TYPE_MEMBER:
                $aProfileInfo = getProfileInfo((int)$aParams['user_id']);

                $iUserId = $aProfileInfo['ID'];
                $sUserName = getNickName($aProfileInfo['ID']);
                $sUserEmail = $aProfileInfo['Email'];
                break;
        }

        $aSubscriptions = $this->getSubscriptions($aParams['unit'], $aParams['action']);
        if(!is_array($aSubscriptions) || empty($aSubscriptions))
            return array('code' => 2, 'message' => _t('_sys_txt_sbs_cannot_find_subscription'));

        $aTemplateParams = array();
        $aResults = array();
        foreach($aSubscriptions as $aSubscription) {
            if($aSubscription['action'] == $aParams['action'] && !empty($aSubscription['params'])) {
                $oFunction = function($arg1, $arg2, $arg3) use ($aSubscription) {
                    return eval($aSubscription['params']);
                };

                $aUnitParams = $oFunction($aParams['unit'], $aParams['action'], $aParams['object_id']);
            }

            /*
            if(empty($aSubscription['action']))
                continue;
            */

            $iEntryId = (int)$this->getOne("SELECT `id` FROM `" . $this->_sPrefix . "entries` WHERE `subscriber_id`='" . $iUserId . "' AND `subscriber_type`='" . $aParams['type'] . "' AND `subscription_id`='" . $aSubscription['id'] . "' AND `object_id`='" . (int)$aParams['object_id'] . "' LIMIT 1");
            if(!empty($iEntryId))
                return array('code' => 3, 'message' => _t('_sys_txt_sbs_already_subscribed'));

            $iResult = (int)$this->query("INSERT INTO `" . $this->_sPrefix . "entries`(`subscriber_id`, `subscriber_type`, `subscription_id`, `object_id`) VALUES('" . $iUserId . "', '" . $aParams['type'] . "', '" . $aSubscription['id'] . "', '" . (int)$aParams['object_id'] . "')");
            if($iResult > 0)
                $aResults[] = $this->lastId();
        }

        if(count($aResults) > 0) {
            $oEmailTemplate = new BxDolEmailTemplates();
            $aTemplateParams = array (
                'RealName' => $sUserName,
                'SysUnsubscribeLink' => $this->_oSubscription->_getUnsubscribeLink($aResults)
            );
            if(isset($aUnitParams['template']))
                $aTemplateParams = array_merge($aTemplateParams, $aUnitParams['template']);

            $aMail = $oEmailTemplate->parseTemplate('t_Subscription', $aTemplateParams);
            sendMail($sUserEmail, $aMail['subject'], $aMail['body']);

            $aResult = array('code' => 0, 'message' => _t('_sys_txt_sbs_success_subscribe'));
        } else
            $aResult = array('code' => 5, 'message' => _t('_sys_txt_sbs_error_occured'));

        return $aResult;
    }
    function deleteSubscription($aParams)
    {
        switch($aParams['type']) {
            case BX_DOL_SBS_TYPE_VISITOR:
                if(isset($aParams['user_id']))
                    $iUserId = (int)$aParams['user_id'];
                else if(isset($aParams['user_name']) && isset($aParams['user_email']))
                    $iUserId = (int)$this->getOne("SELECT `id` FROM `" . $this->_sPrefix . "users` WHERE `name`='" . process_db_input($aParams['user_name'], BX_TAGS_STRIP) . "' AND `email`='" . process_db_input($aParams['user_email'], BX_TAGS_STRIP) . "' LIMIT 1");

                $iUserType = BX_DOL_SBS_TYPE_VISITOR;
                break;
            case BX_DOL_SBS_TYPE_MEMBER:
                $iUserId = (int)$aParams['user_id'];
                $iUserType = BX_DOL_SBS_TYPE_MEMBER;
                break;
        }

        $iResult = 0;
        //--- Unsubscribe when the button is clicked ---//
        if(isset($aParams['unit']) && isset($aParams['action'])) {
            $aSubscriptions = $this->getSubscriptions($aParams['unit'], $aParams['action']);
            if(!is_array($aSubscriptions) || empty($aSubscriptions))
                return array('code' => 2, 'message' => _t('_sys_txt_sbs_cannot_find_subscription'));

            foreach($aSubscriptions as $aSubscription)
                $iResult += (int)$this->query("DELETE FROM `" . $this->_sPrefix . "entries` WHERE `subscriber_id`='" . $iUserId . "' AND `subscriber_type`='" . $iUserType . "' AND `subscription_id`='" . $aSubscription['id'] . "'" . ((int)$aParams['object_id'] != 0 ? " AND `object_id`='" . (int)$aParams['object_id'] . "'" : ""));
        }
        //--- Unsubscribe when the object is deleted ---//
        else if(isset($aParams['unit']) && isset($aParams['object_id'])) {
            $aSubscriptions = $this->getSubscriptions($aParams['unit']);
            if(is_array($aSubscriptions) && !empty($aSubscriptions)) {
                foreach($aSubscriptions as $aSubscription)
                    $aIds[] = $aSubscription['id'];

                $iResult = (int)$this->query("DELETE FROM `" . $this->_sPrefix . "entries` WHERE `subscription_id` IN ('" . implode("','", $aIds) . "') AND `object_id`='" . (int)$aParams['object_id'] . "'");
            } else
                $iResult = 0;
        }
        //--- Unsubscribe when the link with SID is clicked ---//
        else if(isset($aParams['sid'])) {
            $aIds = explode(",", base64_decode(urldecode($aParams['sid'])));
            if(is_array($aIds) && !empty($aIds)) {
                foreach ($aIds as $k => $v)
                    $aIds[$k] = (int)$v;

                list($iUserId, $iUserType) = $this->getRow("SELECT `subscriber_id`, `subscriber_type` FROM `" . $this->_sPrefix . "entries` WHERE `id`= ? LIMIT 1", [$aIds[0]], PDO::FETCH_NUM);
                
                $iResult = (int)$this->query("DELETE FROM `" . $this->_sPrefix . "entries` WHERE `id` IN ('" . implode("','", $aIds) . "')");
            }
        }
        //--- Unsubscribe the user from all subscriptions ---//
        else
            $iResult = (int)$this->query("DELETE FROM `" . $this->_sPrefix . "entries` WHERE `subscriber_id`='" . $iUserId . "' AND `subscriber_type`='" . $iUserType . "'");

        if($iUserType == BX_DOL_SBS_TYPE_VISITOR || (isset($aParams['unit']) && isset($aParams['object_id']))) {
            $iSbsEntries = (int)$this->getOne("SELECT COUNT(`id`) FROM `" . $this->_sPrefix . "entries` WHERE `subscriber_id`='" . $iUserId . "' AND `subscriber_type`='" . BX_DOL_SBS_TYPE_VISITOR . "' LIMIT 1");
            if($iSbsEntries == 0)
                $this->query("DELETE FROM `" . $this->_sPrefix . "users` WHERE `id`='" . $iUserId . "' LIMIT 1");
        }

        return $iResult > 0 ? array('code' => 0, 'message' => _t('_sys_txt_sbs_success_unsubscribe')) : array('code' => 4, 'message' => _t('_sys_txt_sbs_already_unsubscribed'));
    }
    function sendDelivery($aParams)
    {
        $iQueued = 0;

        $oEmailTemplates = new BxDolEmailTemplates();
        $aSubscription = $this->getSubscription($aParams['unit'], $aParams['action']);

        if(!empty($aSubscription['params'])) {
            $oFunction = function($arg1, $arg2, $arg3) use ($aSubscription) {
                return eval($aSubscription['params']);
            };

            $aUnitParams = $oFunction($aParams['unit'], $aParams['action'], $aParams['object_id']);
        }

        if(isset($aUnitParams['skip']) && $aUnitParams['skip'] === true)
            return $iQueued;

        $aSubscribers = $this->getAll("SELECT `id` AS `subscription_id`, `subscriber_id` AS `id`, `subscriber_type` AS `type` FROM `" . $this->_sPrefix . "entries` WHERE `subscription_id`='" . (empty($aSubscription['id']) ? 0 : $aSubscription['id']) . "'" . ((int)$aParams['object_id'] != 0 ? " AND `object_id`='" . $aParams['object_id'] . "'" : ""));
        foreach($aSubscribers as $aSubscriber) {
            switch($aSubscriber['type']) {
                case BX_DOL_SBS_TYPE_VISITOR:
                    $sSql = "SELECT '0' AS `id`, `name`, `email` FROM `" . $this->_sPrefix . "users` WHERE `id`= ? LIMIT 1";
                    break;
                case BX_DOL_SBS_TYPE_MEMBER:
                    $sSql = "SELECT `ID` AS `id`, `NickName` AS `name`, `Email` AS `email` FROM `Profiles` WHERE `ID`= ? LIMIT 1";
                    break;
            }
            $aUser = $this->getRow($sSql, [$aSubscriber['id']]);

            //--- Parse message ---//
            $sSql = "SELECT
                        `tse`.`id` AS `id`
                    FROM `" . $this->_sPrefix . "entries` AS `tse`
                    LEFT JOIN `" . $this->_sPrefix . "types` AS `tst` ON `tse`.`subscription_id`=`tst`.`id` AND `tst`.`unit`='" . $aParams['unit'] . "' AND `tst`.`action`<>''
                    WHERE `tse`.`subscriber_id`='" . $aSubscriber['id'] . "' AND `tse`.`subscriber_type`='" . $aSubscriber['type'] . "'"  . ((int)$aParams['object_id'] != 0 ? " AND `object_id`='" . $aParams['object_id'] . "'" : "");
            $aEntries = $this->getColumn($sSql);

            $aTemplateParams = array(
                'RealName' => $aUser['id'] ? getNickName($aUser['id']) : $aUser['name'],
                'Email' => $aUser['email'],
                'ObjectId' => $aParams['object_id'],
                'UnsubscribeLink' => $this->_oSubscription->_getUnsubscribeLink((int)$aSubscriber['subscription_id']),
                'UnsubscribeAllLink' => $this->_oSubscription->_getUnsubscribeLink($aEntries),
            );
            if(isset($aUnitParams['template']))
                $aTemplateParams = array_merge($aTemplateParams, $aUnitParams['template']);

            $aMail = $oEmailTemplates->parseTemplate($aSubscription['template'], $aTemplateParams, (int)$aUser['id']);

            $iQueued += (int)$this->query("INSERT INTO `" . $this->_sPrefix . "queue`(`email`, `subject`, `body`) VALUES('" . $aUser['email'] . "', '" . process_db_input($aMail['subject'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION) . "', '" . process_db_input($aMail['body'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION) . "')");
        }

        return $iQueued;
    }
    function getSubscribersCount($iType)
    {
        switch($iType) {
            case BX_DOL_SBS_TYPE_VISITOR:
                $sSql = "SELECT
                           COUNT(DISTINCT `tsu`.`id`) AS `count`
                        FROM `" . $this->_sPrefix . "users` AS `tsu`
                        INNER JOIN `" . $this->_sPrefix . "entries` AS `tse` ON `tsu`.`id`=`tse`.`subscriber_id` AND `tse`.`subscriber_type`='" . BX_DOL_SBS_TYPE_VISITOR . "'
                        WHERE 1
                        LIMIT 1";
                break;
            case BX_DOL_SBS_TYPE_MEMBER:
                $sSql = "SELECT
                           COUNT(DISTINCT `tsu`.`ID`) AS `count`
                        FROM `Profiles` AS `tsu`
                        INNER JOIN `" . $this->_sPrefix . "entries` AS `tse` ON `tsu`.`ID`=`tse`.`subscriber_id` AND `tse`.`subscriber_type`='" . BX_DOL_SBS_TYPE_MEMBER . "'
                        WHERE 1
                        LIMIT 1";
                break;
        }
        return (int)$this->getOne($sSql);
    }
    function getSubscribers($iType, $iStart, $iCount)
    {
        switch($iType) {
            case BX_DOL_SBS_TYPE_VISITOR:
                $sSql = "SELECT
                           `tsu`.`id` AS `id`,
                           `tsu`.`name` AS `name`,
                           `tsu`.`email` AS `email`
                        FROM `" . $this->_sPrefix . "users` AS `tsu`
                        INNER JOIN `" . $this->_sPrefix . "entries` AS `tse` ON `tsu`.`id`=`tse`.`subscriber_id` AND `tse`.`subscriber_type`='" . BX_DOL_SBS_TYPE_VISITOR . "'
                        WHERE 1
                        GROUP BY `tsu`.`id`
                        LIMIT " . $iStart . "," . $iCount;
                break;
            case BX_DOL_SBS_TYPE_MEMBER:
                $sSql = "SELECT
                           `tsu`.`ID` AS `id`,
                           `tsu`.`NickName` AS `name`,
                           `tsu`.`Email` AS `email`
                        FROM `Profiles` AS `tsu`
                        INNER JOIN `" . $this->_sPrefix . "entries` AS `tse` ON `tsu`.`ID`=`tse`.`subscriber_id` AND `tse`.`subscriber_type`='" . BX_DOL_SBS_TYPE_MEMBER . "'
                        WHERE 1
                        GROUP BY `tsu`.`ID`
                        LIMIT " . $iStart . "," . $iCount;
                break;
        }
        return $this->getAll($sSql);
    }
}
