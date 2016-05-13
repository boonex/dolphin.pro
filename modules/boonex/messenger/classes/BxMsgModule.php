<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolModule.php');

class BxMsgModule extends BxDolModule
{
    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        //--- Define Membership Actions ---//
        $aActions = $this->_oDb->getMembershipActions();
        foreach($aActions as $aAction) {
            $sName = 'ACTION_ID_' . strtoupper(str_replace(' ', '_', $aAction['name']));
            if(!defined($sName))
                define($sName, $aAction['id']);
        }
    }

    function actionGetInvitation()
    {
        $aForm = array(
            'form_attrs' => array(
                'name' => 'invitation_form'
            ),
            'params' => array(
                'remove_form' => true
            ),
            'inputs' => array(
                array(
                    'type' => 'input_set',
                    'colspan' => true,
                    0 => array(
                        'type' => 'button',
                        'name' => 'accept',
                        'value' => _t("_messenger_invitation_accept"),
                        'attrs' => array(
                            'class' => 'bx-btn-small',
                            'onclick' => 'BxMsgPerformAction("__sender_id__", "accept");'
                        )
                    ),
                    1 => array(
                        'type' => 'button',
                        'name' => 'decline',
                        'value' => _t("_messenger_invitation_decline"),
                        'attrs' => array(
                            'class' => 'bx-btn-small',
                            'onclick' => 'BxMsgPerformAction("__sender_id__", "decline");'
                        )
                    ),
                    2 => array(
                        'type' => 'button',
                        'name' => 'block',
                        'value' => _t("_messenger_invitation_block"),
                        'attrs' => array(
                            'class' => 'bx-btn-small',
                            'onclick' => 'BxMsgPerformAction("__sender_id__", "block");'
                        )
                    ),
                    3 => array(
                        'type' => 'button',
                        'name' => 'report',
                        'value' => _t("_messenger_invitation_report"),
                        'attrs' => array(
                            'class' => 'bx-btn-small',
                            'onclick' => 'BxMsgPerformAction("__sender_id__", "spam");'
                        )
                    )
                )
            )
        );

        $oForm = new BxTemplFormView($aForm);

        $aVariables = array(
            'invitation_buttons' => $oForm->getCode()
        );
        $sResult = $this->_oTemplate->parseHtmlByName("invitation.html", $aVariables);
        return $sResult;
    }

    function getMessenger($iSndId, $sSndPassword, $iRspId)
    {
        if(!empty($iSndId) && !empty($sSndPassword) && !empty($iRspId)) {
            $aResult = checkAction($iSndId, ACTION_ID_USE_MESSENGER, true);
            if($aResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED)
                $sResult = getApplicationContent('im', 'user', array('sender' => $iSndId, 'password' => $sSndPassword, 'recipient' => $iRspId), false);
            else
                $sResult = MsgBox($aResult[CHECK_ACTION_MESSAGE]);
        } else
            $sResult = MsgBox(_t('_messenger_err_not_logged_in'));

        return $sResult;
    }

    function actionGetThumbnail($iId)
    {
        return get_member_thumbnail($iId, "left");
    }

    function serviceGetInvitation()
    {
        global $sRayXmlUrl;

        $iId = isset($_COOKIE['memberID']) ? (int)$_COOKIE['memberID'] : 0;
        $sPassword = isset($_COOKIE['memberPassword']) ? $_COOKIE['memberPassword'] : '';

        $sResult = '';
        if(!empty($iId) && !empty($sPassword)) {
            $aResult = checkAction($iId, ACTION_ID_USE_MESSENGER);
            if($aResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED) {
                $sHomeUrl = $this->_oConfig->getHomeUrl();
                ob_start();
?>
<script language="javascript" type="text/javascript">
<!--
    var BxMsgTopMargin = 25;
    var BxMsgUpdateInterval = <?=rayGetSettingValue("im", "updateInterval") * 1000?>;
    if(isNaN(BxMsgUpdateInterval)) BxMsgUpdateInterval = 30000;
    var sBxMsgMemberId = "<?=$iId?>";
    var sBxMsgMemberPassword = "<?=$sPassword?>";
    var sBxMsgSiteUrl = "<?=BX_DOL_URL_ROOT?>";
    var sBxMsgGetUrl = "<?=BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri()?>";
    var sBxMsgUpdateUrl = "<?=$sRayXmlUrl?>?module=im&action=updateInvite&recipient=<?=$iId?>";
    BxMsgUpdate();
-->
</script>
<?php
                $this->_oTemplate->addCss("invitation.css");
                $this->_oTemplate->addJs("invite.js");

                $sResult .= ob_get_clean();
            }
        }
        return $sResult;
    }
    function serviceGetActionLink($iMemberId, $iProfileId)
    {
        $aResult = checkAction($iMemberId, ACTION_ID_USE_MESSENGER);
        if($iMemberId > 0 && get_user_online_status($iProfileId) && $aResult[CHECK_ACTION_RESULT] == CHECK_ACTION_RESULT_ALLOWED && $iMemberId != $iProfileId && !isBlocked($iProfileId, $iMemberId))
            $sResult = _t('_messenger_actions_item');
        else
            $sResult = '';

        return $sResult;
    }
}
