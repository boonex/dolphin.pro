<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( 'inc/header.inc.php' );
require_once( BX_DIRECTORY_PATH_INC . 'design.inc.php' );
require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolRssFactory.php' );

function actionRSS()
{
    $sType = process_db_input($_REQUEST['action'], BX_TAGS_STRIP);
    $iLength = (int)$_REQUEST['length'];

    if(strncmp($sType, 'sys_', 4) === 0) {
        $aRssTitle = '';
        $aRssData = array();

        switch($sType) {
            case 'sys_stats':
                $aRssTitle = getParam('site_title');

                $oCache = $GLOBALS['MySQL']->getDbCacheObject();
                $aStats = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_stat_site'));
                if (null === $aStats) {
                    genSiteStatCache();
                    $aStats = $oCache->getData($GLOBALS['MySQL']->genDbCacheKey('sys_stat_site'));
                }

                if ($aStats && is_array($aStats)) {
                    foreach ($aStats as $sKey => $aStat) {
                        $iNum = strlen($aStat['query']) > 0 ? db_value($aStat['query']) : 0;

                        $aRssData[] = array(
                           'UnitID' => $sKey,
                           'OwnerID' => '',
                           'UnitTitle' => $iNum . ' ' . _t('_' . $aStat['capt']),
                           'UnitLink' => strlen($aStat['link']) > 0 ? BX_DOL_URL_ROOT . $aStat['link'] : '',
                           'UnitDesc' => '',
                           'UnitDateTimeUTS' => 0,
                           'UnitIcon' => ''
                        );
                    }
                }
                break;

            case 'sys_members':
                $aRssTitle = getParam('site_title');

                $iLength = $iLength != 0 ? $iLength : 33;
                $aMembers = $GLOBALS['MySQL']->getAll("SELECT *, UNIX_TIMESTAMP(`DateReg`) AS `DateRegUTS` FROM `Profiles` WHERE 1 AND (`Couple`='0' OR `Couple`>`ID`) AND `Status`='Active' ORDER BY `DateReg` DESC LIMIT " . $iLength);
                foreach($aMembers as $aMember) {
                    $aRssData[] = array(
                       'UnitID' => '',
                       'OwnerID' => '',
                       'UnitTitle' => $aMember['NickName'],
                       'UnitLink' => getProfileLink($aMember['ID']),
                       'UnitDesc' => $GLOBALS['oFunctions']->getMemberAvatar($aMember['ID']),
                       'UnitDateTimeUTS' => $aMember['DateRegUTS'],
                       'UnitIcon' => ''
                    );
                }
                break;

            case 'sys_news':
                echo BxDolService::call('news', 'news_rss', array($iLength));
                return;
        }

        $oRss = new BxDolRssFactory();
        echo $oRss->GenRssByData($aRssData, $aRssTitle, '');
    } else
        BxDolService::call($sType, $sType . '_rss', array());
}

actionRSS();
