<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolSubscriptionQuery');

define('BX_DOL_SBS_TYPE_VISITOR', 0);
define('BX_DOL_SBS_TYPE_MEMBER', 1);

/**
 * Subscriptions for any content changes.
 *
 * Integration of the content with subscriptions engine allows
 * site member and visitors to subscribe to any content changes.
 *
 * Related classes:
 *  BxDolSubscriptionQuery - database queries.
 *
 * Example of usage:
 * 1. Register all your subscriptions in `sys_sbs_types` database table.
 * 2. Add necessary email templates in the `sys_email_templates` table.
 * 3. Add necessary HTML/JavaScript data on the page where the 'Subscribe'
 *    button would be displayed. Use the following code
 *
 *    $oSubscription = BxDolSubscription::getInstance();
 *    $oSubscription->getData();
 *
 * 4. Add Subscribe/Unsubscribe button using the following code.
 *
 *    $oSubscription = new BxDolSubscription();
 *    $oSubscription->getButton($iUserId, $sUnit, $sAction, $iObjectId);
 *
 * @see an example of integration in the default Dolphin's modules(feedback, news, etc)
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxDolSubscription
{
    var $_oDb;
    var $_bDataAdded;
    var $_sJsObject;
    var $_sActionUrl;
    var $_sVisitorPopup;

    /**
     * constructor
     */
    function __construct()
    {
        $this->_oDb = new BxDolSubscriptionQuery($this);
        $this->_bDataAdded = false;
        $this->_sJsObject = 'oBxDolSubscription';
        $this->_sActionUrl = $GLOBALS['site']['url'] . 'subscription.php';
        $this->_sVisitorPopup = 'sbs_visitor_popup';
    }

	static public function getInstance()
    {
        if(!isset($GLOBALS['bxDolClasses']['BxDolSubscription']))
        	$GLOBALS['bxDolClasses']['BxDolSubscription'] = new BxDolSubscription();

		return $GLOBALS['bxDolClasses']['BxDolSubscription'];
    }

    function getMySubscriptions()
    {
        global $oSysTemplate;
        $aUserInfo = getProfileInfo();

        $aSubscriptions = $this->_oDb->getSubscriptionsByUser((int)$aUserInfo['ID']);
        if(empty($aSubscriptions))
            return MsgBox(_t('_Empty'));

        $sContent = "";
        if((int)$aUserInfo['EmailNotify'] == 0)
            $sContent .= MsgBox(_t('_sbs_wrn_email_notify_disabled', BX_DOL_URL_ROOT . 'pedit.php?ID=' . (int)$aUserInfo['ID']));

        $aForm = array(
            'form_attrs' => array(
                'id' => 'sbs-subscriptions-form',
                'name' => 'sbs-subscriptions-form',
                'action' => bx_html_attribute($_SERVER['PHP_SELF']),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'params' => array(),
            'inputs' => array()
        );
        $sUnit = '';
        $bCollapsed = true;
        $sTmplRow = $oSysTemplate->getHtml('subscription_row.html');
        foreach($aSubscriptions as $aSubscription) {
            $oFunction = function($arg1, $arg2, $arg3) use ($aSubscription) {
                return eval($aSubscription['params']);
            };

            $aParams = $oFunction($aSubscription['unit'], $aSubscription['action'], $aSubscription['object_id']);
            if(isset($aParams['skip']) && $aParams['skip'] === true)
                continue;

            if($sUnit != $aSubscription['unit']) {
                if(!empty($sUnit))
                    $aForm['inputs'][$sUnit . '_end'] = array(
                        'type' => 'block_end'
                    );
                $aForm['inputs'][$aSubscription['unit'] . '_begin'] = array(
                    'type' => 'block_header',
                    'caption' => _t('_sbs_txt_title_' . $aSubscription['unit']),
                    'collapsable' => true,
                    'collapsed' => $bCollapsed
                );

                $sUnit = $aSubscription['unit'];
                $bCollapsed = true;
            }

            $sName = 'sbs-subscription_' . $aSubscription['entry_id'];
            $aForm['inputs'][$sName] = array(
                'type' => 'custom',
                'name' => $sName,
                'content' => $oSysTemplate->parseHtmlByContent($sTmplRow, array(
                    'js_object' => $this->_sJsObject,
                    'obj_link' => $aParams['template']['ViewLink'],
                    'obj_title' => $aParams['template']['Subscription'],
                    'unsbs_link' => $this->_getUnsubscribeLink($aSubscription['entry_id'])
                )),
                'colspan' => true
            );
        }

        $aForm['inputs'][$sUnit . '_end'] = array(
            'type' => 'block_end'
        );

        $oForm = new BxTemplFormView($aForm);
        $sContent .= $oForm->getCode();
        $sContent .= $this->_getJsCode();

        $GLOBALS['oTopMenu']->setCurrentProfileID((int)$aUserInfo['ID']);

        $oSysTemplate->addJs(array('BxDolSubscription.js'));
        $oSysTemplate->addCss(array('subscription.css'));
        $oSysTemplate->addJsTranslation('_sbs_wrn_unsubscribe');
        return $oSysTemplate->parseHtmlByName('default_margin.html', array('content' => $sContent));
    }
    function getData($bDynamic = false)
    {
        global $oSysTemplate;

        $sContent = '';
		if(!$this->_bDataAdded) {
	        $sContent .= $this->_getJsCode();

	        $aForm = array(
	            'form_attrs' => array(
	                'id' => 'sbs_form',
	                'name' => 'sbs_form',
	                'action' => $this->_sActionUrl,
	                'method' => 'post',
	                'enctype' => 'multipart/form-data',
	                'onSubmit' => 'javascript: return ' . $this->_sJsObject . '.send(this);'
	
	            ),
	            'inputs' => array (
	                'direction' => array (
	                    'type' => 'hidden',
	                    'name' => 'direction',
	                    'value' => ''
	                ),
	                'unit' => array (
	                    'type' => 'hidden',
	                    'name' => 'unit',
	                    'value' => ''
	                ),
	                'action' => array (
	                    'type' => 'hidden',
	                    'name' => 'action',
	                    'value' => ''
	                ),
	                'object_id' => array (
	                    'type' => 'hidden',
	                    'name' => 'object_id',
	                    'value' => ''
	                ),
	                'user_name' => array (
	                    'type' => 'text',
	                    'name' => 'user_name',
	                    'caption' => _t('_sys_txt_sbs_name'),
	                    'value' => '',
	                    'attrs' => array (
	                        'id' => 'sbs_name'
	                    )
	                ),
	                'user_email' => array (
	                    'type' => 'text',
	                    'name' => 'user_email',
	                    'caption' => _t('_sys_txt_sbs_email'),
	                    'value' => '',
	                    'attrs' => array (
	                        'id' => 'sbs_email'
	                    )
	                ),
	                'sbs_controls' => array (
	                    'type' => 'input_set',
	                    array (
	                        'type' => 'submit',
	                        'name' => 'sbs_subscribe',
	                        'value' => _t('_sys_btn_sbs_subscribe'),
	                        'attrs' => array(
	                            'onClick' => 'javascript:$("#' . $this->_sVisitorPopup . ' [name=\'direction\']").val(\'subscribe\')',
	                        )
	                    ),
	                    array (
	                        'type' => 'submit',
	                        'name' => 'sbs_unsubscribe',
	                        'value' => _t('_sys_btn_sbs_unsubscribe'),
	                        'attrs' => array(
	                            'onClick' => 'javascript:$("#' . $this->_sVisitorPopup . ' [name=\'direction\']").val(\'unsubscribe\')',
	                        )
	                    ),
	                )
	
	            )
	        );
	        $oForm = new BxTemplFormView($aForm);
	        $sContent .= PopupBox($this->_sVisitorPopup, _t('_sys_bcpt_subscribe'), $oSysTemplate->parseHtmlByName('default_margin.html', array(
	            'content' => $oForm->getCode()
	        )));

	        $this->_bDataAdded = true;
		}

        $sCssJs = '';
        $sCssJs .= $oSysTemplate->addCss(array('subscription.css', 'subscription_phone.css'), $bDynamic);
        $sCssJs .= $oSysTemplate->addJs(array('BxDolSubscription.js'), $bDynamic);
        return ($bDynamic ? $sCssJs : '') . $sContent;
    }
    function getButton($iUserId, $sUnit, $sAction = '', $iObjectId = 0)
    {
        if($this->_oDb->isSubscribed(array('user_id' => $iUserId, 'unit' => $sUnit, 'action' => $sAction, 'object_id' => $iObjectId)))
            $aResult = array(
                'title' => _t('_sys_btn_sbs_unsubscribe'),
                'script' => $this->_sJsObject . ".unsubscribe(" . $iUserId . ", '" . $sUnit . "', '" . $sAction . "', " . $iObjectId . ")"
            );
        else
            $aResult = array(
                'title' => _t('_sys_btn_sbs_subscribe'),
                'script' => $this->_sJsObject . ".subscribe(" . $iUserId . ", '" . $sUnit . "', '" . $sAction . "', " . $iObjectId . ")"
            );

        return $aResult;
    }

    function subscribeVisitor($sUserName, $sUserEmail, $sUnit, $sAction, $iObjectId = 0)
    {
        $aResult = $this->_processVisitor('add', $sUserName, $sUserEmail, $sUnit, $sAction, $iObjectId);
        return $aResult;
    }
    function unsubscribeVisitor($sUserName, $sUserEmail, $sUnit, $sAction, $iObjectId = 0)
    {
        return $this->_processVisitor('delete', $sUserName, $sUserEmail, $sUnit, $sAction, $iObjectId);
    }
    function subscribeMember($iUserId, $sUnit, $sAction, $iObjectId = 0)
    {
        return $this->_processMember('add', $iUserId, $sUnit, $sAction, $iObjectId);
    }
    function unsubscribeMember($iUserId, $sUnit, $sAction, $iObjectId = 0)
    {
        return $this->_processMember('delete', $iUserId, $sUnit, $sAction, $iObjectId);
    }
    function unsubscribe($aParams)
    {
        $aRequest = array();

        switch($aParams['type']) {
            case 'sid';
                $aRequest = array('sid' => $aParams['sid']);
                break;
            case 'object_id';
                $aRequest = array('unit' => $aParams['unit'], 'object_id' => $aParams['object_id']);
                break;
            case 'visitor':
                $aRequest = array(
                    'type' => BX_DOL_SBS_TYPE_VISITOR,
                    'user_id' => $aParams['id']
                );
                break;
            case 'member':
                $aRequest = array(
                    'type' => BX_DOL_SBS_TYPE_MEMBER,
                    'user_id' => $aParams['id']
                );
                break;
        }
        return $this->_oDb->deleteSubscription($aRequest);
    }
    function send($sUnit, $sAction, $iObjectId = 0, $aExtras = array())
    {
        return $this->_oDb->sendDelivery(array(
            'unit' => $sUnit,
            'action' => $sAction,
            'object_id' => $iObjectId
        ));
    }
    function getSubscribersCount($iType = BX_DOL_SBS_TYPE_VISITOR)
    {
        return $this->_oDb->getSubscribersCount($iType);
    }
    function getSubscribers($iType = BX_DOL_SBS_TYPE_VISITOR, $iStart = 0, $iCount = 1)
    {
        return $this->_oDb->getSubscribers($iType, $iStart, $iCount);
    }

    function _processMember($sDirection, $iUserId, $sUnit, $sAction, $iObjectId)
    {
        $sMethodName = $sDirection . 'Subscription';
        return $this->_oDb->$sMethodName(array(
            'type' => BX_DOL_SBS_TYPE_MEMBER,
            'user_id' => $iUserId,
            'unit' => $sUnit,
            'action' => $sAction,
            'object_id' => $iObjectId
        ));
    }
    function _processVisitor($sDirection, $sUserName, $sUserEmail, $sUnit, $sAction, $iObjectId)
    {
        $sMethodName = $sDirection . 'Subscription';
        return $this->_oDb->$sMethodName(array(
            'type' => BX_DOL_SBS_TYPE_VISITOR,
            'user_name' => $sUserName,
            'user_email' => $sUserEmail,
            'unit' => $sUnit,
            'action' => $sAction,
            'object_id' => $iObjectId
        ));
    }

    function _getJsCode()
    {
        ob_start();
        ?>
            var <?=$this->_sJsObject; ?> = new BxDolSubscription({
                sActionUrl: '<?=$this->_sActionUrl; ?>',
                sObjName: '<?=$this->_sJsObject; ?>',
                sVisitorPopup: '<?=$this->_sVisitorPopup; ?>'
            });
        <?php
        $sContent = ob_get_clean();

        return $GLOBALS['oSysTemplate']->_wrapInTagJsCode($sContent);
    }

    function _getUnsubscribeLink($mixedIds)
    {
        $aIds = array();
        if(is_int($mixedIds))
            $aIds = array($mixedIds);
        else if(is_string($mixedIds))
            $aIds = explode(",", $mixedIds);
        else if(is_array($mixedIds))
            $aIds = $mixedIds;

        return !empty($aIds) ? $this->_sActionUrl . '?sid=' . urlencode(base64_encode(implode(",", $aIds))) : '';
    }
}
