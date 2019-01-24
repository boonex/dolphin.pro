<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( './inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC     . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC     . 'db.inc.php' );
require_once( BX_DIRECTORY_PATH_INC     . 'match.inc.php' );
bx_import('BxDolProfileFields');
bx_import('BxDolProfilesController');
bx_import('BxTemplFormView');
bx_import('BxDolPageView');
bx_import('BxDolPrivacy');

class BxDolPEditProcessor extends BxDolPageView
{
    var $iProfileID; // id of profile which will be edited
    var $iArea = 0;  // 2=owner, 3=admin, 4=moderator
    var $bCouple = false; // if we edititng couple profile
    var $aCoupleMutualFields; // couple mutual fields

    var $oPC;        // object of profiles controller
    var $oPF;        // object of profile fields

    var $aBlocks;    // blocks of page (with items)
    var $aItems;     // all items within blocks

    var $aProfiles;  // array with profiles (couple) data
    var $aValues;    // values
    var $aOldValues; // values before save
    var $aErrors;    // generated errors

    var $bAjaxMode;  // if the script was called via ajax

    var $bForceAjaxSave = false;

    var $aFormPrivacy;
    var $aFormMembership;

    function __construct()
    {
        global $logged;

        $this -> aProfiles = array( 0 => array(), 1 => array() ); // double arrays (for couples)
        $this -> aValues   = array( 0 => array(), 1 => array() );
        $this -> aErrors   = array( 0 => array(), 1 => array() );

        $iId = bx_get('ID');
        $this -> iProfileID = (int)$iId;

        // basic checks
        if( $logged['member'] ) {
            $iMemberID = getLoggedId();
            if( !$this -> iProfileID ) {
                //if profile id is not set by request, edit own profile
                $this -> iProfileID = $iMemberID;
                $this -> iArea = 2;
            } else {
                // check if this member is owner
                if( $this -> iProfileID == $iMemberID )
                    $this -> iArea = 2;
            }
        } elseif( $logged['admin'] )
            $this -> iArea = 3;
        elseif( $logged['moderator'] )
            $this -> iArea = 4;

        $this -> bAjaxMode = ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' );
        $this -> bForceAjaxSave = bx_get('force_ajax_save');

        parent::__construct('pedit');
    }

    function getBlockCode_Info()
    {
        if( !$this -> iProfileID )
            return _t( '_Profile not specified' );

        if( !$this -> iArea )
            return _t( '_You cannot edit this profile' );

        /* @var $this->oPC BxDolProfilesController */
        $this -> oPC = new BxDolProfilesController();

        //get profile info array
        $this -> aProfiles[0] = $this -> oPC -> getProfileInfo( $this -> iProfileID );
        if( !$this -> aProfiles[0] )
            return _t( '_Profile not found' );

        if( $this -> aProfiles[0]['Couple'] ) { // load couple profile
            $this -> aProfiles[1] = $this -> oPC -> getProfileInfo( $this -> aProfiles[0]['Couple'] );

            if( !$this -> aProfiles[1] )
                return _t( '_Couple profile not found' );

            $this -> bCouple = true; //couple enabled
        }

        /* @var $this->oPF BxDolProfileFields */
        $this -> oPF = new BxDolProfileFields( $this -> iArea );
        if( !$this -> oPF -> aArea )
            return 'Profile Fields cache not loaded. Cannot continue.';

        $this -> aCoupleMutualFields = $this -> oPF -> getCoupleMutualFields();

        //collect blocks
        $this -> aBlocks = $this -> oPF -> aArea;

        //collect items
        $this -> aItems = array();
        foreach ($this -> aBlocks as $aBlock) {
            foreach( $aBlock['Items'] as $iItemID => $aItem )
                $this -> aItems[$iItemID] = $aItem;
        }

        $this -> aValues[0] = $this -> oPF -> getValuesFromProfile( $this -> aProfiles[0] ); // set default values
        if( $this -> bCouple )
            $this -> aValues[1] = $this -> oPF -> getValuesFromProfile( $this -> aProfiles[1] ); // set default values

        $this -> aOldValues = $this -> aValues;

        $sStatusText = '';
        if( isset($_POST['do_submit']) ) {
            $this -> oPF -> processPostValues( $this -> bCouple, $this -> aValues, $this -> aErrors, 0, $this -> iProfileID, (int)$_POST['pf_block'] );

            //--- move necessary mutual fields in second profile for correct work of some features like browsing, etc.
            $aMutualFieldsCopy = $this->oPF->getCoupleMutualFieldsCopy();
            if(!empty($aMutualFieldsCopy) && is_array($aMutualFieldsCopy)) {
                $aMutualFieldsCopy = array_intersect_key($this -> aValues[0], array_flip($aMutualFieldsCopy));
                        
                $this -> aValues[1] = array_merge($this -> aValues[1], $aMutualFieldsCopy);
            }

            if( empty( $this -> aErrors[0] ) and empty( $this -> aErrors[1] ) ) { // do not save in ajax mode
                if (!$this -> bAjaxMode or $this->bForceAjaxSave) {
                    $this -> saveProfile();
                    $sStatusText = '_Save profile successful';
                }
            }
        }

        if($this -> bAjaxMode) {
            $this -> showErrorsJson();
            exit;
        } else
            return array($this -> showEditForm($sStatusText), array(), array(), false);
    }
    function getBlockCode_Privacy()
    {
        $oPrivacy = new BxDolPrivacy('sys_page_compose_privacy', 'id', 'user_id');

        $this->aFormPrivacy = array(
            'form_attrs' => array(
                'id' => 'profile_edit_privacy',
                'name' => 'profile_edit_privacy',
                'action' => BX_DOL_URL_ROOT . 'pedit.php?ID=' . $this->iProfileID,
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'params' => array (
                'db' => array(
                    'table' => '',
                    'key' => '',
                    'uri' => '',
                    'uri_title' => '',
                    'submit_name' => 'save_privacy'
                ),
            ),
            'inputs' => array (
                'profile_id' => array(
                    'type' => 'hidden',
                    'name' => 'profile_id',
                    'value' => $this->iProfileID,
                ),
                'allow_view_to' => $oPrivacy->getGroupChooser(getLoggedId(), 'profile', 'view'),
                'save_privacy' => array(
                    'type' => 'submit',
                    'name' => 'save_privacy',
                    'value' => _t('_edit_profile_privacy_save'),
                ),
            )
        );
        $aProfileInfo = getProfileInfo($this->iProfileID);
        $this->aFormPrivacy['inputs']['allow_view_to']['value'] = $aProfileInfo['allow_view_to'];

        $oForm = new BxTemplFormView($this->aFormPrivacy);
        $oForm->initChecker();

        if($oForm->isSubmittedAndValid()) {
            $iProfileId = (int)$_POST['profile_id'];
            $iAllowViewTo = (int)$_POST['allow_view_to'];
            $aProfileInfo = getProfileInfo($iProfileId);

            if ((int)db_res("UPDATE `Profiles` SET `allow_view_to`='" . $iAllowViewTo . "' WHERE `ID`='" . $iProfileId . "' LIMIT 1") > 0) {
                $sStatusText = '_Save profile successful';
                createUserDataFile($iProfileId);

                if (BxDolModule::getInstance('BxWmapModule'))
                    BxDolService::call('wmap', 'response_entry_change', array('profiles', $iProfileId));

                // create system event
                bx_import('BxDolAlerts');
                $oZ = new BxDolAlerts('profile', 'edit', $iProfileId, 0, array('OldProfileInfo' => $aProfileInfo, 'privacy' => $iAllowViewTo));
                $oZ->alert();
            }
        }

        if($sStatusText)
            $sStatusText = MsgBox(_t($sStatusText), 3);

        return array($sStatusText . $oForm->getCode(), array(), array(), false);
    }
    function getBlockCode_Membership()
    {
        if(!isAdmin())
            return;

        $sUnlimited = process_line_output(_t('_pfm_unlimited'));

        $this->aFormMembership = array(
            'form_attrs' => array(
                'id' => 'profile_edit_membership',
                'name' => 'profile_edit_membership',
                'action' => BX_DOL_URL_ROOT . 'pedit.php?ID=' . $this->iProfileID,
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'params' => array (
                'db' => array(
                    'table' => '',
                    'key' => '',
                    'uri' => '',
                    'uri_title' => '',
                    'submit_name' => 'save_membership'
                ),
            ),
            'inputs' => array(
                'doSetMembership' => array(
                    'type' => 'hidden',
                    'name' => 'doSetMembership',
                    'value' => 'yes',
                ),
                'MembershipInfo' => array(
                    'type' => 'custom',
                    'caption' => _t('_Membership_current'),
                    'content' => ''
                ),
                'MembershipID' => array(
                    'type' => 'select',
                    'name' => 'MembershipID',
                    'caption' => _t('_Membership_name'),
                    'value' => '',
                    'values' => array(),
                    'required' => 0,
                    'attrs' => array(
                        'onchange' => 'checkStandard()'
                    ),
                    'checker' => array (
                        'func' => 'avail',
                        'params' => array(),
                        'error' => _t('_Membership_name_err_empty'),
                    ),
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                'MembershipDays' => array(
                    'type' => 'text',
                    'name' => 'MembershipDays',
                    'caption' => _t('_Membership_days'),
                    'value' => $sUnlimited,
                    'required' => 0,
                    'attrs' => array(
                        'onfocus' => "if(MembershipDays.value == '" . $sUnlimited . "') MembershipDays.value = ''",
                        'onblur' => "if(MembershipDays.value == '') MembershipDays.value = '" . $sUnlimited . "'"
                    ),
                    'info' => _t('_Membership_days_info'),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'MembershipImmediately' => array(
                    'type' => 'checkbox',
                    'name' => 'MembershipImmediately',
                    'caption' => _t('_Membership_starts'),
                    'value' => 'on',
                    'required' => 0,
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'save_membership' => array(
                    'type' => 'submit',
                    'name' => 'save_membership',
                    'value' => _t('_Membership_save'),
                ),
            )
        );

        $aMemberships = getMemberships();
        foreach($aMemberships as $iId => $sName)
            if($iId != MEMBERSHIP_ID_NON_MEMBER)
                $this->aFormMembership['inputs']['MembershipID']['values'][] = array('key' => $iId, 'value' => $sName);

        $oForm = new BxTemplFormView($this->aFormMembership);
        $oForm->initChecker();

        $sContent = "";
        if($oForm->isSubmittedAndValid()) {
            $iMshipID   =  (int)$oForm->getCleanValue('MembershipID');
            $iMshipDays = (int)$oForm->getCleanValue('MembershipDays'); //0 = unlim
            $bStartsNow = $oForm->getCleanValue('MembershipImmediately') == 'on';

            $bSave = setMembership( $this -> iProfileID, $iMshipID, $iMshipDays, $bStartsNow );
            $sContent .= MsgBox(_t($bSave ? '_Membership_save_msg_saved' : '_Membership_save_err_saved'), 3);
        }

        /**
         * Retrieve current membership info.
         */
        $aMembershipCurrent = getMemberMembershipInfo($this->iProfileID);
        $sMembershipCurrent = $aMembershipCurrent['Name'];
        if($aMembershipCurrent['ID'] != MEMBERSHIP_ID_STANDARD)
            $sMembershipCurrent .= ', ' . (!isset($aMembershipCurrent['DateExpires']) ? _t('_MEMBERSHIP_EXPIRES_NEVER') : _t('_MEMBERSHIP_EXPIRES', defineTimeInterval($aMembershipCurrent['DateExpires'])));

        $oForm->aInputs['MembershipInfo']['content'] = $sMembershipCurrent;

        ob_start();
?>
        <script type="text/javascript">
        <!--
        function checkStandard()
        {
            var iId = parseInt($("[name='MembershipID']").val());
            if(iId == <?=MEMBERSHIP_ID_STANDARD; ?>) {
                $("[name='MembershipDays']").attr('disabled', 'disabled');
                $("[name='MembershipImmediately']").attr('disabled', 'disabled');
            } else {
                $("[name='MembershipDays']").removeAttr('disabled');
                $("[name='MembershipImmediately']").removeAttr('disabled');
            }
        }
        $(document).ready(function() {
            checkStandard();
        });
        -->
        </script>
<?php
        $sContent .= ob_get_clean();
        return array($sContent . $oForm->getCode(), array(), array(), false);
    }

    function showErrorsJson()
    {
        header('Content-Type:text/javascript; charset=utf-8');
        echo $this -> oPF -> genJsonErrors( $this -> aErrors, $this -> bCouple );
    }

    function showEditForm( $sStatusText )
    {
        $aEditFormParams = array(
            'couple_enabled' => $this->bCouple,
            'couple'         => $this->bCouple,
            'page'           => $this->iPage,
            'hiddens'        => array('ID' => $this -> iProfileID, 'do_submit' => '1'), //$this->genHiddenFieldsArray(),
            'errors'         => $this->aErrors,
            'values'         => $this->aValues,
            'profile_id'     => $this->iProfileID,
        );

        if($sStatusText)
            $sStatusText = MsgBox(_t($sStatusText), 3);

        return $sStatusText . $this->oPF->getFormCode($aEditFormParams);
    }

    function saveProfile()
    {
        $aProfileInfo = db_arr("SELECT * FROM `Profiles` WHERE `ID` = {$this -> iProfileID}");
        $aDiff = $this -> getDiffValues(0);
        $aUpd = $this -> oPF -> getProfileFromValues( $aDiff );

        $aUpd['DateLastEdit'] = date( 'Y-m-d H:i:s' );
        if( !getParam('autoApproval_ifProfile') && $this -> iArea == 2 )
            $aUpd['Status'] = 'Approval';

        if( ( $this -> iArea == 3 or $this -> iArea == 4 ) and isset( $_POST['doSetMembership'] ) and $_POST['doSetMembership'] == 'yes' )
            $this -> setMembership();

		$bResult = $this -> oPC -> updateProfile( $this -> iProfileID, $aUpd );
        if($bResult && $this -> iProfileID == getLoggedId() && isset($aUpd['Password']))
        	bx_login($this -> iProfileID, false, false);

        if (BxDolModule::getInstance('BxWmapModule'))
            BxDolService::call('wmap', 'response_entry_change', array('profiles', $this->iProfileID));

        // create system event
        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts('profile', 'edit',  $this -> iProfileID, 0, array('OldProfileInfo' => $aProfileInfo) );
        $oZ->alert();

        if( $this -> bCouple ) {
            $aDiff = $this -> getDiffValues(1);
            $aUpd = $this -> oPF -> getProfileFromValues( $aDiff );

            $aUpd['DateLastEdit'] = date( 'Y-m-d H:i:s' );
            if( !getParam('autoApproval_ifProfile') && $this -> iArea == 2 )
                $aUpd['Status'] = 'Approval';

            $this -> oPC -> updateProfile( $this -> aProfiles[0]['Couple'], $aUpd );
        }
    }

    function getDiffValues($iInd)
    {
        $aOld = $this -> aOldValues[$iInd];
        $aNew = $this -> aValues[$iInd];

        $aDiff = array();
        foreach( $aNew as $sName => $mNew ){
            $mOld = $aOld[$sName];

            if( is_array($mNew) ) {
                if( count($mNew) == count($mOld) ) {
                    //compare each value
                    $mOldS = $mOld;
                    $mNewS = $mNew;
                    sort( $mOldS ); //sort them for correct comparison
                    sort( $mNewS );

                    foreach( $mNewS as $iKey => $sVal )
                        if( $mNewS[$iKey] != $mOld[$iKey] ) {
                            $aDiff[$sName] = $mNew; //found difference
                            break;
                        }
                } else
                    $aDiff[$sName] = $mNew;
            } else {
                if( $mNew != $mOld )
                    $aDiff[$sName] = $mNew;
            }
        }

        return $aDiff;
    }
}

$_page['name_index'] = 25;
$_page['css_name']   = 'pedit.css';
$_page['extra_js']  .= '<script type="text/javascript" language="JavaScript" src="' . $site['plugins'] . 'jquery/jquery.form.min.js"></script>';
$_page['extra_js']  .= '<script type="text/javascript" language="JavaScript" src="inc/js/pedit.js"></script>';

check_logged();

if (!(isAdmin() || isModerator() || (isLogged() && getLoggedId() == bx_get('ID')))) {
    $GLOBALS['oSysTemplate']->displayAccessDenied ();
    exit;
}

$_page['header']      = _t( '_Edit Profile' );
$_page['header_text'] = _t( '_Edit Profile' );
$_ni = $_page['name_index'];

$oEditProc = new BxDolPEditProcessor();
$_page_cont[$_ni]['page_main_code'] = $oEditProc->getCode();
PageCode();
