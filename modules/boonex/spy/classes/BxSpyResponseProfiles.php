<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlerts.php');

    class BxSpyResponseProfiles extends BxDolAlertsResponse
    {
        var $_oModule;

        function __construct($oModule)
        {
            parent::__construct();

            $this->_oModule = $oModule;
        }

        function response($oAlert)
        {
            $iSenderId = $oAlert->iSender;

            $iRecipientId = $oAlert->iObject;
            $sRecipientName = getNickName($iRecipientId);
            $sRecipientLink = getProfileLink($iRecipientId);

            $iCommentId = 0;
            $aParams = array();
            switch($oAlert->sUnit . '_' .$oAlert->sAction ) {
                case 'profile_join' :
                    $aParams = array(
                        'lang_key'  => '_bx_spy_profile_has_joined',
                        'params'    => array(
                            'profile_link' => $sRecipientLink,
                            'profile_nick' => $sRecipientName,
                        ),
                    );
                    $iSenderId = $oAlert -> iObject;
                    $iRecipientId = 0;
                    break;

                case 'profile_edit' :
                    $aParams = array(
                        'lang_key'  => '_bx_spy_profile_has_edited',
                        'params'    => array(
                            'profile_link' => $sRecipientLink,
                            'profile_nick' => $sRecipientName,
                        ),
                    );
                    $iRecipientId = 0;
                    break;

                case 'profile_edit_status_message' :
                    $aParams = array(
                        'lang_key'  => '_bx_spy_profile_has_edited_status_message',
                        'params'    => array(
                            'profile_link' => $sRecipientLink,
                            'profile_nick' => $sRecipientName,
                        ),
                    );
                    $iRecipientId = 0;
                    break;

                case 'profile_rate' :
                    if($iSenderId == $iRecipientId)
                        break;

                    $aSenderInfo = $this -> _getSenderInfo($iSenderId);
                    $sSenderNickName = $aSenderInfo['NickName'];
                    $sSenderProfileLink = $aSenderInfo['Link'];

                    $aParams = array(
                        'lang_key'  => '_bx_spy_profile_has_rated',
                        'params'    => array(
                            'sender_p_link' => $sSenderProfileLink,
                            'sender_p_nick' => $sSenderNickName,
                            'recipient_p_link' => $sRecipientLink,
                            'recipient_p_nick' => $sRecipientName,
                        ),
                    );
                    break;

                case 'profile_delete':
                    $this->_oModule->_oDb->deleteActivityByUser($iRecipientId);
                    break;

                case 'profile_commentPost' :
                    if($iSenderId == $iRecipientId || !isset($oAlert->aExtras['comment_id']) || (int)$oAlert->aExtras['comment_id'] == 0)
                        break;

                    $iCommentId = (int)$oAlert->aExtras['comment_id'];
                    $aSenderInfo = $this -> _getSenderInfo($iSenderId);
                    $sSenderNickName = $aSenderInfo['NickName'];
                    $sSenderProfileLink = $aSenderInfo['Link'];

                    $aParams = array(
                        'lang_key'  => '_bx_spy_profile_has_commented',
                        'params'    => array(
                            'sender_p_link' => $sSenderProfileLink,
                            'sender_p_nick' => $sSenderNickName,
                            'recipient_p_link' => $sRecipientLink,
                            'recipient_p_nick' => $sRecipientName,
                        ),
                    );
                    break;

                case 'profile_commentRemove':
                    if(!isset($oAlert->aExtras['comment_id']) || (int)$oAlert->aExtras['comment_id'] == 0)
                        break;

                    $this->_oModule->_oDb->deleteActivityByObject($oAlert->sUnit, $iRecipientId, (int)$oAlert->aExtras['comment_id']);
                    break;

                case 'friend_accept':
                    if($iSenderId == $iRecipientId)
                        break;

                    $aSenderInfo = $this -> _getSenderInfo($iSenderId);
                    $sSenderNickName = $aSenderInfo['NickName'];
                    $sSenderProfileLink = $aSenderInfo['Link'];

                    $aParams = array(
                        'lang_key'  => '_bx_spy_profile_friend_accept',
                        'params'    => array(
                            'sender_p_link' => $sRecipientLink,
                            'sender_p_nick' => $sRecipientName,
                            'recipient_p_link' => $sSenderProfileLink,
                            'recipient_p_nick' => $sSenderNickName,
                        ),
                    );
                    break;
            }

            if(empty($aParams))
                return;

            // create new activity;
            $aParams['spy_type'] = 'profiles_activity';

            $iActivityId = 0;
            if($iSenderId || (!$iSenderId && $this->_oModule->_oConfig->bTrackGuestsActivites))
                $iActivityId = $this->_oModule->_oDb->createActivity(
                    $oAlert->sUnit,
                    $oAlert->sAction,
                    $oAlert->iObject,
                    $iCommentId,
                    $iSenderId,
                    $iRecipientId,
                    $aParams
                );

            if(!$iActivityId)
                return;

            // try to define all profile's friends;
            $aFriends = getMyFriendsEx($iSenderId);
            if(empty($aFriends) || !is_array($aFriends))
                return;

            // attach activity to friend;
            foreach($aFriends as $iFriendId => $aItems)
                $this->_oModule -> _oDb -> attachFriendEvent($iActivityId, $iSenderId, $iFriendId);
        }

        function _getSenderInfo($iSenderId)
        {
            $sSenderNickName = getNickName($iSenderId);
            $sSenderLink = $sSenderNickName ? getProfileLink($iSenderId) : 'javascript:void(0)';
            if(!$sSenderNickName)
                $sSenderNickName = _t('_Guest');

            $aRet = array(
                'NickName' => $sSenderNickName,
                'Link' => $sSenderLink,
            );

           return $aRet;
        }
    }
