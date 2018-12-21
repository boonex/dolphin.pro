<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');
bx_import('BxDolPermalinks');

define('BX_PROFILE_MENU_ID', 9);

class BxDolMenu
{
    var $aTopMenu;
    var $aMenuInfo = array();
    var $oTemplConfig;
    var $sCode = '';
    //var $iDivide; //divider or top items
    var $sRequestUriFile;
    var $sSelfFile;
    var $aNotShowSubsFor = array( );
    var $oPermalinks;
    var $aCustomBreadcrumbs = array ();

    function __construct()
    {
        global $oTemplConfig;

        $this->oPermalinks = new BxDolPermalinks();

        $this->oTemplConfig = &$oTemplConfig;

        //$this->iDivide = (int)getParam( 'topmenu_items_perline' );

        if( !$this->load() )
            $this->aTopMenu = array();
    }

    function load()
    {
        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        $this->aTopMenu = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_menu_top'));

        if (null === $this->aTopMenu) {

            if (!$this->compile())
                return false;

            $this->aTopMenu = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_menu_top'));
        }

        if( !$this->aTopMenu or !is_array( $this->aTopMenu ) ) {
            echo '<b>Warning!</b> Cannot evaluate Menu Cache.';
            return false;
        }

        if (BxDolRequest::serviceExists('pageac', 'menu_items_filter')) {
            BxDolService::call('pageac', 'menu_items_filter', array('top', &$this->aTopMenu));
        }

        return true;
    }

    function setCurrentProfileID($iProfileID = 0)
    {
        $iProfileID = (int)$iProfileID;
        $sProfileUserName = getUsername($iProfileID);
        $sProfileNickName = getNickName($iProfileID);

        if($iProfileID > 0 && !empty($sProfileUserName) && !empty($sProfileNickName)) {
            $this->aMenuInfo['profileID'] = $iProfileID;
            $this->aMenuInfo['profileUsername'] = $sProfileUserName;
            $this->aMenuInfo['profileNick'] = $sProfileNickName;
            $this->aMenuInfo['profileLink'] = getProfileLink($iProfileID);
        } else {
            $this->aMenuInfo['profileID'] = 0;
            $this->aMenuInfo['profileUsername'] = '';
            $this->aMenuInfo['profileNick'] = '';
            $this->aMenuInfo['profileLink'] = '';
        }
    }

    function setCustomVar($sVar, $sVal)
    {
        $this->aMenuInfo[$sVar] = $sVal;
    }

    function unsetCustomVar($sVar, $sVal)
    {
        $this->aMenuInfo[$sVar] = $sVal;
    }

    function setCurrentProfileNickName($sNickName = '')
    {
        $sNickName = trim($sNickName);
        $iProfileID = getID($sNickName);
        $this->setCurrentProfileID($iProfileID);
    }

    function getMenuInfo()
    {
        global $p_arr;

        $aSiteUrl = parse_url( BX_DOL_URL_ROOT );
        $this->sRequestUriFile = substr($_SERVER['REQUEST_URI'], strlen($aSiteUrl['path']));
        $this->sSelfFile       = substr(bx_html_attribute($_SERVER['PHP_SELF']), strlen($aSiteUrl['path']));

        if (isMember()) {
            $this->aMenuInfo['memberID']   = (int)$_COOKIE['memberID'];
            $this->aMenuInfo['memberUsername'] = getUsername( $this->aMenuInfo['memberID'] );
            $this->aMenuInfo['memberNick'] = getNickName( $this->aMenuInfo['memberID'] );
            $this->aMenuInfo['memberLink'] = getProfileLink( $this->aMenuInfo['memberID'] );
            $this->aMenuInfo['visible']    = 'memb';
        } else {
            $this->aMenuInfo['memberID'] = 0;
            $this->aMenuInfo['memberUsername'] = '';
            $this->aMenuInfo['memberNick'] = '';
            $this->aMenuInfo['memberLink'] = '';
            $this->aMenuInfo['visible']  = 'non';
        }

        // if profile ID is not defined yet by script (using setCurrentProfileID)
        if (empty($this->aMenuInfo['profileID'])) {
            //get viewed profile ID (cherez jopu)

            $selfFile = basename( $_SERVER['PHP_SELF'] );

            if (isset($p_arr) and isset($p_arr['ID']))
                $iProfileID = (int)$p_arr['ID'];

            // known modules
            elseif ($selfFile == 'browseMedia.php')
                $iProfileID = (int)$_GET['userID'];
            elseif ($selfFile == 'viewFriends.php')
                $iProfileID = (int)$_GET['iUser'];

            // unknown modules. f*ck me! %-[
            elseif (isset($_REQUEST['iUser']))
                $iProfileID = (int)$_REQUEST['iUser'];
            elseif (isset($_REQUEST['userID']))
                $iProfileID = (int)$_REQUEST['userID'];
            elseif (isset($_REQUEST['profileID']))
                $iProfileID = (int)$_REQUEST['profileID'];
            elseif (isset($_REQUEST['ownerID']))
                $iProfileID = (int)$_REQUEST['ownerID'];
            // Have more variants? Please add them. Do not hesitate. It is ugly anyway. ;)

            // not found
            else
                $iProfileID = 0;

            $this->setCurrentProfileID($iProfileID);
        }

        // detect current menu
        $this->aMenuInfo['currentCustom'] = -1;
        $this->aMenuInfo['currentTop']    = 0;
        $this->aMenuInfo['currentTopLink']    = 0;

        $aPossibleItems = array();
        foreach( $this->aTopMenu as $iItemID => $aItem ) {

            if( $aItem['Type'] == 'top' and $this->aMenuInfo['currentTop'] and $this->aMenuInfo['currentTop'] != $iItemID )
                break;

            // if profile ID isn't defined, then profile menu submenus can't be currently selected
            if((!isset($this->aMenuInfo['profileID']) || !$this->aMenuInfo['profileID']) && BX_PROFILE_MENU_ID == $aItem['Parent'] )
                continue;

            $this->aMenuInfo['currentTopLink'] = $aItem['Link'];

            $aItemUris = explode( '|', $aItem['Link'] );
            foreach ($aItemUris as $sItemUri) {

                if( empty($this->aMenuInfo['memberID'] )) {
                    unset($this->aMenuInfo['memberID']);
                    unset($this->aMenuInfo['memberUsername']);
                    unset($this->aMenuInfo['memberNick']);
                    unset($this->aMenuInfo['memberLink']);
                }

                if( empty($this->aMenuInfo['profileID']) ) {
                    unset ($this->aMenuInfo['profileID']);
                    unset ($this->aMenuInfo['profileUsername']);
                    unset ($this->aMenuInfo['profileNick']);
                    unset ($this->aMenuInfo['profileLink']);
                }

                foreach ($this->aMenuInfo as $k => $v)
                    $sItemUri = str_replace('{'.$k.'}', $v, $sItemUri);

                $sItemUriPermalink = $this->oPermalinks->permalink($sItemUri);

                if (0 === strcasecmp($sItemUri, $this->sRequestUriFile) || 0 === strcasecmp($sItemUriPermalink, $this->sRequestUriFile) || 0 === strncasecmp(rawurldecode($this->sRequestUriFile), $sItemUri, strlen($sItemUri)) || 0 === strncasecmp($this->sRequestUriFile, $sItemUriPermalink, strlen($sItemUriPermalink))) {

                    if ((isset($aPossibleItems[$sItemUriPermalink]) && $aPossibleItems[$sItemUriPermalink]['Type'] == "custom" && $aItem['Type'] == "top") 
                        || 
                        (isset($this->aTopMenu[$aItem['Parent']]) && !$this->checkToShow($this->aTopMenu[$aItem['Parent']]))
                        ||
                        !$this->checkToShow($aItem))
                        continue;
                    $aItem['ID'] = $iItemID;
                    $aPossibleItems[$sItemUriPermalink] = $aItem;
                }
            }

        }
        $aPossibleItemsKeys = array_keys($aPossibleItems);
        if (!empty($aPossibleItemsKeys)) {
            $sMaxUri = $aPossibleItemsKeys[0];
            for($i=1; $i<count($aPossibleItemsKeys); $i++)
                if(strlen($aPossibleItemsKeys[$i]) > strlen($sMaxUri))
                    $sMaxUri = $aPossibleItemsKeys[$i];
        }

        if(count($aPossibleItems) > 0) {
            $aItem = $aPossibleItems[$sMaxUri];
            if( $aItem['Type'] == 'custom' ) {
                $this->aMenuInfo['currentCustom'] = $aItem['ID'];
                $this->aMenuInfo['currentTop']    = (int)$aItem['Parent'];
                $this->aMenuInfo['currentTopName']    = (int)$aItem['Parent'];
            } else { //top or system
                if( $this->aMenuInfo['currentTop'] and $this->aMenuInfo['currentTop'] != $aItem['ID'] ) {
                } else {
                    $this->aMenuInfo['currentTop'] = $aItem['ID'];
                }
            }
        }
                // if( $this->aMenuInfo['currentCustom'] )
                    // break;
        if(!$this->aMenuInfo['currentTop']) {
            $this->aMenuInfo['currentCustom'] = -1;
            $this->aMenuInfo['currentTop']    = -1;
            $this->aMenuInfo['currentTopLink']    = -1;
        }
    }

    // check if to show current sub menu
    function checkShowCurSub()
    {
        foreach( $this->aNotShowSubsFor as $sExcep )
            if( $this->sSelfFile == $sExcep )
                return false;
        return true;
    }

    function checkToShow( $aItem )
    {
        if( !$this->checkVisible( $aItem['Visible'] ) )
            return false;

        if( !$this->checkCond( $aItem['Check'] ) )
            return false;

        return true;
    }

    function checkVisible( $sVisible )
    {
        return ( strpos( $sVisible, $this->aMenuInfo['visible'] ) !== false );
    }

    function checkCond( $sCheck )
    {
        if( !$sCheck )
            return true;

        return eval($sCheck);
    }

    function genSubItems( $iTItemID = 0 )
    {
        if( !$iTItemID )
            $iTItemID = $this->aMenuInfo['currentTop'];

        foreach( $this->aTopMenu as $iItemID => $aItem ) {
            if( $aItem['Type'] != 'custom' )
                continue;

            if( $aItem['Parent'] != $iTItemID )
                continue;

            if( !$this->checkToShow( $aItem ) )
                continue;

            //generate
            list( $aItem['Link'] ) = explode( '|', $aItem['Link'] );

            $aItem['Link']    = $this->replaceMetas( $aItem['Link'] );
            $aItem['Onclick'] = $this->replaceMetas( $aItem['Onclick'] );

            $bActive = ( $iItemID == $this->aMenuInfo['currentCustom'] );

            $this->genSubItem( _t( $aItem['Caption'] ), $aItem['Link'], $aItem['Target'], $aItem['Onclick'], $bActive );
        }
    }

    function replaceMetas( $sLink )
    {
        $sLink = str_replace( '{memberPass}',  empty($this->aMenuInfo['memberID']) ? '' : getPassword( $this->aMenuInfo['memberID'] ),  $sLink );

        foreach ($this->aMenuInfo as $k => $v)
            $sLink = str_replace('{'.$k.'}', $v, $sLink);

        return $sLink;
    }

    function compile()
    {
        $sEval =  "return array(\n";
        $aFields = array( 'Type','Caption','Link','Visible','Target','Onclick','Check','Parent','Picture','Icon','BQuickLink', 'Statistics', 'Name' );

        $sQuery = "
            SELECT
                `ID`,
                `" . implode('`,
                `', $aFields ) . "`
            FROM `sys_menu_top`
            WHERE
                `Active` = 1 AND
                ( `Type` = 'system' OR `Type` = 'top' )
            ORDER BY `Type`,`Order`
        ";

        $rMenu = db_res( $sQuery );
        while( $aMenuItem =  $rMenu ->fetch() ) {
            $sEval .= "  " . str_pad( $aMenuItem['ID'], 2 ) . " => array(\n";

            foreach( $aFields as $sKey => $sField ) {
                $sCont = $aMenuItem[$sField];

                if( $sField == 'Link' )
                    $sCont = $this->getCurrLink($sCont);

                $sCont = str_replace( '\\', '\\\\', $sCont );
                $sCont = str_replace( '"', '\\"',   $sCont );
                $sCont = str_replace( '$', '\\$',   $sCont );

                $sCont = str_replace( "\n", '',     $sCont );
                $sCont = str_replace( "\r", '',     $sCont );
                $sCont = str_replace( "\t", '',     $sCont );

                $sEval .= "    " . str_pad( "'$sField'", 11 ) . " => \"$sCont\",\n";
            }

            $sEval .= "  ),\n";

            // write it's children
            $sQuery = "
                SELECT
                    `ID`,
                    `" . implode('`,
                    `', $aFields ) . "`
                FROM `sys_menu_top`
                WHERE
                    `Active` = 1 AND
                    `Type` = 'custom' AND
                    `Parent` = {$aMenuItem['ID']}
                ORDER BY `Order`
            ";

            $rCMenu = db_res( $sQuery );
            while( $aMenuItem =  $rCMenu ->fetch() ) {
                $sEval .= "  " . str_pad( $aMenuItem['ID'], 2 ) . " => array(\n";

                foreach( $aFields as $sKey => $sField ) {
                    $sCont = $aMenuItem[$sField];

                    if( $sField == 'Link' )
                        $sCont = $this->getCurrLink($sCont);

                    $sCont = str_replace( '\\', '\\\\', $sCont );
                    $sCont = str_replace( '"', '\\"',   $sCont );
                    $sCont = str_replace( '$', '\\$',   $sCont );

                    $sCont = str_replace( "\n", '',     $sCont );
                    $sCont = str_replace( "\r", '',     $sCont );
                    $sCont = str_replace( "\t", '',     $sCont );

                    $sEval .= "    " . str_pad( "'$sField'", 11 ) . " => \"$sCont\",\n";
                }

                $sEval .= "  ),\n";
            }
        }

        $sEval .= ");\n";
        $aResult = eval($sEval);

        // view my profile and view other member's profile must be in particular order
        $aMenuProfileView = $aResult[9];
        $aMenuMyProfile = $aResult[4];        
        unset($aResult[9]);
        unset($aResult[4]);                
        $aResult[9] = $aMenuProfileView;
        $aResult[4] = $aMenuMyProfile;

        $oCache = $GLOBALS['MySQL']->getDbCacheObject();
        return $oCache->setData ($GLOBALS['MySQL']->genDbCacheKey('sys_menu_top'), $aResult);
    }

    /**
    * Returns link in accordance with permalink settings
    */
    function getCurrLink($sCont)
    {
        $aCurrLink = explode('|', $sCont);
        $aCurrLink[0] = $this->oPermalinks->permalink($aCurrLink[0]);
        $sCont = implode( '|', $aCurrLink );

        return $sCont;
    }

    /**
     * set custom breadcrumbs
     * @param $a breadcrumbs array, array keys are titles and array values are links, for example:
     *  array(
     *      _t('Item1') => 'http://item1.com/link',
     *      _t('Item2') => 'http://item2.com/link',
     *  )
     *  NOTE: first element in breadcrumb is always 'Home', it is added automatically, so you don't need to add in this array
     */
    function setCustomBreadcrumbs ($a)
    {
        $this->aCustomBreadcrumbs = $a;
    }
}
