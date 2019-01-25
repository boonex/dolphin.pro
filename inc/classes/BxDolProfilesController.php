<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolProfileFields.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolEmailTemplates.php' );

class BxDolProfilesController
{
    var $oPF;
    var $aItems;
    var $oEmailTemplate;

    function __construct()
    {
        $this -> oEmailTemplate = new BxDolEmailTemplates();
    }

    function createProfile( $aData, $bSendMails = true, $iMainMemberID = 0 )
    {
        if( !$aData or !is_array($aData) or empty($aData) )
            return false;

        unset( $aData['Couple'] );
        unset( $aData['Captcha'] );
        unset( $aData['TermsOfUse'] );
        unset( $aData['ProfilePhoto'] );

        /* @var $this->oPF BxDolProfileFields */
        $this -> oPF = new BxDolProfileFields(100);

        if( !$this -> oPF -> aArea ) {
            echo 'Profile Fields cache not loaded. Cannot continue.';
            return false;
        }

        $this -> aItems = $this -> oPF -> aArea[0]['Items'];

        if( $iMainMemberID )
            $aMainMember = $this -> getProfileInfo( $iMainMemberID );
        else
            $aMainMember = false;

        // begin profile info collecting
        $aNewProfile = array();

        foreach( $this -> aItems as $aItem ) {
            $sItemName = $aItem['Name'];

            if( array_key_exists( $sItemName, $aData ) ) {
                $aNewProfile[$sItemName] = $aData[$sItemName];
            } elseif( $aMainMember and array_key_exists( $sItemName, $aMainMember ) and $aItem['Type'] != 'system' ) {
                if( $aItem['Unique'] )
                    $aNewProfile[$sItemName] = $this -> genUniqueValue($sItemName, $aMainMember[$sItemName]);
                else
                    $aNewProfile[$sItemName] = $aMainMember[$sItemName];
            } else {
                switch( $aItem['Type'] ) {
                    case 'pass':
                        $aNewProfile[$sItemName] = $this -> genRandomPassword();
                    break;

                    case 'num':
                        $aNewProfile[$sItemName] = (int)$aItem['Default'];
                    break;

                    case 'bool':
                        $aNewProfile[$sItemName] = (bool)$aItem['Default'];
                    break;

                    case 'system':
                        switch( $sItemName ) {
                            case 'ID': //set automatically
                            case 'Captcha': //not been inserted
                            case 'Location': //not been inserted
                            case 'Keyword': //not been inserted
                            case 'TermsOfUse': //not been inserted
                                //pass
                            break;

                            case 'DateReg':
                                $aNewProfile[$sItemName] = date( 'Y-m-d H:i:s' ); // set current date
                            break;

                            case 'DateLastEdit':
                            case 'DateLastLogin':
                                $aNewProfile[$sItemName] = '0000-00-00';
                            break;

                            case 'Couple':
                                $aNewProfile[$sItemName] = $aMainMember ? $iMainMemberID : 0; //if main member exists, set him as a couple link
                            break;

                            case 'Featured':
                                $aNewProfile[$sItemName] = false;
                            break;

                            case 'Status':
                                if (getParam('autoApproval_ifNoConfEmail') == 'on') {
                                    if (getParam('autoApproval_ifJoin') == 'on' && !(getParam('sys_dnsbl_enable') && 'approval' == getParam('sys_dnsbl_behaviour') && bx_is_ip_dns_blacklisted('', 'join')))
                                        $aNewProfile[$sItemName] = 'Active';
                                    else
                                        $aNewProfile[$sItemName] = 'Approval';
                                } else
                                    $aNewProfile[$sItemName] = 'Unconfirmed';
                            break;
                        }
                    break;

                    default:
                        $aNewProfile[$sItemName] = $aItem['Default'];
                }
            }
        } //we completed collecting

        // set default language
        $aNewProfile['LangID'] = getLangIdByName(getCurrentLangName());

        // set default privacy
        bx_import('BxDolPrivacyQuery');
        $oPrivacy = new BxDolPrivacyQuery();
        $aNewProfile['allow_view_to'] = $oPrivacy->getDefaultValueModule('profile', 'view_block');

        $sSet = $this -> collectSetString( $aNewProfile );
        $sQuery = "INSERT INTO `Profiles` SET \n$sSet";

        $rRes = db_res( $sQuery );

        if( $rRes ) {
            $iNewID = db_last_id();

            $this -> createProfileCache( $iNewID );

            if( $aMainMember )
                $this -> updateProfile( $iMainMemberID, array('Couple' => $iNewID ) ); //set main member's couple. they will be linked each other

            //collect status text
            if( $bSendMails and !$aMainMember ) { //send mail only to main member, not to couple
                $sStatusText = $aNewProfile['Status'];
                if (getParam('autoApproval_ifNoConfEmail') == 'on') {
                    if ('Active' ==  $sStatusText) 
                        $this -> sendActivationMail( $iNewID );
                    else
                        $this -> sendApprovalMail( $iNewID );
                } else {
                    if (!$this -> sendConfMail($iNewID))
                        $sStatusText = 'NotSent';
                }
            } else
                $sStatusText = 'OK';

            //set crypted password
            $sSalt = genRndSalt();
            $this -> updateProfile($iNewID, array(
                'Password' => encryptUserPwd($aNewProfile['Password'], $sSalt),
                'Salt' => $sSalt
            ));

            bx_member_ip_store($iNewID);

            return array( $iNewID, $sStatusText );
        } else
            return array( false, 'Failed' );
    }

    function createProfileCache( $iMemID )
    {
        createUserDataFile( $iMemID );
    }

    function sendConfMail( $iMemID )
    {
        global $site;

        $iMemID = (int)$iMemID;
        $aMember = $this -> getProfileInfo( $iMemID );
        if( !$aMember )
            return false;

        $sEmail    = $aMember['Email'];

        $sConfCode = base64_encode( base64_encode( crypt( $sEmail, CRYPT_EXT_DES ? 'secret_ph' : 'se' ) ) );
        $sConfLink = "{$site['url']}profile_activate.php?ConfID={$iMemID}&ConfCode=" . urlencode( $sConfCode );

        $aPlus = array( 'ConfCode' => $sConfCode, 'ConfirmationLink' => $sConfLink );

        $aTemplate = $this -> oEmailTemplate -> getTemplate( 't_Confirmation', $iMemID ) ;
        return sendMail( $sEmail, $aTemplate['Subject'], $aTemplate['Body'], $iMemID, $aPlus, 'html', false, true );
    }

    // sent when user status changed to active
    function sendActivationMail( $iMemID )
    {
        $iMemID = (int)$iMemID;
        $aMember  = $this -> getProfileInfo( $iMemID );
        if( !$aMember )
            return false;

        $sEmail    = $aMember['Email'];
        $aTemplate = $this -> oEmailTemplate -> getTemplate( 't_Activation', $iMemID ) ;

        return sendMail( $sEmail, $aTemplate['Subject'], $aTemplate['Body'], $iMemID, array(), 'html', false, true);
    }

    //sent if member in approval status
    function sendApprovalMail( $iMemId )
    {
    }

    // sent to admin
    function sendNewUserNotify( $iMemID )
    {
        $iMemID = (int)$iMemID;
        $aMember = $this -> getProfileInfo( $iMemID );
        if( !$aMember )
            return false;

        $oEmailTemplates = new BxDolEmailTemplates();
        $aTemplate = $oEmailTemplates->getTemplate('t_UserJoined');

        return sendMail($GLOBALS['site']['email'], $aTemplate['Subject'], $aTemplate['Body'], $iMemID);
    }

    function sendUnregisterUserNotify( $aMember )
    {
        if(empty($aMember) || !is_array($aMember))
			return false;

        $oEmailTemplates = new BxDolEmailTemplates();
        $aTemplate = $oEmailTemplates->parseTemplate('t_UserUnregistered', array(
			'NickName'	=> $aMember['NickName'],
        	'Email'	=> $aMember['Email'],
        ));

        return sendMail($GLOBALS['site']['email'], $aTemplate['subject'], $aTemplate['body']);
    }

    function updateProfile( $iMemberID, $aData )
    {
        if( !$aData or !is_array($aData) or empty($aData) )
            return false;

        $this -> _checkUpdateMatchFields($aData);

        $sSet = $this -> collectSetString( $aData );
        $sQuery = "UPDATE `Profiles` SET {$sSet} WHERE `ID` = " . (int)$iMemberID;
        //echo $sQuery ;
        $res = db_res($sQuery);
        $this -> createProfileCache( $iMemberID );
        return (bool)db_affected_rows($res);
    }

    /**
    * Check if we need to update profile matching
    */
    function _checkUpdateMatchFields(&$aData)
    {
        // list of all matchable fields
        $aAllMatchFields = array();

        // temporary flag of member
        $aData['UpdateMatch'] = false;

        // get array of matching fields
        $oMatchFields = new BxDolProfileFields(101);
        $aMatchFields = $oMatchFields -> aArea[0]['Items'];

        // get array of all fields
        $oAllFields = new BxDolProfileFields(100);
        $aAllFields = $oAllFields -> aArea[0]['Items'];

        // find all matchable fields
        foreach ($aMatchFields as $iFieldID => $aField) {
            // put it to the list
            $aAllMatchFields[$iFieldID] = $aField['Name'];

            // get matched field too
            $iNewFieldID = $aField['MatchField'];
            $aNewField   = $aAllFields[$iNewFieldID];

            // and put it to the list too
            $aAllMatchFields[$iNewFieldID] = $aNewField['Name'];
        }

        // also need to re-match if Status is changed
        $aAllMatchFields[7] = 'Status';

        //echoDbg($aAllMatchFields);

        // check if one of updated fields is matchable
        foreach ($aData as $sName => $sValue) {
            //echo $sName . "\n";
            if (in_array($sName, $aAllMatchFields)) {
                $aData['UpdateMatch'] = true;
                break; // if at least one of the fields is matchable then true
            }
        }

        //echoDbg($aData);
    }

    function collectSetString( $aData )
    {
        $sRequestSet = '';

        foreach( $aData as $sField => $mValue ) {
            if( is_string($mValue) )
                $sValue = "{$GLOBALS['MySQL']->escape($mValue)}";
            elseif( is_bool($mValue) )
                $sValue = (int)$mValue;
            elseif( is_array($mValue) ) {
                $sValue = '';
                foreach( $mValue as $sStr )
                    $sValue .= $GLOBALS['MySQL']->escape(str_replace( ',', '', $sStr ), false) . ',';

                $sValue = "'" . substr($sValue,0,-1) . "'";
            } elseif( is_int($mValue) ) {
                $sValue = $mValue;
            } else
                continue;

            $sRequestSet .= "`$sField` = $sValue,\n";
        }

        $sRequestSet = substr( $sRequestSet,0, -2 );// remove ,\n

        return $sRequestSet;
    }

    function deleteProfile( $iMemberID )
    {
    }

    function genRandomPassword()
    {
        return 'aaaaaa';
    }

    function getProfileInfo( $iMemberID )
    {
        return db_assoc_arr( "SELECT * FROM `Profiles` WHERE `ID` = " . (int)$iMemberID );
    }

    function genUniqueValue( $sFieldName, $sValue, $mixedRandMore = false )
    {
        if( $mixedRandMore === true )
            $sRand = '(' . rand(1000, 9999) . ')';
        else if(is_string($mixedRandMore) && !empty($mixedRandMore))
            $sRand = $mixedRandMore;
        else
            $sRand = '(2)';

        $sNewValue = $sValue . $sRand;

        $iCount = (int)db_value( "SELECT COUNT(*) FROM `Profiles` WHERE `$sFieldName` = {$GLOBALS['MySQL']->escape($sNewValue)}" );
        if( $iCount )
            return genUniqueValue( $sFieldName, $sValue, true );
        else
            return $sNewValue;
    }
}
