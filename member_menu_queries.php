<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . " GMT"); // always modified
header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
header('Pragma: no-cache'); // HTTP/1.0
header('Content-Type: text/html; charset=utf-8');

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );

bx_import('BxTemplMemberMenu');
bx_import('BxDolPageView');

$oMemberMenu = new BxTemplMemberMenu();

// return member's extra menu sub block ;
if ( isset($_GET['action']) ) {

    // read data from cache file ;
    $oCache = $oMemberMenu->getCacheObject();
    $aMenuStructure = $oCache
        ->getData($GLOBALS['MySQL']->genDbCacheKey($oMemberMenu -> sMenuCacheFile));

    // if cache file defined;
    if ($aMenuStructure) {

        $iMemberId   = getLoggedId();
        $iMenuId 	 = ( isset($_GET['menu_id']) )
            ? (int) $_GET['menu_id']
            : 0 ;

        $sOutputHtml = null;
        switch( $_GET['action'] ) {

            case 'get_menu_content' :
                if ($iMemberId && $iMenuId) {

                    // define the menu's sub menu code ;
                    $sSubMenuCode = null;
                    $aLinkedItems = array();
                    foreach($aMenuStructure as $sKey => $aItems) {
						if(!isset($aMenuStructure[$sKey][$iMenuId]) ) 
							continue;

						$sSubMenuCode = $aMenuStructure[$sKey][$iMenuId]['PopupMenu'];
						if($aMenuStructure[$sKey][$iMenuId]['linked_items'])
							$aLinkedItems = $aMenuStructure[$sKey][$iMenuId]['linked_items'];
						break;
                    }

                    if ($sSubMenuCode) {
                        header("Content-Type: text/html; charset=utf-8");
                        $sOutputHtml = $oMemberMenu -> getSubMenuContent ($iMemberId, $sSubMenuCode, $aLinkedItems);
                    }
                }
            break;

            case 'get_bubbles_values' :
                $sBubbles = ( isset($_GET['bubbles']) ) ?  $_GET['bubbles'] : null;
                if ( $sBubbles && $iMemberId ) {

                    $aMemberInfo  = getProfileInfo($iMemberId);
                    if($aMemberInfo['UserStatus'] != 'offline') {
                        // update the date of last navigate;
                        update_date_lastnav($iMemberId);
                    }

                    $aBubbles = array();
                    $aBubblesItems = explode(',', $sBubbles);

                    if ( $aBubblesItems && is_array($aBubblesItems) ) {
                        $bClearCache = false;
                        foreach( $aBubblesItems as $sValue) {
                            $aItem   = explode(':', $sValue);

                            $sBubbleCode = null;
                            foreach($aMenuStructure as $sKey => $aItems) {
                                foreach($aItems as $iKey => $aSubItems) {
                                    if( $aSubItems['Name'] == $aItem[0]) {
                                        $sBubbleCode = $aSubItems['Bubble'];
                                        break;
                                    }
                                }

                                if ($sBubbleCode) {
                                    break;
                                }
                            }

                            if ($sBubbleCode) {
                                $sCode  = str_replace('{iOldCount}', (int)$aItem[1], $sBubbleCode);
                                $sCode  = str_replace('{ID}', (int)$iMemberId, $sCode);

                                eval($sCode);
                                $aBubbles[$aItem[0]] = array (
                                    'count'     => $aRetEval['count'],
                                    'messages'  => $aRetEval['messages'],
                                    'onlclick_script'  => ( isset($aRetEval['onlclick_script'])
                                        && $aRetEval['onlclick_script']) ? $aRetEval['onlclick_script'] : '',
                                );

                                if($aItem[1] != $aRetEval['count']) {
                                    $bClearCache = true;
                                }
                            }
                        }

                        //clear cache
                        if($bClearCache) {
                            $oMemberMenu -> deleteMemberMenuKeyFile($iMemberId);
                        }

                        header('Content-Type: text/plain; charset=utf-8');
                        $sOutputHtml = json_encode($aBubbles);
                    }
                }
            break;
        }

        exit($sOutputHtml);
    }
}
