<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . "membership_levels.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "match.inc.php");
require_once(BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseSearchResultText.php');

class BxBaseSearchProfile extends BxBaseSearchResultText
{
    var $aCurrent = array(
        'name' => 'profile',
        'title' => '_People',
        'table' => 'Profiles',
        'ownFields' => array('ID', 'NickName', 'DescriptionMe', 'Country', 'City', 'Tags', 'DateReg', 'DateOfBirth', 'Sex', 'Couple'),
        'searchFields' => array('NickName', 'FullName', 'FirstName', 'LastName', 'DescriptionMe', 'City', 'Tags'),
        'restriction' => array(
            'activeStatus' => array('value'=>'Active', 'field'=>'Status', 'operator'=>'='),
        ),
        'paginate' => array('perPage' => 10, 'page' => 1, 'totalNum' => 10, 'totalPages' => 1),
        'sorting' => 'last'
    );

    var $aPermalinks = array(
        'param' => 'enable_modrewrite',
        'enabled' => array(
            'file' => '{uri}',
            'browseAll' => 'browse.php'
        ),
        'disabled' => array(
            'file' => 'profile.php?ID={id}',
            'browseAll' => 'browse.php'
        )
    );

    function __construct ($sParamName = '', $sParamValue = '', $sParamValue1 = '', $sParamValue2 = '')
    {
        parent::__construct();
        $this->iRate = 0;

        switch ($sParamName) {
            case 'calendar':
                $GLOBALS ['_page']['header'] =  _t('_sys_profiles_caption_browse_by_day')
                    . ': ' . getLocaleDate( strtotime("{$sParamValue}-{$sParamValue1}-{$sParamValue2}")
                        , BX_DOL_LOCALE_DATE_SHORT);

                $sParamValue = (int)$sParamValue;
                $sParamValue1 = (int)$sParamValue1;
                $sParamValue2 = (int)$sParamValue2;
                $this->aCurrent['restriction']['calendar-min'] = array('value' => "'{$sParamValue}-{$sParamValue1}-{$sParamValue2} 00:00:00'", 'field' => 'DateReg', 'operator' => '>=', 'no_quote_value' => true);
                $this->aCurrent['restriction']['calendar-max'] = array('value' => "'{$sParamValue}-{$sParamValue1}-{$sParamValue2} 23:59:59'", 'field' => 'DateReg', 'operator' => '<=', 'no_quote_value' => true);
                $this->aCurrent['title'] = $GLOBALS ['_page']['header'];
                break;
        }
    }

    function displaySearchUnit($aData, $aExtendedCss = array())
    {
        $sCode = '';
        $sOutputMode = (isset ($_GET['search_result_mode']) && $_GET['search_result_mode']=='ext') ? 'ext' : 'sim';

        $sTemplateName = ($sOutputMode == 'ext') ? 'search_profiles_ext.html' : 'search_profiles_sim.html';

        if ($sTemplateName) {
            if ($aData['Couple'] > 0) {
                $aProfileInfoC = getProfileInfo( $aData['Couple'] );
                $sCode .= $this->PrintSearhResult( $aData, $aProfileInfoC, $aExtendedCss, $sTemplateName );
            } else {
                $sCode .= $this->PrintSearhResult( $aData, array(), $aExtendedCss, $sTemplateName );
            }
        }
        return $sCode;
    }

    /**
     * @description : function will generate profile block (used the profile template );
     * @return : Html presentation data ;
    */
    function PrintSearhResult($aProfileInfo, $aCoupleInfo = '', $aExtendedKey = null, $sTemplateName = '', $oCustomTemplate = null)
    {
        global $site;
        global $aPreValues;

        $iVisitorID = getLoggedId();
        $bExtMode = (!empty($_GET['mode']) && $_GET['mode'] == 'extended') || (!empty($_GET['search_result_mode']) && $_GET['search_result_mode'] == 'ext');
        $isShowMatchPercent = $bExtMode && $iVisitorID && ( $iVisitorID != $aProfileInfo['ID'] ) && getParam('view_match_percent') && getParam('enable_match');

        $bPublic = $bExtMode ? bx_check_profile_visibility ($aProfileInfo['ID'], $iVisitorID, true) : true;
        if ($bPublic && $iVisitorID != $aProfileInfo['ID'] && !isAdmin()) {
            $oPrivacy = new BxDolPrivacy('sys_page_compose_privacy', 'id', 'user_id');

            $iBlockID = $GLOBALS['MySQL']->getOne("SELECT `ID` FROM `sys_page_compose` WHERE `Page` = 'profile' AND `Func` = 'Description' AND `Column` != 0");
            $iPrivacyId = (int)$GLOBALS['MySQL']->getOne("SELECT `id` FROM `sys_page_compose_privacy` WHERE `user_id`='{$aProfileInfo['ID']}' AND `block_id`='{$iBlockID}' LIMIT 1");
            $bPublic = !$iBlockID || !$iPrivacyId || $oPrivacy->check('view_block', $iPrivacyId, $iVisitorID);
        }

        $sProfileThumb = get_member_thumbnail( $aProfileInfo['ID'], 'none', ! $bExtMode, 'visitor' );
        $sProfileMatch = $isShowMatchPercent ? $GLOBALS['oFunctions']->getProfileMatch( $iVisitorID, $aProfileInfo['ID'] ) : '';

        $sProfileNickname = '<a href="' . getProfileLink($aProfileInfo['ID']) . '">' . getNickName($aProfileInfo['ID']) . '</a>';
        $sProfileInfo = $GLOBALS['oFunctions']->getUserInfo($aProfileInfo['ID']);
        $sProfileDesc = $bPublic ? strmaxtextlen($aProfileInfo['DescriptionMe'], 130) : _t('_sys_profile_private_text_title');
        $sProfileZodiac = ($bPublic && $bExtMode && getParam('zodiac')) ? $GLOBALS['oFunctions']->getProfileZodiac($aProfileInfo['DateOfBirth']) : '';

        $sProfile2ASc1 = $sProfile2ASc2 = $sProfile2Nick = $sProfile2Desc = $sProfile2Info = $sProfile2Zodiac = '';
        if ($aCoupleInfo) {

            $sProfile2Nick = '<a href="' . getProfileLink( $aCoupleInfo['ID'] ) . '">' . getNickName($aCoupleInfo['ID']) . '</a>';
            $sProfile2Info = $GLOBALS['oFunctions']->getUserInfo($aCoupleInfo['ID']);
            $sProfile2Desc = $bPublic ? strmaxtextlen($aCoupleInfo['DescriptionMe'], 130) : _t('_sys_profile_private_text_title');
            $sProfile2Zodiac = ($bPublic && $bExtMode && getParam('zodiac')) ? $GLOBALS['oFunctions']->getProfileZodiac($aCoupleInfo['DateOfBirth']) : '';

            $sProfile2ASc1 = 'float:left;width:31%;margin-right:10px;';
            $sProfile2ASc2 = 'float:left;width:31%;display:block;';

        } else {
            $sProfile2ASc2 = 'display:none;';
        }

        $aKeys = array(
            'thumbnail' => $sProfileThumb,
            'match' => $sProfileMatch,

            'nick' => $sProfileNickname,
            'info' => $sProfileInfo,
            'i_am_desc' => $sProfileDesc,
            'zodiac_sign' => $sProfileZodiac,

            'nick2' => $sProfile2Nick,
            'info2' => $sProfile2Info,
            'i_am_desc2' => $sProfile2Desc,
            'zodiac_sign2' => $sProfile2Zodiac,

            'add_style_c1' => $sProfile2ASc1,
            'add_style_c2' => $sProfile2ASc2,
        );

        if ( $aExtendedKey and is_array($aExtendedKey) and !empty($aExtendedKey) ) {
            foreach($aExtendedKey as $sKey => $sValue )
                $aKeys[$sKey] = $sValue;
        } else {
            $aKeys['ext_css_class'] = '';
        }

        return ($oCustomTemplate) ? $oCustomTemplate->parseHtmlByName($sTemplateName, $aKeys) : $GLOBALS['oSysTemplate']->parseHtmlByName($sTemplateName, $aKeys);
    }

    function displaySearchBox ($sCode, $sPaginate = '', $bAdminBox = false)
    {
        $sCode = $GLOBALS['oFunctions']->centerContent($sCode, '.searchrow_block_simple');
        $sClearBoth = '<div class="clear_both"></div>';
        $sCode = DesignBoxContent(_t($this->aCurrent['title']), '<div class="searchContentBlock">'.$sCode.$sClearBoth.'</div>'. $sPaginate, 1);
        if (!isset($_GET['searchMode']))
           $sCode = '<div id="page_block_'.$this->id.'">'.$sCode.$sClearBoth.'</div>';
        return $sCode;
    }

    function _getPseud ()
    {
        return array(
            'date' => 'DateReg'
        );
    }

    function getRestriction ()
    {
        $sWhere = parent::getRestriction ();
        $sWhere .= " AND (`Profiles`.`Couple` = 0 OR `Profiles`.`Couple` > `Profiles`.`ID`) ";
        return $sWhere;
    }
}
