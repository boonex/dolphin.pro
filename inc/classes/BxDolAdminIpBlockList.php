<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplFormView');

class BxDolAdminIpBlockList
{
    var $_oDb;
    var $_sActionUrl;

    /**
     * constructor
     */
    function __construct($sActionUrl = '')
    {
        $this->_oDb = $GLOBALS['MySQL'];
        $this->_sActionUrl = !empty($sActionUrl) ? $sActionUrl : bx_html_attribute($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    }

    function GenStoredMemIPs()
    {
        $sCntSQL = "SELECT COUNT(*) FROM `sys_ip_members_visits`";
        ////////////////////////////
        $iTotalNum = db_value( $sCntSQL );
        if(!$iTotalNum)
			return MsgBox(_t('_Empty'));

        $iPerPage = (int)$_GET['per_page'];
        if( !$iPerPage )
            $iPerPage = 10;
        $iCurPage = (int)$_GET['page'];
        if( $iCurPage < 1 )
            $iCurPage = 1;
        $sLimitFrom = ( $iCurPage - 1 ) * $iPerPage;
        $sqlLimit = "LIMIT {$sLimitFrom}, {$iPerPage}";
        ////////////////////////////

        $sSQL = "SELECT *, UNIX_TIMESTAMP(`DateTime`) AS `DateTimeTS` FROM `sys_ip_members_visits` ORDER BY `DateTime` DESC {$sqlLimit}";
        $rIPList = db_res( $sSQL );

        $aTmplVarsItems = array();
        while( $aIPList =  $rIPList ->fetch() ) {
            $iID = (int)$aIPList['ID'];
            $sFrom = long2ip($aIPList['From']);
            $sLastDT = getLocaleDate($aIPList['DateTimeTS'], BX_DOL_LOCALE_DATE);
            $sMember = $aIPList['MemberID'] ? '<a href="' . getProfileLink($aIPList['MemberID']) . '">' . getNickname($aIPList['MemberID']) . '</a>' : '';

            $aTmplVarsItems[] = array(
            	'from' => $sFrom,
            	'bx_if:show_profile_link' => array(
            		'condition' => !empty($aIPList['MemberID']),
            		'content' => array(
            			'href' => getProfileLink($aIPList['MemberID']),
            			'caption' => getNickname($aIPList['MemberID'])
            		)
            	),
            	'date' => $sLastDT
            );
        }

        $oPaginate = new BxDolPaginate(array(
			'page_url' => $GLOBALS['site']['url_admin'] . 'ip_blacklist.php?mode=list&page={page}&per_page={per_page}',
			'count' => $iTotalNum,
			'per_page' => $iPerPage,
			'page' => $iCurPage,
		));

        return $GLOBALS['oAdmTemplate']-> parseHtmlByName('ip_blacklist_list_ips.html', array(
        	'bx_repeat:items' => $aTmplVarsItems,
        	'paginate' => $oPaginate -> getPaginate()
        ));
    }

    function GenIPBlackListTable()
    {
        $sSQL = "SELECT *, FROM_UNIXTIME(`LastDT`) AS `LastDT_U` FROM `sys_ip_list` ORDER BY `From` ASC";
        $rIPList = db_res( $sSQL );

        $aTmplVarsItems = array();
        while( $aIPList =  $rIPList ->fetch() ) {
            $iID = (int)$aIPList['ID'];
            $sFrom = long2ip($aIPList['From']);
            $sTo = ($aIPList['To'] == 0) ? '' : long2ip($aIPList['To']);
            $sType = process_html_output($aIPList['Type']);
            $sLastDT_Formatted = getLocaleDate($aIPList['LastDT'], BX_DOL_LOCALE_DATE);
            $sLastDT = preg_replace('/([\d]{2}):([\d]{2}):([\d]{2})/', '$1:$2', $aIPList['LastDT_U']);
            $sDesc = process_html_output($aIPList['Desc']);
            $sDescAttr = bx_html_attribute(bx_js_string($aIPList['Desc'],  BX_ESCAPE_STR_APOS));

            $aTmplVarsItems[] = array(
            	'id' => $iID,
            	'from' => $sFrom,
            	'to' => $sTo,
            	'type' => $sType,
            	'date' => $sLastDT,
            	'date_uf' => $sLastDT_Formatted,
            	'description' => $sDesc,
            	'description_attr' => $sDescAttr,
                'delete_action_url' => bx_append_url_params($this->_sActionUrl, array('action' => 'apply_delete', 'id' => $iID)),
            );
        }

        if(empty($aTmplVarsItems))
			return MsgBox(_t('_Empty'));

        return $GLOBALS['oAdmTemplate']-> parseHtmlByName('ip_blacklist_list_filters.html', array(
        	'bx_repeat:items' => $aTmplVarsItems
        ));
    }

    function getManagingForm()
    {
        $sApplyChangesC = _t('_sys_admin_apply');
        $sFromC = _t('_From');
        $sToC = _t('_To');
        $sSampleC = _t('_adm_ipbl_sample');
        $sTypeC = _t('_adm_ipbl_IP_Role');
        $sDescriptionC = _t('_Description');
        $sDatatimeC = _t('_adm_ipbl_Date_of_finish');
        $sErrorC = _t('_Error Occured');

        $aForm = array(
            'form_attrs' => array(
                'name' => 'apply_ip_list_form',
                'action' => $this->_sActionUrl,
                'method' => 'post',
            ),
            'params' => array (
                'db' => array(
                    'table' => 'sys_ip_list',
                    'key' => 'ID',
                    'submit_name' => 'add_button',
                ),
            ),
            'inputs' => array(
                'FromIP' => array(
                    'type' => 'text',
                    'name' => 'from',
                    'caption' => $sFromC,
                    'info' => $sSampleC . ': 10.0.0.0',
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(7,15),
                        'error' => $sErrorC,
                    ),
                ),
                'ToIP' => array(
                    'type' => 'text',
                    'name' => 'to',
                    'caption' => $sToC,
                    'info' => $sSampleC . ': 10.0.0.100',
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(7,15),
                        'error' => $sErrorC,
                    ),
                ),
                'IPRole' => array(
                    'type' => 'select',
                    'name' => 'type',
                    'caption' => $sTypeC,
                    'values' => array('allow', 'deny'),
                    'required' => true,
                ),
                'DateTime' => array(
                    'type' => 'datetime',
                    'name' => 'LastDT',
                    'caption' => $sDatatimeC,
                    'required' => true,
                    'checker' => array (
                        'func' => 'DateTime',
                        'error' => $sErrorC,
                    ),
                    'db' => array (
                        'pass' => 'DateTime',
                    ),
                ),
                'Desc' => array(
                    'type' => 'text',
                    'name' => 'desc',
                    'caption' => $sDescriptionC,
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(2,128),
                        'error' => $sErrorC,
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'ID' => array(
                    'type' => 'hidden',
                    'value' => '0',
                    'name' => 'id',
                ),
                'add_button' => array(
                    'type' => 'submit',
                    'name' => 'add_button',
                    'value' => $sApplyChangesC,
                ),
            ),
        );

        $sResult = '';
        $oForm = new BxTemplFormView($aForm);
        $oForm->initChecker();
        if ($oForm->isSubmittedAndValid()) {
            /*list($iDay, $iMonth, $iYear) = explode( '/', $_REQUEST['datatime']);
            $iDay = (int)$iDay;
            $iMonth = (int)$iMonth;
            $iYear = (int)$iYear;
            //$sCurTime = date("Y:m:d H:i:s");// 2012-06-20 15:46:21
            $sCurTime = "{$iYear}:{$iMonth}:{$iDay} 12:00:00";*/

            $sFrom = sprintf("%u", ip2long($_REQUEST['from']));
            $sTo = sprintf("%u", ip2long($_REQUEST['to']));

            $sType = ((int)$_REQUEST['type']==1) ? 'deny' : 'allow';

            $aValsAdd = array (
                'From' => $sFrom,
                'To' => $sTo,
                /*'LastDT' => $sCurTime,*/
                'Type' => $sType
            );

            $iLastId = ((int)$_REQUEST['id']>0) ? (int)$_REQUEST['id'] : -1;

            if ($iLastId>0) {
                $oForm->update($iLastId, $aValsAdd);
            } else {
                $iLastId = $oForm->insert($aValsAdd);
            }

            $sResult = ($iLastId > 0) ? MsgBox(_t('_Success'), 3) : MsgBox($sErrorC);
        }
        return $sResult . $oForm->getCode();
    }

    function ActionApplyDelete()
    {
        $iID = (int)$_REQUEST['id'];

        if ($iID>0) {
            $sDeleteSQL = "DELETE FROM `sys_ip_list` WHERE `ID`='{$iID}' LIMIT 1";
            db_res($sDeleteSQL);
        }
    }

    function deleteExpired ()
    {
        $iTime = time();
        $r = db_res("DELETE FROM `sys_ip_list` WHERE `LastDT` <= $iTime");
        if ($r && ($iAffectedRows = db_affected_rows($r))) {
            db_res("OPTIMIZE TABLE `sys_ip_list`");
            return $iAffectedRows;
        } else {
            return 0;
        }
    }
}
