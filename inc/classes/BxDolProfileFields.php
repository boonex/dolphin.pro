<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'Thing.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPFM.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolPrivacy.php' );

class BxDolProfileFields extends Thing
{
    var $iAreaID;
    var $aArea; // just a cache array
    var $aBlocks; // array of current blocks
    var $aCache; // full cache of profile fields
    var $aCoupleMutual; //couple mutual fields
    var $aCoupleMutualCopy; //couple mutual fields, which values should be coppied in second profile.

    var $sLinkPref = '#!'; //prefix for values links

    var $aAddPleaseSelect = array(
    	'join' => array('Country', 'Sex'),
    );

    function __construct( $iAreaID )
    {
        $this -> iAreaID = $iAreaID;

        if( !$this -> loadCache() )
            return false;

    }

    function loadCache( $bCycle = true )
    {
        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        $this -> aCache = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_profile_fields'));
        if (null === $this -> aCache || !is_array($this->aCache)) {

            $oPFM = new BxDolPFMCacher();

            if (!$oPFM -> createCache())
                return false;

            if ($bCycle) //to prevent cycling
                return $this -> loadCache (false); // try againg
            else
                return false;
        }

        $this -> aArea = $this -> aCache[ $this -> iAreaID ];

        //load blocks
        $this -> aBlocks = $this -> aArea;

        //get mutual fields
        $this -> _getCoupleMutualFields();

        return true;
    }

    function genJsonErrors( $aErrors, $bCouple )
    {
        $aJsonErrors = array();

        $aJsonErrors[0] = $aErrors[0];
        if( $bCouple )
            $aJsonErrors[1] = $aErrors[1];

        return json_encode( $aJsonErrors );
    }

    //sets to $Errors intuitive array
    function processPostValues( $bCouple, &$aValues, &$aErrors, $iPage = 0, $iProfileID = 0, $iBlockOnly = 0 )
    {
        $iHumans = $bCouple ? 2 : 1; // number of members in profile (single/couple), made for double arrays

        if( $this -> iAreaID == 1 ) // join
            $this -> aBlocks = $this -> aArea[$iPage];

        foreach( $this -> aBlocks as $iBlockID => $aBlock ) {
            if ($iBlockOnly > 0 and $iBlockOnly != $iBlockID)
                continue;

            $aItems = $aBlock['Items'];
            foreach ($aItems as $iItemID => $aItem) {
                $sItemName = $aItem['Name'];

                for( $iHuman = 0; $iHuman < $iHumans; $iHuman ++ ) {
                    if( $iHuman == 1 and in_array( $sItemName, $this -> aCoupleMutual ) )
                        continue;

                    $mValue = null;
                    switch( $aItem['Type'] ) {
                        case 'text':
                        case 'area':
                        case 'pass':
                        case 'select_one':
                            if( isset( $_POST[$sItemName] ) and isset( $_POST[$sItemName][$iHuman] ) )
                                $mValue = process_pass_data( $_POST[$sItemName][$iHuman] );
                        break;

                        case 'html_area':
                            if( isset( $_POST[$sItemName] ) and isset( $_POST[$sItemName][$iHuman] ) )
                                $mValue = clear_xss( process_pass_data($_POST[$sItemName][$iHuman]) );
                        break;

                        case 'bool':
                            if( isset( $_POST[$sItemName] ) and isset( $_POST[$sItemName][$iHuman] ) and $_POST[$sItemName][$iHuman] == 'yes' )
                                $mValue = true;
                            else
                                $mValue = false;
                        break;

                        case 'num':
                            if( isset( $_POST[$sItemName] ) and isset( $_POST[$sItemName][$iHuman] ) and trim( $_POST[$sItemName][$iHuman] ) !== '' )
                                $mValue = (int)trim( $_POST[$sItemName][$iHuman] );
                        break;

                        case 'date':
                            if( isset( $_POST[$sItemName] ) and isset( $_POST[$sItemName][$iHuman] ) and trim( $_POST[$sItemName][$iHuman] ) !== '' ) {
                                list( $iYear, $iMonth, $iDay ) = explode( '-', $_POST[$sItemName][$iHuman] ); // 1985-10-28

                                $iDay   = intval($iDay);
                                $iMonth = intval($iMonth);
                                $iYear  = intval($iYear);

                                $mValue = sprintf("%04d-%02d-%02d", $iYear, $iMonth, $iDay);
                            }
                        break;

                        case 'select_set':
                            $mValue = array();
                            if( isset( $_POST[$sItemName] ) and isset( $_POST[$sItemName][$iHuman] ) and is_array( $_POST[$sItemName][$iHuman] ) ) {
                                foreach ($_POST[$sItemName][$iHuman] as $sValue ) {
                                    $mValue[] = process_pass_data( $sValue );
                                }
                            }
                        break;

                        case 'range':
                            if( isset( $_POST[$sItemName] ) and isset( $_POST[$sItemName][$iHuman] ) ) {
                                if (is_array($_POST[$sItemName][$iHuman]))
                                    $aRange = $_POST[$sItemName][$iHuman];
                                else
                                    $aRange = explode('-', $_POST[$sItemName][$iHuman], 2);

                                $mValue = array( null, null );

                                $aRange[0] = isset( $aRange[0] ) ? trim( $aRange[0] ) : '';
                                $aRange[1] = isset( $aRange[1] ) ? trim( $aRange[1] ) : '';

                                if( $aRange[0] !== '' )
                                    $mValue[0] = (int)$aRange[0];

                                if( $aRange[1] !== '' )
                                    $mValue[1] = (int)$aRange[1];
                            }
                        break;

                        case 'system':
                            switch( $aItem['Name'] ) {
                                case 'Couple':
                                case 'TermsOfUse':
                                case 'Featured': //they are boolean
                                    if( isset( $_POST[$sItemName] ) and $_POST[$sItemName] == 'yes' )
                                        $mValue = true;
                                    else
                                        $mValue = false;
                                break;

                                case 'Captcha':
                                case 'Status': // they are select_one
                                    if( isset( $_POST[$sItemName] ) )
                                        $mValue = process_pass_data( $_POST[$sItemName] );
                                break;

                                case 'ProfilePhoto':
                                    if (isset($_FILES['ProfilePhoto'])) {
                                        if ($_FILES['ProfilePhoto']['error'] == UPLOAD_ERR_OK) {
                                            $sTmpName  = tempnam($GLOBALS['dir']['tmp'], 'pphot');
                                            if (move_uploaded_file($_FILES['ProfilePhoto']['tmp_name'], $sTmpName))
                                                $mValue = basename($sTmpName);
                                        }
                                    } elseif (isset($_POST['ProfilePhoto']) && trim($_POST['ProfilePhoto'])) {
                                        $mValue = preg_replace('/[^a-zA-Z0-9\.]/', '', $_POST['ProfilePhoto']);
                                    }
                                break;
                            }
                        break;
                    }

                    $rRes = $this -> checkPostValue( $iBlockID, $iItemID, $mValue, $iHuman, $iProfileID );

                    if( $rRes !== true )
                        $aErrors[$iHuman][$sItemName] = $rRes; //it is returned error text

                    //if password on edit page
                    if( $aItem['Type'] == 'pass' and ( $this -> iAreaID == 2 or $this -> iAreaID == 3 or $this -> iAreaID == 4 ) ) {
                        if( empty($mValue) )
                            $mValue = $aValues[$iHuman][$sItemName];
                        else
                            $mValue = encryptUserPwd($mValue, $aValues[$iHuman]['Salt']);
                    }

                    $aValues[$iHuman][$sItemName] = $mValue;
                }
            }
        }
    }

    function checkPostValue( $iBlockID, $iItemID, $mValue, $iHuman, $iProfileID )
    {
        // get item
        $aItem = $this -> aBlocks[$iBlockID]['Items'][$iItemID];
        if( !$aItem )
            return 'Item not found';

        $aChecks = array(
            'text' => array( 'Mandatory', 'Min', 'Max', 'Unique', 'Check' ),
            'area' => array( 'Mandatory', 'Min', 'Max', 'Unique', 'Check' ),
            'html_area' => array( 'Mandatory', 'Min', 'Max', 'Unique', 'Check' ),
            'pass' => array( 'Mandatory', 'Min', 'Max', 'Check', 'PassConfirm' ),
            'date' => array( 'Mandatory', 'Min', 'Max', 'Check' ),
            'select_one' => array( 'Min', 'Max', 'Mandatory', 'Values', 'Check' ),
            'select_set' => array( 'Min', 'Max', 'Mandatory', 'Values', 'Check' ),
            'num'    => array( 'Mandatory', 'Min', 'Max', 'Unique', 'Check' ),
            'range'  => array( 'Mandatory', 'RangeCorrect', 'Min', 'Max', 'Check' ),
            'system' => array( 'System' ),
            'bool'   => array( 'Mandatory' )
        );

        $aMyChecks = $aChecks[ $aItem['Type'] ];

        // if ($aItem['Type'] == 'date') return $mValue;

        foreach ($aMyChecks as $sCheck ) {
            $sFunc = 'checkPostValueFor' . $sCheck;

            $mRes = $this -> $sFunc( $aItem, $mValue, $iHuman, $iProfileID );

            if( $mRes !== true ) {
                if( is_bool( $mRes ) ) // it is false...
                    return _t( $aItem[ $sCheck . 'Msg' ], $aItem[$sCheck] );
                else
                    return $mRes; // returned as text
            }
        }

        return true;
    }

    function checkPostValueForPassConfirm( $aItem, $mValue, $iHuman )
    {
        $sConfPass = process_pass_data( $_POST[ "{$aItem['Name']}_confirm" ][$iHuman] );
        if( $sConfPass != $mValue )
            return _t( '_Password confirmation failed' );
        else
            return true;
    }

    function checkPostValueForRangeCorrect( $aItem, $mValue )
    {
        if( is_null($mValue[0]) or is_null($mValue[1]) )
            return true; // if not set, pass this check

        if( $mValue[0] > $mValue[1] )
            return _t( '_First value must be bigger' );

        return true;
    }

    function checkPostValueForMin( $aItem, $mValue )
    {
        $iMin = $aItem['Min'];
        if( is_null($iMin) )
            return true;

        switch( $aItem['Type'] ) {
            case 'text':
            case 'area':
                if( mb_strlen( $mValue ) < $iMin )
                    return false;
            break;

            case 'html_area' :
                if( mb_strlen( strip_tags($mValue) ) < $iMin )
                    return false;
            break;

            case 'pass':
                if( mb_strlen( $mValue ) > 0 and mb_strlen( $mValue ) < $iMin )
                    return false;
            break;

            case 'num':
                if( $mValue < $iMin )
                    return false;
            break;

            case 'date':
                if( $this -> getAge($mValue) < $iMin )
                    return false;
            break;

            case 'range':
                if( $mValue[0] < $iMin || $mValue[1] < $iMin )
                    return false;
            break;

            case 'select_set':
                if( count( $mValue ) < $iMin )
                    return false;
            break;
        }

        return true;
    }

    function checkPostValueForMax( $aItem, $mValue )
    {
        $iMax = $aItem['Max'];
        if( is_null($iMax) )
            return true;

        switch( $aItem['Type'] ) {
            case 'text':
            case 'area':
            case 'pass':
                if( mb_strlen( $mValue ) > $iMax )
                    return false;
            break;

            case 'html_area':
                if( mb_strlen( strip_tags($mValue) ) > $iMax )
                    return false;
            break;

            case 'num':
                if( $mValue > $iMax )
                    return false;
            break;

            case 'date':
                if( $this -> getAge($mValue) > $iMax )
                    return false;
            break;

            case 'range':
                if( $mValue[0] > $iMax || $mValue[1] > $iMax )
                    return false;
            break;

            case 'select_set':
                if( count( $mValue ) > $iMax )
                    return false;
            break;
        }

        return true;
    }

    function checkPostValueForUnique( $aItem, $mValue, $iHuman, $iProfileID )
    {
        global $logged;

        if( !$aItem['Unique'] )
            return true;

        $iProfileID = (int)$iProfileID;
        if( $iProfileID ) {
            $sAdd = "AND `ID` != $iProfileID";
        } else
            $sAdd = '';
        
        $sQuery = "SELECT COUNT(*) FROM `Profiles` WHERE `{$aItem['Name']}` = ? $sAdd";
        if( (int)db_value( $sQuery, [$mValue] ) )
            return false;

        return true;
    }

    function checkPostValueForCheck( $aItem, $mValue )
    {
        $sCheck = $aItem['Check'];
        if( empty($sCheck) )
            return true;

        $sFunc = function($arg0) use ($sCheck) {
            return eval($sCheck);
        };

        if( !$sFunc( $mValue ) )
            return false;

        return true;
    }

    function checkPostValueForMandatory( $aItem, $mValue )
    {
        if( !$aItem['Mandatory'] )
            return true;

        if( $aItem['Type'] == 'num' ) {
            if( is_null($mValue) )
                return false;
        } elseif( $aItem['Type'] == 'range' ) {
            if( is_null($mValue[0]) or is_null($mValue[1]) )
                return false;
        } elseif( $aItem['Type'] == 'pass' ) {
            if( $this -> iAreaID == 2 or $this -> iAreaID == 3 or $this -> iAreaID == 4 ) // if area is edit, non-mandatory
                return true;
            else
                if( empty($mValue) ) // standard check
                    return false;
        } else {
            if( empty($mValue) )
                return false;
        }

        return true;
    }

    function checkPostValueForValues( $aItem, $mValue )
    {
        if( empty($mValue) ) //it is not selected
            return true;

        if( is_array( $aItem['Values'] ) )
            $aValues = $aItem['Values'];
        else
            $aValues = $this -> getPredefinedKeysArr( $aItem['Values'] );

        if( !$aValues )
            return 'Cannot find list';

        if( $aItem['Type'] == 'select_one' ) {
            if( !in_array( $mValue, $aValues ) )
                return 'Value not in list. Hack attempt!';
        } elseif( $aItem['Type'] == 'select_set' ) {
            foreach( $mValue as $sValue )
                if( !in_array( $sValue, $aValues ) )
                    return 'Value not in list. Hack attempt!';
        }

        return true;
    }

    function getDefaultValues()
    {
        $aItems = array();
        foreach($this -> aCache[100][0]['Items'] as $aItem)
            if(!empty($aItem["Default"]))
                $aItems[$aItem["Name"]] = $aItem["Default"];
        return $aItems;
    }

    function getPredefinedKeysArr( $sKey )
    {
        global $aPreValues;

        if( substr( $sKey, 0, 2 ) == $this->sLinkPref )
            $sKey = substr( $sKey, 2 );

        return @array_keys( $aPreValues[$sKey] );
    }

    function checkPostValueForSystem( $aItem, $mValue )
    {
        switch( $aItem['Name'] ) {
            case 'Captcha':
                return ( $this -> checkCaptcha( $mValue ) ) ? true : _t( '_Captcha check failed' );
            break;

            case 'Status':
                if( !in_array($mValue, $aItem['Values'] ) )
                    return 'Status hack attempt!';
            break;
            
            case 'Agree':
            case 'TermsOfUse':
                $i = getParam(base64_decode('c3lzX2FudGlzcGFtX3NtYXJ0X2NoZWNr')) && bx_get('do_submit');
                bx_import('BxDolStopForumSpam');
                $oBxDolStopForumSpam = new BxDolStopForumSpam();
                if (2 == getParam('ipBlacklistMode') && bx_is_ip_blocked())
                    return _t('_Sorry, your IP been banned');
                elseif (('on' == getParam('sys_dnsbl_enable') && 'block' == getParam('sys_dnsbl_behaviour') && bx_is_ip_dns_blacklisted('', 'join')) || $i || $oBxDolStopForumSpam->isSpammer(array('email' => $_POST['Email'][0], 'ip' => getVisitorIP(false)), 'join'))
                    return sprintf(_t('_sys_spam_detected'), BX_DOL_URL_ROOT . 'contact.php');
                else
                    return $aItem['Name'] != 'TermsOfUse' || $mValue ? true : _t( '_You must agree with terms of use' );
            break;

            case 'ProfilePhoto':
                if ($aItem['Mandatory'] && is_null($mValue))
                    return _t( '_Please specify image file' );

                if (( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ))
                    return true;

                $sFileName = $GLOBALS['dir']['tmp'] . $mValue;

                if ($mValue && !file_exists($sFileName)) // hack attempt
                    return 'No way! File not exists: ' . $sFileName;

                $aSize = @getimagesize($sFileName);
                if ($mValue && !$aSize) {
                    @unlink($sFileName);
                    return _t( '_Please specify image file' );
                }

                if ($mValue && $aSize[2] != IMAGETYPE_GIF && $aSize[2] != IMAGETYPE_JPEG && $aSize[2] != IMAGETYPE_PNG) {
                    unlink($sFileName);
                    return _t( '_Please specify image of JPEG, GIF or PNG format' );
                }

                return true;
            break;
        }

        return true;
    }

    function checkCaptcha( $mValue )
    {
        bx_import('BxDolForm');
        return (new BxDolFormCheckerHelper)->checkCaptcha($mValue);
    }

    function getAge( $sBirthDate )
    {
        /*
        // Old style 28/10/1985
        $bd = explode( '/', $sBirthDate );
        foreach ($bd as $i => $v) $bd[$i] = (int)$v;

        if ( date('n') > $bd[1] || ( date('n') == $bd[1] && date('j') >= $bd[0] ) )
            $age = date('Y') - $bd[2];
        else
            $age = date('Y') - $bd[2] - 1;
        */

        // New style 1985-10-28
        $bd = explode( '-', $sBirthDate );
        foreach ($bd as $i => $v) $bd[$i] = intval($v);

        if (intval(date('n')) > $bd[1] || (intval(date('n')) == $bd[1] && intval(date('j')) >= $bd[2]))
            $age = intval(date('Y')) - $bd[0];
        else
            $age = intval(date('Y')) - $bd[0] - 1;

        return $age;
    }

    // create intuitive array of values from default text profile array (getProfileInfo)
    function getValuesFromProfile( $aProfile )
    {
        $aValues = array();

        foreach( $this -> aBlocks as $aBlock ) {
            foreach( $aBlock['Items'] as $aItem ) {
                $sItemName = $aItem['Name'];
                if( !array_key_exists( $sItemName, $aProfile ))
                    continue; //pass this

                $mValue = $aProfile[$sItemName];

                switch( $aItem['Type'] ) {
                    case 'select_set':
                        $mValue = explode( ',', $mValue );
                    break;

                    case 'range':
                        $mValue = explode( ',', $mValue );
                        foreach( $mValue as $iInd => $sValue )
                            $mValue[$iInd] = (int)$sValue;
                    break;

                    case 'bool':
                        $mValue = (bool)$mValue;
                    break;

                    case 'num':
                        $mValue = (int)$mValue;
                    break;

                    case 'date':
                        /*
                        $aDate = explode( '-', $mValue ); //YYYY-MM-DD
                        $mValue = (int)$aDate[2] . '/' . (int)$aDate[1] . '/' . $aDate[0];
                        */

                        //return $mValue;
                    break;

                    case 'system':
                        switch( $sItemName ) {
                            case 'Couple':
                            case 'ID':
                                $mValue = (int)$mValue;
                            break;

                            case 'Featured':
                                $mValue = (bool)$mValue;
                            break;
                        }
                    break;
                }

                $aValues[$sItemName] = $mValue;
            }
        }
        $aValues['Salt'] = $aProfile['Salt']; // encryptUserPwd

        return $aValues;
    }

    // reverse of previous function. convert intuitive array to text array
    function getProfileFromValues( $aValues )
    {
        $aProfile = array();

        if( $this -> iAreaID == 1 ) {
            $aBlocks = array();
            foreach( array_keys( $this -> aArea ) as $iPage )
                $aBlocks = array_merge( $aBlocks, $this -> aArea[ $iPage ] );
        } else
            $aBlocks = $this -> aBlocks;

        foreach( $aBlocks as $aBlock ) {
            foreach( $aBlock['Items'] as $aItem ) {
                $sItemName = $aItem['Name'];
                if( !array_key_exists( $sItemName, $aValues ) )
                    continue; //pass this

                $mValue = $aValues[$sItemName];

                /*
                // convertation
                switch( $aItem['Type'] ) {
                    case 'date':
                        $aDate = explode( '/', $mValue );
                        $mValue = sprintf( '%04d-%02d-%02d', $aDate[2], $aDate[1], $aDate[0] );
                    break;

                    //impl others
                }
                */

                $aProfile[$sItemName] = $mValue;
            }
        }

        return $aProfile;
    }

    //internal function
    function _getCoupleMutualFields()
    {
        $aAllItems = $this -> aCache[100][0]['Items'];

        $this -> aCoupleMutual = array( 'NickName', 'Password', 'Email', 'Country', 'City', 'zip', 'EmailNotify' );
        $this -> aCoupleMutualCopy = array('Country', 'City', 'zip');

        foreach( $aAllItems as $aItem ) {
            if( $aItem['Name'] == 'Couple' ) {
                $a = explode("\n", $aItem['Extra']);
                array_walk($a, function (&$sItem, $iKey) {
                    $sItem = trim($sItem);
                });
                $this -> aCoupleMutual = array_merge( $this -> aCoupleMutual, $a ); // add specified values
            }

            if( $aItem['Type'] == 'system' && 'Age' != $aItem['Name'])
                $this -> aCoupleMutual[] = $aItem['Name'];

            if( $aItem['Type'] == 'pass' )
                $this -> aCoupleMutual[] = $aItem['Name'] . '_confirm';
        }
    }

    //external function
    function getCoupleMutualFields()
    {
        return $this -> aCoupleMutual;
    }

    function getCoupleMutualFieldsCopy()
    {
        return $this -> aCoupleMutualCopy;
    }

    function getViewableValue( $aItem, $sValue )
    {
        global $site;

        switch( $aItem['Type'] ) {
            case 'text':
            case 'num':
            case 'area':
                return nl2br(htmlspecialchars_adv($sValue));

            case 'html_area':
                return $sValue;

            case 'date':
                return $this -> getViewableDate($sValue);

            case 'range':
                return htmlspecialchars_adv( str_replace( ',', ' - ', $sValue ) );

            case 'bool':
                return _t( $sValue ? '_Yes' : '_No' );

            case 'select_one':
                $sValueView = $this -> getViewableSelectOne( $aItem['Values'], $sValue );

                if ($aItem['Name'] == 'Country') {
                    $sFlagName = strtolower($sValue);
                    $sValueView = '<img src="' . $site['flags']  . $sFlagName . '.gif" /> ' . $sValueView;
                }

                return $sValueView;

            case 'select_set':
                return $this -> getViewableSelectSet( $aItem['Values'], $sValue );

            case 'system':
                switch( $aItem['Name'] ) {
                    case 'Age':
                        return age($sValue);

                    case 'DateReg':
                    case 'DateLastEdit':
                    case 'DateLastLogin':
                    case 'DateLastNav':
                        return $this -> getViewableDate($sValue, BX_DOL_LOCALE_DATE);

                    case 'Status':
                        return _t( "_$sValue" );

                    case 'ID':
                        return $sValue;

                    case 'Featured':
                        return _t( $sValue ? '_Yes' : '_No' );

                    default:
                        return '&nbsp;';
                }
            break;

            case 'pass':
            default:
                return '&nbsp;';
        }
    }

    /**
     * Get viewable date
     *
     * @param $sDate string
     * @param $iFormat integer
     * @return string
     */
    function getViewableDate($sDate, $iFormat = BX_DOL_LOCALE_DATE_SHORT)
    {
        $sViewableDate = $sDate != '0000-00-00 00:00:00' && $sDate != '0000-00-00'
            ? getLocaleDate( strtotime($sDate),  $iFormat)
            : _t('_undefined');

        return $sViewableDate;
    }

    function getViewableSelectOne( $mValues, $sValue, $sUseLKey = 'LKey' )
    {
        global $aPreValues;

        if( is_string($mValues) and substr($mValues, 0, 2) == $this->sLinkPref ) {
            $sKey = substr($mValues, 2);

            if( !isset( $aPreValues[$sKey][$sUseLKey] ) )
                $sUseLKey = 'LKey';

            return htmlspecialchars_adv( _t( $aPreValues[$sKey][$sValue][$sUseLKey] ) );
        } elseif( is_array($mValues) ) {
            if( in_array($sValue, $mValues) )
                return htmlspecialchars_adv( _t( "_FieldValues_{$sValue}" ) );
                //return htmlspecialchars_adv( _t( "_$sValue" ) );
            else
                return '';
        } else
            return '';
    }

    function getViewableSelectSet( $mValues, $sValue, $sUseLKey = 'LKey' )
    {
        global $aPreValues;

        if( is_string($mValues) and substr($mValues, 0, 2) == $this->sLinkPref ) {
            $sKey = substr($mValues, 2);
            if( !isset( $aPreValues[$sKey] ) )
                return '&nbsp;';

            $aValues = explode( ',', $sValue );

            $aTValues = array();

            foreach( $aValues as $sValue )
                $aTValues[] = _t( $aPreValues[$sKey][$sValue][$sUseLKey] );

            return htmlspecialchars_adv( implode( ', ', $aTValues ) );
        } elseif( is_array($mValues) ) {
            $aValues = array();
            foreach( explode( ',', $sValue ) as $sValueOne )
                $aValues[] = _t( "_FieldValues_{$sValueOne}" );
                //$aValues[] = _t( "_$sValueOne" );

            return htmlspecialchars_adv( implode( ', ', $aValues ) );
        } else
            return '';
    }

    function collectSearchRequestParams()
    {
        $aParams = array();

        if( empty($_GET) and empty($_POST) )
            return $aParams;

        foreach( $this -> aBlocks as $aBlock ) {
            foreach( $aBlock['Items'] as $aItem ) {
                $sItemName = $aItem['Name'];
                $mValue = null;

                switch( $aItem['Type'] ) {
                    case 'text':
                    case 'area':
                    case 'html_area':
                        if( isset( $_REQUEST[$sItemName] ) and $_REQUEST[$sItemName] )
                            $mValue = process_pass_data( $_REQUEST[$sItemName] );
                    break;

                    case 'num':
                    case 'date':
                    case 'range':
                        if( isset( $_REQUEST[$sItemName] ) and !empty( $_REQUEST[$sItemName] ) ) {
                            $mValue = explode('-', $_REQUEST[$sItemName], 2);

                            $mValue[0] = (int)$mValue[0];
                            $mValue[1] = (int)$mValue[1];

                            if( !$mValue[0] and !$mValue[1] )
                                $mValue = null; // if no values entered, skip them
                        }
                    break;

                    case 'select_one':
                    case 'select_set':
                        if( isset( $_REQUEST[$sItemName] ) and !empty( $_REQUEST[$sItemName] ) ) {
                            if (is_array( $_REQUEST[$sItemName] )) {
                                $mValue = array();

                                foreach( $_REQUEST[$sItemName] as $sValue ) {
                                    $sValue = trim( process_pass_data( $sValue ) );
                                    if( $sValue )
                                        $mValue[] = $sValue;
                                }
                            } else {
                                $mValue = trim( process_pass_data( $_REQUEST[$sItemName] ) );
                            }
                        }
                        if (!$mValue)
                            $mValue = null;
                    break;

                    case 'bool':
                        if( isset( $_REQUEST[$sItemName] ) and $_REQUEST[$sItemName] )
                            $mValue = true;
                    break;

                    case 'system':
                        switch( $sItemName ) {
                            case 'ID':
                                if( isset( $_REQUEST[$sItemName] ) and (int)$_REQUEST[$sItemName] )
                                    $mValue = (int)$_REQUEST[$sItemName];
                            break;

                            case 'Couple':
                                if( isset( $_REQUEST[$sItemName] ) and is_array( $_REQUEST[$sItemName] ) ) {
                                    if( isset( $_REQUEST[$sItemName][0] ) and isset( $_REQUEST[$sItemName][1] ) )
                                        $mValue = '-1'; //pass
                                    elseif( isset( $_REQUEST[$sItemName][0] ) )
                                        $mValue = 0;
                                    elseif( isset( $_REQUEST[$sItemName][1] ) )
                                        $mValue = 1;
                                } elseif( isset( $_REQUEST[$sItemName] ) ) {
                                    $mValue = 'yes' == $_REQUEST[$sItemName] ? 1 : 0;
                                }
                            break;

                            case 'Location':

                            break;

                            case 'Keyword':
                                if( isset( $_REQUEST[$sItemName] ) and trim( $_REQUEST[$sItemName] ) )
                                    $mValue = trim( process_pass_data( $_REQUEST[$sItemName] ) );
                            break;

                        }
                    break;
                }

                if( !is_null( $mValue ) )
                    $aParams[ $sItemName ] = $mValue;
            }
        }

        return $aParams;
    }

    function getProfilesMatch( $aProf1, $aProf2 )
    {
        if( !$this -> aArea )
            return 0;

        $aFields1 = $this -> aBlocks[0]['Items'];
        $aFields2 = $this -> aCache[100][0]['Items'];

        $iMyPercent = 0;
        $iTotalPercent = 0;
        foreach( $aFields1 as $aField1 ) {
            $aField2 = $aFields2[ $aField1['MatchField'] ];
            if( !$aField2 )
                continue;

            $iTotalPercent += $aField1['MatchPercent'];

            $sVal1 = $aProf1[ $aField1['Name'] ];
            $sVal2 = $aProf2[ $aField2['Name'] ];

            if( !strlen($sVal1) or !strlen($sVal2) )
                continue;

            $iAddPart = 0;
            switch( "{$aField1['Type']} {$aField1['Type']}" ) {
                case 'select_set select_one':
                    $aVal1 = explode( ',', $sVal1 );

                    if( in_array( $sVal2, $aVal1 ) )
                        $iAddPart = 1;
                break;

                case 'select_one select_set':
                    $aVal2 = explode( ',', $sVal2 );

                    if( in_array( $sVal1, $aVal2 ) )
                        $iAddPart = 1;
                break;

                case 'select_set select_set':
                    $aVal1 = explode( ',', $sVal1 );
                    $aVal2 = explode( ',', $sVal2 );

                    $iFound = 0;
                    foreach( $aVal1 as $sTempVal1 ) {
                        if( in_array( $sTempVal1, $aVal2 ) )
                            $iFound ++;
                    }

                    $iAddPart = $iFound / count( $aVal1 );
                break;

                case 'range num':
                    $aVal1 = explode( ',', $sVal1 );
                    $sVal2 = (int)$sVal2;

                    if( (int)$aVal1[0] <= $sVal2 and $sVal2 <= (int)$aVal1[0] )
                        $iAddPart = 1;
                break;

                case 'range date':
                    $aVal1 = explode( ',', $sVal1 );

                    $aDate = explode( '-', $sVal2 );
                    $sVal2 = sprintf( '%d/%d/%d', $aDate[2], $aDate[1], $aDate[0] );
                    $sAge = $this -> getAge( $sVal2 );

                    if( (int)$aVal1[0] <= $sVal2 and $sVal2 <= (int)$aVal1[0] )
                        $iAddPart = 1;
                break;

                default:
                    if( $sVal1 == $sVal2 )
                        $iAddPart = 1;
            }

            $iMyPercent    += round( $aField1['MatchPercent'] * $iAddPart );
        }

        if( $iTotalPercent != 100 && $iTotalPercent != 0 )
            $iMyPercent = (int)( ( $iMyPercent / $iTotalPercent ) * 100 );

        return $iMyPercent;
    }

    function getFormCode($aParams = null)
    {
        switch ($this->iAreaID) {
            // join
            case 1:
                $aForm = $this->getFormJoin($aParams);
            break;

            // edit
            case 2:
            case 3:
            case 4:
                $aForm = $this->getFormEdit($aParams);
            break;

            // search
            case 9:
            case 10:
            case 11:
                return $this->getFormsSearch($aParams);
            break;

            default:
                return false;
        }

        $oForm = new BxTemplFormView($aForm);

        bx_import('BxDolAlerts');
        $sCustomHtmlBefore = '';
        $sCustomHtmlAfter = '';
        $oAlert = new BxDolAlerts('profile', 'show_profile_form', 0, 0, array('oProfileFields' => $this, 'oForm' => $oForm, 'sCustomHtmlBefore' => &$sCustomHtmlBefore, 'sCustomHtmlAfter' => &$sCustomHtmlAfter));
        $oAlert->alert();

        return $sCustomHtmlBefore . $oForm->getCode() . $sCustomHtmlAfter;
    }

    function getFormsSearch($aParams, $bReturnArray = false)
    {
        $aShowModes = array('featured', 'birthdays', 'top_rated', 'popular', 'moderators');

        // original member profile, used for setting default search params
        $aDefaultParams = $aParams['default_params'];

        $sSearchModeName = ($this->iAreaID == 10 ? 'quick' : ($this->iAreaID == 11 ? 'adv' : 'simple'));

        $sResult = '';
        $aResult = array();

        $iFormCounter = 1;

        // generate blocks
        foreach ($this->aBlocks as $iBlockId => $aBlock) {

            $bAddFlags = true; // flags "online only" and "photos only"

            //collect inputs
            $aInputs = array();

            // create search mode hidden input
            $aInputs[] = array(
                'type'  => 'hidden',
                'name'  => 'search_mode',
                'value' => $sSearchModeName,
            );
            
            // create search result mode hidden input (if requested)
            $sSrmKey = 'search_result_mode';
            if(!empty($aDefaultParams[$sSrmKey])) {
                $aInputs[] = array(
                    'type'  => 'hidden',
                    'name'  => $sSrmKey,
                    'value' => $aDefaultParams[$sSrmKey],
                );

                unset($aDefaultParams[$sSrmKey]);
            }

            // create show parameter as hidden input 
            $aInputs[] = array(
                'type'  => 'hidden',
                'name'  => 'show',
                'value' => isset($_REQUEST['show']) && in_array($_REQUEST['show'], $aShowModes) ? $_REQUEST['show'] : '',
            );

            // generate block input
            $aInputs[] = array(
                'type' => 'block_header',
                'caption' => _t($aBlock['Caption']),
            );

            // generate inputs for items of this block
            foreach ($aBlock['Items'] as $iItemId => $aItem) {

                if ($iItemId == 1 or $iItemId == 2)
                    $bAddFlags = false; // do not add flags when username or id available

                // generate input
                $aFormInput = array(
                    'name'    => $aItem['Name'],
                    'caption' => (_t($aItem['Caption']) != $aItem['Caption']) ? _t($aItem['Caption']) : null,
                    'info'    => (_t($aItem['Desc'])    != $aItem['Desc'])    ? _t($aItem['Desc'])    : null,
                    'value'   => empty($aDefaultParams[$aItem['Name']]) ? '' : $aDefaultParams[$aItem['Name']],
                );

                switch ($aItem['Type']) {
                    case 'text':
                    case 'area': // search in area like simple keyword
                    case 'html_area': // search in area like simple keyword
                        $aFormInput['type'] = 'text';
                    break;

                    case 'bool':
                        $aFormInput['value'] = 'yes';
                        $aFormInput['type'] = 'checkbox';
                    break;

                    case 'select_one':
                    case 'select_set':

                        switch($aItem['Control']) {
                            case 'select' :
                                $aFormInput['type'] = 'select_box';
                                $aFormInput['attrs']['add_other'] = 'false';
                                break;

                            case 'checkbox' :
                                $aFormInput['type'] = $aItem['Name'] == 'LookingFor' ? 'select' : 'checkbox_set';
                                break;

                            default :
                                $aFormInput['type'] = $aItem['Name'] == 'Sex' ? 'checkbox_set' : 'select';
                        }

                        $aFormInput['values'] = $this->convertValues4Input($aItem['Values'], $aItem['UseLKey'], 'search');
                        if($aItem['Type'] == 'select_one' && is_array($aFormInput['value'])) {
                            $aFormInput['value'] = $aFormInput['value'][0];
                        }
                    break;

                    case 'date':
                    case 'num':
                    case 'range':
                        $aFormInput['type'] = 'doublerange'; /* Changed because of realisation of WebForms 2.0 */
                        $aFormInput['attrs'] = array(
                            'min' => $aItem['Min'],
                            'max' => $aItem['Max'],
                        );
                    break;

                    case 'system':
                        switch ($aItem['Name']) {
                            case 'ID':
                                $aFormInput['type'] = 'number';
                                $aFormInput['attrs']['min'] = 1;
                            break;

                            case 'Keyword':
                                $aFormInput['type'] = 'text';
                            break;

                            case 'Location':
                                $sLivingWithinC = _t("_living within");
                                $sMilesC        = _t("_miles");
                                $sKmC           = _t("_kilometers");
                                $sFromZipC      = _t("_from zip/postal code");

                                $aFormInput['type'] = 'custom';

                                $aFormInput['content'] = <<<EOF
                                    <div class="location_wrapper">
                                        <div>
                                            <input type="text" name="distance" class="form_input_distance bx-def-round-corners-with-border bx-def-font" />
                                            <select name="metric" class="form_input_select form_input_metric">
                                                <option selected="selected" value="miles">$sMilesC</option>
                                                <option value="km">$sKmC</option>
                                            </select>
                                        </div>
                                        <div>
                                            $sFromZipC
                                            <input type="text" name="zip" class="form_input_zip bx-def-round-corners-with-border bx-def-font" />
                                        </div>
                                    </div>
EOF;
                            break;

                            case 'Couple':
                                if ('on' == getParam('enable_global_couple')) {
                                    $aFormInput['type'] = 'select';
                                    $aFormInput['values'] = array(
                                        'no'  => _t('_Single'),
                                        'yes' => _t('_Couple')
                                    );
                                } else {
                                    $aFormInput['type'] = 'hidden';
                                    $aFormInput['value'] = 'no';
                                }
                            break;
                        }
                    break;
                }

                $aInputs[] = $aFormInput;
            }

            if ($bAddFlags /* array_search($iBlockId, array_keys($this->aBlocks)) != 0 */) {
                // create input for "online only"
                $aInputs[] = array(
                    'type' => 'checkbox',
                    'name' => 'online_only',
                    'label' => _t('_online only'),
                    'checked' => !empty($aDefaultParams['online_only']) &&
                        ($aDefaultParams['online_only'] == 'on'),
                );

                // create input for "with photos only"
                bx_import('BxDolMemberInfo');
                $oMemberInfo = BxDolMemberInfo::getObjectInstance(getParam('sys_member_info_thumb'));
                if($oMemberInfo->isAvatarSearchAllowed())
                    $aInputs[] = array(
                        'type' => 'checkbox',
                        'name' => 'photos_only',
                        'label' => _t('_With photos only'),
                        'checked' => !empty($aDefaultParams['photos_only']) &&
                            $aDefaultParams['photos_only'] == 'on',
                    );
            }

            // create submit button
            $aInputs[] = array(
                'type' => 'submit',
                'name' => 'submit',
                'value' => _t('_Search'),
                'colspan' => true, // colspan
            );

            // create form array
            $aForm = array(
                'form_attrs' => array(
                    'method' => 'get',
                    'action' => $GLOBALS['site']['url'] . 'search.php',
                    'name'   => $sSearchModeName . '_search_form' . $iFormCounter,
                ),
                'inputs' => $aInputs,
            );

            if (isset($aParams['form_attrs']) && is_array($aParams['form_attrs']))
                $aForm['form_attrs'] = array_merge ($aForm['form_attrs'], $aParams['form_attrs']);

            if (isset($aParams['inputs']) && is_array($aParams['inputs']))
                $aForm['inputs'] = array_merge ($aForm['inputs'], $aParams['inputs']);

            if(!$bReturnArray) {
                $oForm = new BxTemplFormView($aForm);
                $sResult .= $oForm->getCode();
            }
            else
                $aResult[] = $aForm;

            $iFormCounter++;
        } // block generation finished

        return !$bReturnArray ? $sResult : $aResult;
    }

    /**
     * Generate form for join
     *
     */
    function getFormJoin($aParams)
    {
        // get parameters
        $bDynamic = !empty($aParams['dynamic']) ? $aParams['dynamic'] : false;
        $bCoupleEnabled = $aParams['couple_enabled'];
        $bCouple = $aParams['couple'];
        $aHiddenItems = $aParams['hiddens'];
        $iPage = $aParams['page'];

        $aValues = $aParams['values'];
        $aErrors = $aParams['errors'];

        // collect inputs
        $aInputs = array();

        // convert array of hidden fields to inputs
        foreach ($aHiddenItems as $sName => $sValue) {
            $aInputs[] = array(
                'type'  => 'hidden',
                'name'  => $sName,
                'value' => $sValue,
            );
        }

        // add table headers
        /*
        $aInputs[] = array(
            'type' => 'headers',
            'tr_class' => 'hidable',
            0 => '&nbsp;',
            1 => _t( '_First Person' ),
            2 => _t( '_Second Person' ),
        );
        */

        // add every block on this page
        foreach( $this->aArea[$iPage] as $aBlock ) {
            // generate block header
            $aInputs[] = array(
                'type' => 'block_header',
                'caption' => _t( $aBlock['Caption'] ),
            );

            $aAddInputs = array();

            // add every item
            foreach( $aBlock['Items'] as $aItem ) {

                $aInputParams = array(
                	'dynamic' => $bDynamic,
                    'couple' => $bCouple,
                    'values' => array(
                        0 => isset($aValues[0][$aItem['Name']]) ? $aValues[0][$aItem['Name']] : null,
                        1 => isset($aValues[1][$aItem['Name']]) ? $aValues[1][$aItem['Name']] : null,
                    ),
                    'errors' => array(
                        0 => isset($aErrors[0][$aItem['Name']]) ? $aErrors[0][$aItem['Name']] : null,
                        1 => isset($aErrors[1][$aItem['Name']]) ? $aErrors[1][$aItem['Name']] : null,
                    ),
                );

                $aInputs[] = $this->convertJoinField2Input($aItem, $aInputParams, 0);

                if ($bCoupleEnabled && !in_array( $aItem['Name'], $this -> aCoupleMutual ))
                    $aAddInputs[] = $this->convertJoinField2Input($aItem, $aInputParams, 1);

                // duplicate password (confirmation)
                if ($aItem['Type'] == 'pass') {
                    $aItem_confirm = $aItem;

                    $aItem_confirm['Name']    .= '_confirm';
                    $aItem_confirm['Caption']  = '_Confirm password';
                    $aItem_confirm['Desc']     = '_Confirm password descr';

                    $aInputs[] = $this->convertJoinField2Input($aItem_confirm, $aInputParams, 0);

                    if ($bCoupleEnabled && !in_array( $aItem['Name'], $this -> aCoupleMutual ))
                        $aAddInputs[] = $this->convertJoinField2Input($aItem_confirm, $aInputParams, 1);
                }
            }

            // add second person
            if (!empty($aAddInputs)) {

                $aInputs[] = array(
                    'type' => 'block_header',
                    'caption' => _t( $aBlock['Caption'] ) . ' - ' . _t('_Second Person'),
                    'attrs' => array(
                        'class' => 'hidable',
                        'style' => 'display: ' . ($bCouple ? 'table-row' : 'none'),
                    ),
                );

                $aInputs = array_merge($aInputs, $aAddInputs);
            }
        }

        // add submit button
        $aInputs[] = array(
            'type' => 'submit',
            'name' => 'do_submit',
            'value' => _t( '_Join_now' ),
        	'attrs' => array(
				'class' => 'bx-btn-primary'
        	),
            'colspan' => false,
        );

        // generate form array
        $aForm = array(
            'form_attrs' => array(
                'name'     => 'join_form',
                'action'   => BX_DOL_URL_ROOT . 'join.php',
                'method'   => 'post',
                'onsubmit' => 'return validateJoinForm(this);',
                'enctype'  => 'multipart/form-data',
            ),
            'table_attrs' => array(
                'id' => 'join_form_table'
            ),
            'params' => array(
                'double'         => $bCoupleEnabled,
                'second_enabled' => $bCouple
            ),
            'inputs' => $aInputs,
        );

        return $aForm;
    }

    /**
     * Generate form for edit
     *
     */
    function getFormEdit($aParams)
    {
        // get parameters
        $bCoupleEnabled = $aParams['couple_enabled'];
        $bCouple        = $aParams['couple'];
        $aHiddenItems   = $aParams['hiddens'];

        $iProfileID     = $aParams['profile_id'];

        $aValues        = $aParams['values'];
        $aErrors        = $aParams['errors'];

        // collect inputs
        $aInputs = array();

        // convert array of hidden fields to inputs
        foreach ($aHiddenItems as $sName => $sValue) {
            $aInputs[] = array(
                'type'  => 'hidden',
                'name'  => $sName,
                'value' => $sValue,
            );
        }

        // add table headers (only if couple)
        /*
        if ($bCouple) {
            $aInputs[] = array(
                'type' => 'headers',
                'tr_class' => 'hidable',
                0 => '&nbsp;',
                1 => _t( '_First Person' ),
                2 => _t( '_Second Person' ),
            );
        }
        */

        // add every block on this page
        foreach( $this->aBlocks as $aBlock ) {
            // generate block header
            $aInputs[] = array(
                'type' => 'block_header',
                'caption' => _t( $aBlock['Caption'] ),
            );

            $aAddInputs = array();

            // add every item
            foreach( $aBlock['Items'] as $aItem ) {

                $aInputParams = array(
                    'couple'         => $bCouple,
                    'values'         => array(
                        0 => isset($aValues[0][$aItem['Name']]) ? $aValues[0][$aItem['Name']] : null,
                        1 => isset($aValues[1][$aItem['Name']]) ? $aValues[1][$aItem['Name']] : null,
                    ),
                    'errors'         => array(
                        0 => isset($aErrors[0][$aItem['Name']]) ? $aErrors[0][$aItem['Name']] : null,
                        1 => isset($aErrors[1][$aItem['Name']]) ? $aErrors[1][$aItem['Name']] : null,
                    ),
                    'profile_id' => $iProfileID,
                );

                $aInputs[] = $this->convertEditField2Input($aItem, $aInputParams, 0);

                if ($bCoupleEnabled && !in_array( $aItem['Name'], $this -> aCoupleMutual ))
                    $aAddInputs[] = $this->convertEditField2Input($aItem, $aInputParams, 1);

                // duplicate password (confirmation)
                if ($aItem['Type'] == 'pass') {
                    $aItem_confirm = $aItem;

                    $aItem_confirm['Name']    .= '_confirm';
                    $aItem_confirm['Caption']  = '_Confirm password';
                    $aItem_confirm['Desc']     = '_Confirm password descr';

                    $aInputs[] = $this->convertEditField2Input($aItem_confirm, $aInputParams, 0);

                    if ($bCoupleEnabled && !in_array( $aItem['Name'], $this -> aCoupleMutual ))
                        $aAddInputs[] = $this->convertEditField2Input($aItem, $aInputParams, 1);
                }
            }

            // add second person
            if (!empty($aAddInputs)) {

                $aInputs[] = array(
                    'type' => 'block_header',
                    'caption' => _t( $aBlock['Caption'] ) . ' - ' . _t('_Second Person'),
                    'attrs' => array(
                        'class' => 'hidable',
                        'style' => 'display: ' . ($bCouple ? 'table-row' : 'none'),
                    ),
                );

                $aInputs = array_merge($aInputs, $aAddInputs);
            }
        }

        // add submit button
        $aInputs[] = array(
            'type' => 'submit',
            'name' => 'do_save',
            'value' => _t( '_Save' ),
            'colspan' => false,
        );

        // generate form array
        $aForm = array(
            'form_attrs' => array(
                'name'     => 'edit_form',
                'action'   => BX_DOL_URL_ROOT . 'pedit.php?ID=' . $iProfileID,
                'method'   => 'post',
                'onsubmit' => 'return validateEditForm(this);',
            ),
            'table_attrs' => array(
                'id' => 'edit_form_table'
            ),
            'params' => array(
                'double'         => $bCoupleEnabled,
                'second_enabled' => $bCouple
            ),
            'inputs' => $aInputs,
        );

        return $aForm;
    }

    function convertEditField2Input($aItem, $aParams, $iPerson)
    {
        $bCouple        = $aParams['couple'];
        $aValues        = $aParams['values'];
        $aErrors        = $aParams['errors'];

        $iProfileID     = $aParams['profile_id'];

        $aInput = array();

        switch ($aItem['Type']) {
            case 'text':  $aInput['type'] = 'text';     $aInput['value'] = $aValues[$iPerson]; break;
            case 'area':  $aInput['type'] = 'textarea'; $aInput['value'] = $aValues[$iPerson]; break;
            case 'html_area':  $aInput['type'] = 'textarea'; $aInput['html'] = true; $aInput['value'] = $aValues[$iPerson]; break;
            case 'date':     $aInput['type'] = 'date';     $aInput['value'] = $aValues[$iPerson]; break;
            case 'datetime': $aInput['type'] = 'datetime'; $aInput['value'] = $aValues[$iPerson]; break;
            case 'num':   $aInput['type'] = 'number';   $aInput['value'] = $aValues[$iPerson]; break;
            case 'pass':  $aInput['type'] = 'password'; break;
            case 'range':
                $aInput['type'] = 'doublerange';
                $aInput['value'] = is_array($aValues[$iPerson]) ? $aValues[$iPerson][0] . '-' . $aValues[$iPerson][1] : $aValues[$iPerson];
                break;
            case 'bool':
                $aInput['type']    = 'checkbox';
                $aInput['value']   = 'yes';
                $aInput['checked'] = (bool)(int)$aValues[$iPerson];
            break;

            case 'select_one':
                switch ($aItem['Control']) {
                    case 'select': $aInput['type'] = 'select';    break;
                    case 'radio':  $aInput['type'] = 'radio_set'; break;

                    default: return false;
                }

                $aInput['values'] = $this->convertValues4Input($aItem['Values'], $aItem['UseLKey'], 'edit');

                $aInput['value'] = $aValues[$iPerson];
            break;

            case 'select_set':
                switch ($aItem['Control']) {
                    case 'select':   $aInput['type'] = 'select_multiple'; break;
                    case 'checkbox': $aInput['type'] = 'checkbox_set';    break;

                    default: return false;
                }

                $aInput['values'] = $this->convertValues4Input($aItem['Values'], $aItem['UseLKey'], 'edit');

                $aInput['value'] = $aValues[$iPerson];
            break;

            case 'system':
                switch ($aItem['Name']) {
                    case 'Featured':
                        $aInput = array(
                            'type' => 'checkbox',
                            'value' => 'yes',
                            'checked' => $aValues[0]
                        );
                    break;

                    case 'Status':
                        $aInput = array(
                            'type' => 'select',
                            'value' => $aValues[0],
                            'values' => array(),
                        );

                        foreach ($aItem['Values'] as $sValue) {
                            $aInput['values'][$sValue] = _t("_FieldValues_$sValue");
                        }
                    break;

                    case 'ID':
                    case 'DateReg':
                    case 'DateLastEdit':
                    case 'DateLastLogin':
                    case 'DateLastNav':
                        //non editable
                        return false;
                    break;

                    default: return false;
                }
            break;

            default: return false;
        }

        $aInput['name']     = ( $aItem['Type'] == 'system' ) ? $aItem['Name'] : ( $aItem['Name'] . "[$iPerson]" );
        $aInput['caption']  = _t( $aItem['Caption'] );
        $aInput['required'] = $aItem['Type'] == 'pass' ? false : $aItem['Mandatory'];
        $aInput['info']     = (
            ($sInfo = _t( $aItem['Desc'], $aItem['Min'], $aItem['Max'] )) != $aItem['Desc']) // if info is translated
            ? $sInfo : null;

        if ($aItem['Type'] == 'date') {
            $aInput['attrs']['min'] = $aItem['Max'] ? (date('Y') - $aItem['Max']) . '-' . date('m') . '-' . date('d') : (date('Y') - 100) . '-' . date('m') . '-' . date('d');
            $aInput['attrs']['max'] = $aItem['Min'] ? (date('Y') - $aItem['Min']) . '-' . date('m') . '-' . date('d') : (date('Y') + 100) . '-' . date('m') . '-' . date('d');
        } else {
            $aInput['attrs']['min'] = $aItem['Min'];
            $aInput['attrs']['max'] = $aItem['Max'];
        }

        $aInput['error']    = $aErrors[$iPerson];

        if ($iPerson == 1) {
            $aInput['tr_attrs'] = array(
                'class' => 'hidable',
                'style' => 'display: ' . ($bCouple ? 'table-row' : 'none'),
            );

        }

        return $aInput;
    }

    function convertJoinField2Input($aItem, $aParams, $iPerson)
    {
		$bDynamic = $aParams['dynamic'];
        $bCouple = $aParams['couple'];
        $aValues = $aParams['values'];
        $aErrors = $aParams['errors'];

        $aInput = array();

        switch ($aItem['Type']) {
            case 'text':  $aInput['type'] = 'text';     $aInput['value'] = $aValues[$iPerson]; break;
            case 'area':  $aInput['type'] = 'textarea'; $aInput['value'] = $aValues[$iPerson]; break;
            case 'html_area':
            	$aInput['type'] = 'textarea';
            	$aInput['html'] = true;
            	$aInput['dynamic'] = $bDynamic;
            	$aInput['value'] = $aValues[$iPerson]; 
            	break;
            case 'date':     $aInput['type'] = 'date';     $aInput['value'] = $aValues[$iPerson]; break;
            case 'datetime': $aInput['type'] = 'datetime'; $aInput['value'] = $aValues[$iPerson]; break;
            case 'num':   $aInput['type'] = 'number';   $aInput['value'] = $aValues[$iPerson]; break;
            case 'range':
                $aInput['type'] = 'doublerange';
                $aInput['value'] = is_array($aValues[$iPerson]) ? $aValues[$iPerson][0] . '-' . $aValues[$iPerson][1] : $aValues[$iPerson];
                break;
            case 'pass':  $aInput['type'] = 'password'; break;
            case 'bool':
                $aInput['type']    = 'checkbox';
                $aInput['value']   = 'yes';
                $aInput['checked'] = (bool)(int)$aValues[$iPerson];
            break;

            case 'select_one':
                switch ($aItem['Control']) {
                    case 'select': $aInput['type'] = 'select';    break;
                    case 'radio':  $aInput['type'] = 'radio_set'; break;
                }

                $aInput['values'] = $this->convertValues4Input($aItem['Values'], $aItem['UseLKey'], 'join');

                $aInput['value'] = $aValues[$iPerson];
            break;

            case 'select_set':
                switch ($aItem['Control']) {
                    case 'select':   $aInput['type'] = 'select_multiple'; break;
                    case 'checkbox': $aInput['type'] = 'checkbox_set';    break;
                }

                $aInput['values'] = $this->convertValues4Input($aItem['Values'], $aItem['UseLKey'], 'join');

                $aInput['value'] = $aValues[$iPerson];
            break;

            case 'system':
                switch ($aItem['Name']) {
                    case 'TermsOfUse':
                        $aInput = array(
                            'type' => 'checkbox',
                            'label' => _t($aItem['Caption']),
                            'colspan' => false,
                            'value' => 'yes',
                        );
                        $aItem['Caption'] = '';
                    break;

                    case 'Couple':
                        if ('on' == getParam('enable_global_couple')) {
                            $aInput = array(
                                'type' => 'select',
                                'values' => array(
                                    'no' => _t('_Single'),
                                    'yes' => _t('_Couple'),
                                ),
                                'attrs' => array(
                                    'onchange' => 'doShowHideSecondProfile(this.value, this.form);',
                                ),
                                'value' => $bCouple ? 'yes' : 'no',
                            );
                        } else {
                            $aInput = array(
                                'type' => 'hidden',
                                'value' => 'no',
                            );
                        }
                    break;

                    case 'Captcha':
                        $aInput['type'] = 'captcha';
                        $aInput['dynamic'] = $bDynamic; 
                    break;

                    case 'ProfilePhoto':
                        $aInput['type'] = 'file';
                        break;

					case 'Agree':
                        $aInput = array(
                            'type' => 'custom',
                            'colspan' => true,
                            'content' => _t('_join_form_note', BX_DOL_URL_ROOT) . '<input type="hidden" name="Agree" />',
                        );
                        $aItem['Caption'] = '';
                    break;
                }
            break;
        }

        $aInput['name']     = ( $aItem['Type'] == 'system' ) ? $aItem['Name'] : ( $aItem['Name'] . "[$iPerson]" );
        $aInput['caption']  = _t( $aItem['Caption'] );
        $aInput['required'] = $aItem['Mandatory'];
        $aInput['info']     = (
            ($sInfo = _t( $aItem['Desc'], $aItem['Min'], $aItem['Max'] )) != $aItem['Desc']) // if info is translated
            ? $sInfo : null;

        if ($aItem['Type'] == 'date') {
            $aInput['attrs']['min'] = $aItem['Max'] ? (date('Y') - $aItem['Max']) . '-' . date('m') . '-' . date('d') : (date('Y') - 100) . '-' . date('m') . '-' . date('d');
            $aInput['attrs']['max'] = $aItem['Min'] ? (date('Y') - $aItem['Min']) . '-' . date('m') . '-' . date('d') : (date('Y') + 100) . '-' . date('m') . '-' . date('d');
        } else {
            $aInput['attrs']['min'] = $aItem['Min'];
            $aInput['attrs']['max'] = $aItem['Max'];
        }

        $aInput['error'] = $aErrors[$iPerson];

        if ($iPerson == 1) {
            $aInput['tr_attrs'] = array(
                'class' => 'hidable',
                'style' => 'display: ' . ($bCouple ? 'table-row' : 'none'),
            );

        }

        return $aInput;
    }

    function convertValues4Input($mValues, $sUseLKey = 'LKey', $sFormName = '')
    {
        $aValues = array();

        if (is_array($mValues)) {
            foreach ($mValues as $sKey)
                $aValues[$sKey] = _t('_FieldValues_' . $sKey);
        } elseif (is_string($mValues) and !empty($mValues) and substr($mValues, 0, 2) == $this->sLinkPref) {
            $sKey = substr($mValues, 2);
            if (isset($GLOBALS['aPreValues'][$sKey]) ) {
                $aPValues = $GLOBALS['aPreValues'][$sKey];

                foreach ($aPValues as $k => $r) {
                    if (!isset($r[$sUseLKey]))
                        $sUseLKey = 'LKey';
                    $aValues[$k] = _t($r[$sUseLKey]);
                }

                if ('Country' == $sKey && $GLOBALS['oTemplConfig']->bForceSortCountries)
                    natsort($aValues);

                if(!empty($sFormName) && !empty($this->aAddPleaseSelect[$sFormName]) && is_array($this->aAddPleaseSelect[$sFormName]) && in_array($sKey, $this->aAddPleaseSelect[$sFormName]))
                	$aValues = array('' => _t('_Please_Select_')) + $aValues;
            }
        }

        return $aValues;
    }
}
