<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'admin.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'db.inc.php' );

bx_import('BxDolProfileFields');
bx_import('BxDolProfilesController' );
bx_import('BxTemplFormView');

class BxDolJoinProcessor
{
    var $oPF; //profile fields
    var $iPage; //currently shown page
    var $aPages; //available pages
    var $aValues; //inputted values
    var $aErrors; //errors generated on page
    var $bAjaxMode; // defines if the script were requested by ajax

    var $bCoupleEnabled;
    var $bCouple;

    function __construct($aParams = array())
    {
        $this -> aErrors = array( 0 => array(), 1 => array() );

        $this -> oPF = !empty($aParams['profile_fields']) ? $aParams['profile_fields'] : new BxDolProfileFields(1);

        $this -> aValues = array();
        $this -> aValues[0] = $this -> aValues[1] = $this -> oPF -> getDefaultValues();// double arrays (for couples)

        $this -> bAjaxMode = ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' );
    }

    function process($aAddonFields = array())
    {
        if(!$this->oPF->aArea)
            return 'Profile Fields cache not loaded. Cannot continue.';

        $this->aPages = array_keys($this->oPF->aArea);

        $this->iPage = isset($_POST['join_page']) ? $_POST['join_page'] : 0; // get current working page from POST
        if($this->iPage !== 'done' )
            $this->iPage = (int)$this->iPage;

        $this->getCoupleOptions();

        $this->processPostValues();

        if($this->bAjaxMode && bx_get('join_page_validate') !== false) {
            echo $this->showErrorsJson();
            exit;
        }

		$sContent = '';
		if($this->iPage === 'done' ) { //if all pages are finished and no errors found
			list($iMemberId, $sStatus) = $this->registerMember();

			$sContent .= !$iMemberId ? $this->showFailPage() : $this->showFinishPage($iMemberId, $sStatus);
		}
		else
			$sContent .= $this->showJoinForm($aAddonFields);

		return $sContent;        
    }

    function getCoupleOptions()
    {
        //find Couple item (check if it is active)
        $aCoupleItem = false;
        foreach ($this -> aPages as $iPageInd => $iPage) { //cycle pages
            $aBlocks = $this -> oPF -> aArea[ $iPage ];
            foreach ($aBlocks as $iBlockID => $aBlock) {   //cycle blocks
                $aItems = $aBlock['Items'];
                foreach ($aItems as $iItemID => $aItem) {  //cycle items
                    if( $aItem['Name'] == 'Couple' ) { // we found it!
                        $aCoupleItem = $aItem;
                        break;
                    }
                }

                if( $aCoupleItem ) // we already found it
                    break;
            }

            if( $aCoupleItem ) // we already found it
                break;
        }

        if( $aCoupleItem ) {
            $this -> bCoupleEnabled      = true;
            $this -> bCouple             = ( isset( $_REQUEST['Couple'] ) and $_REQUEST['Couple'] == 'yes' ) ? true : false;
        } else {
            $this -> bCoupleEnabled      = false;
            $this -> bCouple             = false;
        }
    }

    function processPostValues()
    {
        foreach ($this -> aPages as $iPage) { //cycle pages

            if( $this -> iPage !== 'done' and $iPage >= $this -> iPage ) {
                $this -> iPage = $iPage; // we are on the current page. dont process these values, dont go further, just show form.
                break;
            }

            // process post values by Profile Fields class
            $this -> oPF -> processPostValues( $this -> bCouple, $this -> aValues, $this -> aErrors, $iPage );

            if( !empty( $this -> aErrors[0] ) or ( $this -> bCouple and !empty( $this -> aErrors[1] ) ) ) { //we found errors on previous page
                // do not process further values, just go to erroneous page.
                $this -> iPage = $iPage;
                break;
            }
        }
    }

    function showErrorsJson()
    {
		header('Content-Type:text/javascript; charset=utf-8');
        return $this -> oPF -> genJsonErrors( $this -> aErrors, $this -> bCouple );
    }

    function showJoinForm($aAddonFields = array())
    {
        $aJoinFormParams = array(
        	'dynamic' => $this->bAjaxMode,
            'couple_enabled' => $this->bCoupleEnabled,
            'couple' => $this->bCouple,
            'page' => $this->iPage,
            'hiddens' => $this->genHiddenFieldsArray($aAddonFields),
            'errors' => $this->aErrors,
            'values' => $this->aValues,
        );

        return $this->oPF->getFormCode($aJoinFormParams);
    }

    function genHiddenFieldsArray($aHiddenFields = array())
    {
        //retrieve next page
        $iPageInd = (int)array_search( $this -> iPage, $this -> aPages );
        $iNextInd = $iPageInd + 1;

        if( array_key_exists( $iNextInd, $this -> aPages ) )
            $sNextPage = $this -> aPages[ $iNextInd ];
        else
            $sNextPage = 'done';

        // insert next page
        $aHiddenFields['join_page'] = $sNextPage;

        //echoDbg( $this -> aValues );

        // insert entered values
        $iHumans = $this -> bCouple ? 2 : 1;
        for( $iHuman = 0; $iHuman < $iHumans; $iHuman ++ ) {
            foreach( $this -> aPages as $iPage ) {
                if( $iPage == $this -> iPage )
                    break; // we are on this page

                $aBlocks = $this -> oPF -> aArea[ $iPage ];
                foreach( $aBlocks as $aBlock ) {
                    foreach( $aBlock['Items'] as $aItem ) {
                        $sItemName = $aItem['Name'];

                        if( isset( $this -> aValues[$iHuman][ $sItemName ] ) ) {
                            $mValue = $this -> aValues[$iHuman][ $sItemName ];

                            switch( $aItem['Type'] ) {
                                case 'pass':
                                    $aHiddenFields[ $sItemName . '_confirm[' . $iHuman . ']' ] = $mValue;
                                case 'text':
                                case 'area':
                                case 'html_area':
                                case 'date':
                                case 'datetime':
                                case 'select_one':
                                case 'num':
                                    $aHiddenFields[ $sItemName . '[' . $iHuman . ']' ] = $mValue;
                                break;

                                case 'select_set':
                                    foreach( $mValue as $iInd => $sValue )
                                        $aHiddenFields[ $sItemName . '[' . $iHuman . '][' . $iInd . ']' ] = $sValue;
                                break;

                                case 'range':
                                    $aHiddenFields[ $sItemName . '[' . $iHuman . '][0]' ] = $mValue[0];
                                    $aHiddenFields[ $sItemName . '[' . $iHuman . '][1]' ] = $mValue[1];
                                break;

                                case 'bool':
                                    $aHiddenFields[ $sItemName . '[' . $iHuman . ']' ] = $mValue ? 'yes' : '';
                                break;

                                case 'system':
                                    switch( $aItem['Name'] ) {
                                        case 'Couple':
                                        case 'TermsOfUse':
                                            $aHiddenFields[ $sItemName ] = $mValue ? 'yes' : '';
                                        break;

                                        case 'Captcha':
                                            $aHiddenFields[ $sItemName ] = $mValue;
                                        break;

                                        case 'ProfilePhoto':
                                            $aHiddenFields['ProfilePhoto_tmp'] = $mValue;
                                        break;
                                    }
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $aHiddenFields;
    }

    function registerMember()
    {
        $oPC = new BxDolProfilesController();

        $oZ = new BxDolAlerts('profile', 'before_join', 0, 0, $this->aValues[0]);
        $oZ->alert();

        $aProfile1 = $this->oPF->getProfileFromValues($this->aValues[0]);
        if (empty($aProfile1['NickName']))
            $aProfile1['NickName'] = uriGenerate((empty($aProfile1['FirstName']) ? genRndPwd(10, false) : $aProfile1['FirstName']), 'Profiles', 'NickName');

        list($iId1, $sStatus1) = $oPC->createProfile($aProfile1);

        //--- check whether profile was created successfully or not
        if(!$iId1) {
            if(isset($aProfile1['ProfilePhoto']) && !empty($aProfile1['ProfilePhoto']))
                @unlink($GLOBALS['dir']['tmp'] . $aProfile1['ProfilePhoto']);

            return array(false, 'Fail');
        }

        //--- check for couple profile
        if($this->bCouple) {
            $aProfile2 = $this->oPF->getProfileFromValues($this -> aValues[1]);
            list($iId2, $sStatus2) = $oPC->createProfile($aProfile2, false, $iId1);

            if(!$iId2) {
                $oPC->deleteProfile($iId1);
                return array(false, 'Fail');
            }
        }

        bx_login($iId1);
        check_logged();

        //--- upload profile photo
        if(isset($aProfile1['ProfilePhoto']) && !empty($aProfile1['ProfilePhoto'])) {            

            if ('sys_avatar' == getParam('sys_member_info_thumb') && BxDolRequest::serviceExists('avatar', 'set_image_for_cropping')) {
                BxDolService::call('avatar', 'set_image_for_cropping', array ($iId1, $GLOBALS['dir']['tmp'] . $aProfile1['ProfilePhoto']));
            }
            elseif (BxDolRequest::serviceExists('photos', 'perform_photo_upload', 'Uploader')) {
                bx_import('BxDolPrivacyQuery');
                $oPrivacy = new BxDolPrivacyQuery();

                $aFileInfo = array (
                    'medTitle' => _t('_sys_member_thumb_avatar'),
                    'medDesc' => _t('_sys_member_thumb_avatar'),
                    'medTags' => _t('_ProfilePhotos'),
                    'Categories' => array(_t('_ProfilePhotos')),
                    'album' => str_replace('{nickname}', getUsername($iId1), getParam('bx_photos_profile_album_name')),
                    'albumPrivacy' => $oPrivacy->getDefaultValueModule('photos', 'album_view'),
                );
                BxDolService::call('photos', 'perform_photo_upload', array($GLOBALS['dir']['tmp'] . $aProfile1['ProfilePhoto'], $aFileInfo, false), 'Uploader');
            }
        }

        if (BxDolModule::getInstance('BxWmapModule'))
            BxDolService::call('wmap', 'response_entry_add', array('profiles', $iId1));

        //--- create system event
        bx_import('BxDolAlerts');
        $oZ = new BxDolAlerts('profile', 'join', $iId1, 0, array('status_text' => &$sStatus1));
        $oZ->alert();

        return array($iId1, $sStatus1);
    }

    function showFailPage()
    {
        return '<div class="dbContentHtml">' . _t( '_Join failed' ) . '</div>';
    }

    function showFinishPage( $iMemberId, $sStatus )
    {
        switch( $sStatus ) {
            case 'Active':      $sStatusText = ('_USER_ACTIVATION_SUCCEEDED'); break; //activated automatically
            case 'Approval':    $sStatusText = ('_USER_CONF_SUCCEEDED');       break; //automatically confirmed
            case 'Unconfirmed': $sStatusText = ('_EMAIL_CONF_SENT');           break; //conf mail succesfully sent
            case 'NotSent':     $sStatusText = ('_EMAIL_CONF_NOT_SENT');       break; //failed to send conf mail
        }

        if ('sys_avatar' == getParam('sys_member_info_thumb') && 'EXIT' == BxDolService::call('avatar', 'join', array ($iMemberId, $sStatusText))) {
            exit;
        }

        return '<div class="dbContentHtml bx-def-font-large">' . _t( '_Join complete' ) . '<br />' . _t( $sStatusText ) . '</div>';
    }
}
