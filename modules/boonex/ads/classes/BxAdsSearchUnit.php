<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'header.inc.php' );
bx_import('BxTemplSearchResultText');

class BxAdsSearchUnit extends BxTemplSearchResultText
{
    var $sHomePath;
    var $sHomeUrl;

    var $sUploadDir = 'media/images/classifieds/';
    var $bShowCheckboxes;

    var $aCurrent = array(
        'name' => 'ads',
        'title' => '_bx_ads_Ads',
        'table' => 'bx_ads_main',
        'ownFields' => array('ID', 'IDProfile', 'IDClassifiedsSubs', 'DateTime', 'LifeTime', 'Subject', 'EntryUri', 'Message', 'CustomFieldValue1', 'CustomFieldValue2', 'Media', 'Tags', 'Status', 'Rate', 'RateCount', 'CommentsCount'),
        'searchFields' => array('Subject', 'Message', 'Tags', 'City'),
        'join' => array(
            'subcategory' => array(
                'type' => 'inner',
                'table' => 'bx_ads_category_subs',
                'mainField' => 'IDClassifiedsSubs',
                'onField' => 'ID',
                'joinFields' => array('NameSub', 'SEntryUri', 'ID')
            ),
            'category' => array(
                'type' => 'inner',
                'table' => 'bx_ads_category',
                'mainTable' => 'bx_ads_category_subs',
                'mainField' => 'IDClassified',
                'onField' => 'ID',
                'joinFields' => array('Name', 'CEntryUri', 'ID', 'Unit1', 'Unit2', 'CustomFieldName1', 'CustomFieldName2')
            ),
            'profiles' => array(
                'type' => 'inner',
                'table' => 'Profiles',
                'mainTable' => 'bx_ads_main',
                'mainField' => 'IDProfile',
                'onField' => 'ID',
                'joinFields' => array()
            )
        ),
        'restriction' => array(
            'activeStatus' => array('value'=>'active', 'field'=>'Status', 'operator'=>'='),
            'status' => array('value'=>'', 'field'=>'Status', 'operator'=>'='),
            'featuredStatus' => array('value'=>'', 'field'=>'Featured', 'operator'=>'='),
            'owner' => array('value'=>'', 'field'=>'IDProfile', 'operator'=>'=', 'table' => 'bx_ads_main'),
            'tag' => array('value'=>'', 'field'=>'Tags', 'operator'=>'like'),
            'categoryID'=> array('value'=>'', 'field'=>'ID', 'operator'=>'=', 'table' => 'bx_ads_category'),
            'subcategoryID'=> array('value'=>'', 'field'=>'ID', 'operator'=>'=', 'table' => 'bx_ads_category_subs'),
            'id'=> array('value'=>'', 'field'=>'ID', 'operator'=>'='),
            'country'=> array('value'=>'', 'field'=>'Country', 'operator'=>'=', 'table' => 'bx_ads_main'),
            'message_filter'=> array('value'=>'', 'field'=>'Subject', 'operator'=>'like'),
            'today'=> array('value'=>'', 'field'=>'ID', 'operator'=>'=', 'table' => 'bx_ads_category'),
            'allow_view' => array('value'=>'', 'field'=>'AllowView', 'operator'=>'in', 'table'=> 'bx_ads_main'),
        ),
        'paginate' => array('perPage' => 4, 'page' => 1, 'totalNum' => 10, 'totalPages' => 1),
        'sorting' => 'last',
        'custom_filter1' => '',
        'custom_filter2' => '',
        'second_restr' => '',
        'third_restr' => ''
    );

    var $aPermalinks;
    var $sSelectedUnit;

    function __construct()
    {
        $oMain = $this->getAdsMain();

        $this->sHomePath = $oMain->_oConfig->getHomePath();
        $this->sHomeUrl = $oMain->_oConfig->getHomeUrl();

        $this->aPermalinks = array(
            'param' => 'permalinks_module_ads',
            'enabled' => array(
                'file' => 'ads/entry/{uri}',
                'category' => 'ads/cat/{uri}',
                'subcategory' => 'ads/subcat/{uri}',
                'tag' => 'ads/tag/{uri}',
                'browseAll' => 'ads/',
                'admin_file' => 'ads/entry/{uri}',
                'admin_category' => 'ads/cat/{uri}',
                'admin_subcategory' => 'ads/subcat/{uri}',
                'admin_tag' => 'ads/tag/{uri}',
                'admin_browseAll' => 'ads/'
            ),
            'disabled' => array(
                'file' => 'classifieds.php?ShowAdvertisementID={id}',
                'category' => 'classifieds.php?bClassifiedID={id}',
                'subcategory' => 'classifieds.php?bSubClassifiedID={id}',
                'tag' => 'classifieds_tags.php?tag={uri}',
                'browseAll' => 'classifieds.php',
                'admin_file' => 'classifieds.php?ShowAdvertisementID={id}',
                'admin_category' => 'classifieds.php?bClassifiedID={id}',
                'admin_subcategory' => 'classifieds.php?bSubClassifiedID={id}',
                'admin_tag' => 'classifieds_tags.php?tag={uri}',
                'admin_browseAll' => 'classifieds.php'
            )
        );

        $this->bShowCheckboxes = false;

        parent::__construct();

        $this->sSelectedUnit = 'unit_ads';
    }

    function getAdsMain()
    {
        return BxDolModule::getInstance('BxAdsModule');
    }

       function getCurrentUrl($sType, $iId, $sUri, $aOwner = '')
       {
        if (isAdmin()) {
            $sType = 'admin_' . $sType;
        }

        $bUseFriendlyLinks = getParam('permalinks_module_ads') == 'on' ? true : false;
        $sPath = ($bUseFriendlyLinks) ? BX_DOL_URL_ROOT : $this->sHomeUrl;

           $sLink = $this->aConstants['linksTempl'][$sType];

        $sLink = str_replace('{id}', $iId, $sLink);
        $sLink = str_replace('{uri}', $sUri, $sLink);
        if (is_array($aOwner) && !empty($aOwner)) {
            $sLink = str_replace('{ownerName}', $aOwner['ownerName'], $sLink);
            $sLink = str_replace('{ownerId}', $aOwner['ownerId'], $sLink);
        }

        return $sPath . $sLink;
    }

    function getRestriction()
    {
        $sWhereSQL = parent::getRestriction();

        $oMain = $this->getAdsMain();

        if (isset($this->aCurrent['third_restr']) && $this->aCurrent['third_restr'] != '') {
            $sWhereSQL .= " AND {$this->aCurrent['third_restr']} ";
        }

        if (isset($this->aCurrent['custom_filter1']) && $this->aCurrent['custom_filter1'] != '') {
            $sWhereSQL .= " AND {$this->aCurrent['custom_filter1']} ";
        }

        if (isset($this->aCurrent['custom_filter2']) && $this->aCurrent['custom_filter2'] != '') {
            $sWhereSQL .= " AND {$this->aCurrent['custom_filter2']} ";
        }

        $bSpec = isAdmin();
        $sSign = "<";
        $sTimeCheck = " AND UNIX_TIMESTAMP() - `{$oMain->_oConfig->sSQLPostsTable}`.`LifeTime`*24*60*60 __sign__ `{$oMain->_oConfig->sSQLPostsTable}`.`DateTime`";
        switch($this->aCurrent['second_restr']) {
        case 'expired':
            $sSign = ">";
            $bSpec = FALSE;
            break;
        case 'manage':
            $sSign = "<";
            $bSpec = FALSE;
            break;
        case 'outtime':
            $sTimeCheck = "";
            break;
        }
        if (!$bSpec)
            $sWhereSQL .= str_replace('__sign__', $sSign, $sTimeCheck);
        return $sWhereSQL;
    }

    function displayResultBlock ($bWrap = true)
    {
        $sCode = null;
        $aData = $this->getSearchData();
        if (is_array($aData) && count($aData) > 0) {
            $sCode .= $this->addCustomParts();
            foreach ($aData as $iKey => $aValue) {
                $sCode .= $this->displaySearchUnit($aValue);
            }
        }

        if ($sCode) {
            if ($bWrap)
                $sCode = $GLOBALS['oSysTemplate']->parseHtmlByName('default_padding.html', array('content' => $sCode));
            $sCode .= BxDolService::call('ads', 'get_common_css', array(true));
        }

        return $sCode;
    }

    function displaySearchUnit($aResSQL)
    {
        global $oFunctions;

        $sFromC = _t('_Added by');

        $oMain = $this->getAdsMain();

        $iAdID = (int)$aResSQL['id'];

        $bPossibleToView = $oMain->oPrivacy->check('view', $iAdID, $oMain->_iVisitorID);
        if (! $bPossibleToView) return $oMain->_oTemplate->parseHtmlByName('browse_unit_private.html', array('extra_css_class' => ''));

        $iOwnerID = (int)$aResSQL['ownerId'];
        $sOwnerName = getNickname($iOwnerID);
        $sOwnerLink = getProfileLink($iOwnerID);

        $sTimeAgo = defineTimeInterval($aResSQL['date']);

        $sVotePostRating = '';
        if (!is_null($this->oRate) && $this->oRate->isEnabled())
            $sVotePostRating = $this->oRate->getJustVotingElement(0, 0, $aResSQL['Rate']);

        $iCatID = (int)$aResSQL['categoryId'];
        $iSubCatID = (int)$aResSQL['subcategoryId'];

        $sAdTitle = process_text_output($aResSQL['title']);

        $sEntryUri = process_text_output($aResSQL['uri']);
        $sAdUrl = $this->genUrlX($iAdID, $sEntryUri);

        $sCustomVal1 = process_text_output($aResSQL['CustomFieldValue1']);
        $sCustomVal2 = process_text_output($aResSQL['CustomFieldValue2']);
        $sCustomName1 = process_text_output($aResSQL['CustomFieldName1']);
        $sCustomName2 = process_text_output($aResSQL['CustomFieldName2']);
        $sUnit1 = process_text_output($aResSQL['Unit1']);
        $sUnit2 = process_text_output($aResSQL['Unit2']);
        $sCustomStyle1 = $sCustomStyle2 = '';
        $sCustomStyle1 = ($sCustomVal1!='') ? '' : 'display:none;';
        $sCustomStyle2 = ($sCustomVal2!='') ? '' : 'display:none;';

        $sCategUri = process_text_output($aResSQL['categoryUri']);
        $sSubCategUri = process_text_output($aResSQL['subcategoryUri']);
        $sCategName = process_text_output($aResSQL['categoryName']);
        $sSubCategName = process_text_output($aResSQL['subcategoryName']);

        $sCEntryUri = htmlspecialchars($aResSQL['categoryUri']);
        $sSEntryUri = htmlspecialchars($aResSQL['subcategoryUri']);
        $oMain = $this->getAdsMain();
        $sCategUrl = ($oMain->_oConfig->bUseFriendlyLinks) ? BX_DOL_URL_ROOT . 'ads/cat/'.$sCEntryUri : "{$oMain->_oConfig->sCurrBrowsedFile}?bClassifiedID={$iCatID}";
        $sSubCategUrl = ($oMain->_oConfig->bUseFriendlyLinks) ? BX_DOL_URL_ROOT . 'ads/subcat/'.$sSEntryUri : "{$oMain->_oConfig->sCurrBrowsedFile}?bSubClassifiedID={$iSubCatID}";

        $sAdCover = $oMain->getAdCover($aResSQL['media']);
        $sAdBigCover = $oMain->getAdCover($aResSQL['media'], 'big_thumb');

        $sAdminCheck = '';
        if ($this->bShowCheckboxes) {
            $sAdminCheck = <<<EOF
<div class="browseCheckbox">
    <input id="ad{$iAdID}" type="checkbox" name="ads[]" value="{$iAdID}" />
</div>
EOF;

            $sPostStatus = '<div class="ads_From">' . _t('_Status') . ': ' . process_line_output($aResSQL['Status']) . '</div>';
        }

        $aUnitReplace = array(
            'ad_cover_img' => $sAdCover,
            'ad_link' => $sAdUrl,
            'rating' => $sVotePostRating,
            'ad_title' => $sAdTitle,
            'ad_status' => $sPostStatus,
            'ad_date' => mb_strtolower($sTimeAgo),
            'from_label' => mb_strtolower($sFromC),
            'ad_owner_link' => $sOwnerLink,
            'ad_owner_name' => $sOwnerName,
            'cats' => $oMain->_oTemplate->parseHtmlByTemplateName('category', array(
                'cat_link' => $sCategUrl,
                'sub_cat_link' => $sSubCategUrl,
                'cat_name' => $sCategName,
                'sub_cat_name' => $sSubCategName,
            )),
            'admin_check' => $sAdminCheck,
            'ad_big_cover_img' => $sAdBigCover,
            'bx_if:using_c1' => array(
                'condition' => ($sCustomName1 && $sCustomVal1),
                'content' => array(
                    'cust_style1' => $sCustomStyle1, 'custom_name1' => $sCustomName1, 'custom1' => $sCustomVal1, 'unit' => $sUnit1,
                )
            ),
            'bx_if:using_c2' => array(
                'condition' => ($sCustomName2 && $sCustomVal2),
                'content' => array(
                    'cust_style2' => $sCustomStyle2, 'custom_name2' => $sCustomName2, 'custom2' => $sCustomVal2, 'unit2' => $sUnit2,
                )
            ),
            'bx_if:expired' => array(
                'condition' => (time() - $aResSQL['date']) > 24*60*60*$aResSQL['LifeTime'],
                'content' => array(1 => 1)
            )
        );
        return $oMain->_oTemplate->parseHtmlByTemplateName($this->sSelectedUnit, $aUnitReplace);
    }

    function genUrlX($iEntryId, $sEntryUri, $sType='entry', $bForce = false)
    {
        global $site;

        $oMain = $this->getAdsMain();
        if ($bForce)
            $sEntryUri = db_value("SELECT `EntryUri` FROM `{$oMain->_oConfig->sSQLPostsTable}` WHERE `ID`='{$iEntryId}' LIMIT 1");

        $sMainUrl = BX_DOL_URL_ROOT;

        if ($oMain->_oConfig->bUseFriendlyLinks) {
            $sUrl = $sMainUrl."ads/{$sType}/{$sEntryUri}";
        } else {
            $sUrl = '';
            switch ($sType) {
                case 'entry':
                    $sUrl = "{$oMain->_oConfig->sCurrBrowsedFile}?ShowAdvertisementID={$iEntryId}";
                    break;
            }
        }
        return $sUrl;
    }

    function displayMenu ()
    {
         $aDBTopMenu = $this->getTopMenu();
        $aDBBottomMenu = array();

         return array( $aDBTopMenu, $aDBBottomMenu );
    }

    function getTopMenu ($aExclude = array())
    {
        $aDBTopMenu = array();
        foreach( array('last') as $sMyMode ) {
            switch( $sMyMode ) {
                case 'last':
                    $OrderBy = '`DateTime` DESC';
                    $sModeTitle  = _t( '_Latest' );
                break;
            }

            $sLink  = bx_html_attribute($_SERVER['PHP_SELF']) . "?";
            $sLink .= "ads_mode=".$sMyMode;
            $aDBTopMenu[$sModeTitle] = array('href' => $sLink, 'dynamic' => true, 'active' => ( $sMyMode == $this->aCurrent['sorting'] ));
        }

        return $aDBTopMenu;
    }

    function getBottomMenu($sAllLinkType = 'browseAll', $iId = 0, $sUri = '', $aExclude = array(), $bPgnSim = TRUE)
    {
         $aDBBottomMenu = array();
         if ($this->aCurrent['paginate']['totalPages'] > 1) {
            $sViewAllClass = 'viewAllMembers';

            $oMain = $this->getAdsMain();
             if ($this->aCurrent['paginate']['page'] > 1) {
                $iPrevPage = $this->aCurrent['paginate']['page'] - 1;
                $aDBBottomMenu[ _t('_Back') ] = array( 'href' => bx_html_attribute($_SERVER['PHP_SELF']) . "?ads_mode={$this->aCurrent['sorting']}&amp;page=$iPrevPage{$sUserAddon}{$sFileAddon}", 'dynamic' => true, 'class' => 'backMembers', 'icon_class' => 'left' );
            } else
                $sViewAllClass = 'backMembers';

            if( $this->aCurrent['paginate']['page'] < $this->aCurrent['paginate']['totalPages'] ) {
                $iNextPage = $this->aCurrent['paginate']['page'] + 1;
                $aDBBottomMenu[ _t('_Next') ] = array( 'href' => bx_html_attribute($_SERVER['PHP_SELF']) . "?ads_mode={$this->aCurrent['sorting']}&amp;page=$iNextPage{$sUserAddon}{$sFileAddon}", 'dynamic' => true, 'class' => 'moreMembers' );
            } else
                $sViewAllClass = 'moreMembers';

            if (isset($this->aConstants['linksTempl'][$sAllLinkType]))
                $sAllUrl = $this->getCurrentUrl($sAllLinkType, $sId, $sUri);
            else
                $sAllUrl = $this->getCurrentUrl('browseAll', 0, '');

            $aDBBottomMenu[ _t('_View All').' ('. $this->aCurrent['paginate']['totalNum'] .')' ] = array( 'href' => "$sAllUrl", 'class' => $sViewAllClass );
        }
        return $aDBBottomMenu;
    }

    function setSorting ()
    {
        $this->aCurrent['sorting'] = (false !== bx_get('ads_mode')) ? bx_get('ads_mode') : $this->aCurrent['sorting'];

        if( $this->aCurrent['sorting'] != 'top' && $this->aCurrent['sorting'] != 'last' && $this->aCurrent['sorting'] != 'score' && $this->aCurrent['sorting'] != 'popular')
            $this->aCurrent['sorting'] = 'last';
    }

    function getAlterOrder()
    {
        if ($this->aCurrent['sorting'] == 'popular') {
            $aSql = array();
            $oMain = $this->getAdsMain();
            $aSql['order'] = " ORDER BY `{$oMain->_oConfig->sSQLPostsTable}`.`Views` DESC, `DateTime` DESC";
            return $aSql;
        }
        return array();
    }

    function showPagination($aParams = array())
    {
        $aPgnParams = array(
            'page_url' => $this->aCurrent['paginate']['page_url'],
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
        );

        if (mb_strlen($aPgnParams['page_url']) == 0) {
            $sPageLink = $this->getCurrentUrl('browseAll', 0, '');
            $aLinkAddon = $this->getLinkAddByPrams();
            if ($aLinkAddon) {
               foreach($aLinkAddon as $sValue)
                    $sPageLink .= $sValue;
            }
            if(!$this->id)
                $this->id = 0;
            $sLoadDynamicUrl = $this->id . ', \'' . BX_DOL_URL_ROOT . 'searchKeywordContent.php?searchMode=ajax&section[]=bx_ads' . $aLinkAddon['params'];
            $sKeyword = bx_get('keyword');
            if ($sKeyword !== false && mb_strlen($sKeyword) > 0)
                $sLoadDynamicUrl .= '&keyword=' . rawurlencode(strip_tags($sKeyword));

            $aPgnParams['page_url'] = $sPageLink;
            $aPgnParams['on_change_page'] = 'return !loadDynamicBlock(' . $sLoadDynamicUrl . $aLinkAddon['paginate'].'\');';
            $aPgnParams['on_change_per_page'] = 'return !loadDynamicBlock(' . $sLoadDynamicUrl .'&page=1&per_page=\' + this.value);';
        }

        $oPaginate = new BxDolPaginate($aPgnParams);
        $sPaginate = '<div class="clear_both"></div>'.$oPaginate->getPaginate();
        return $sPaginate;
    }

    function showPagination2($bAdmin = false)
    {
        $aLinkAddon = $this->getLinkAddByPrams();

        $sAllUrl = $this->getCurrentUrl('browseAll', 0, '');

        $oPaginate = new BxDolPaginate(array(
            'page_url' => $this->aCurrent['paginate']['page_url'],
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'on_change_page' => 'return !loadDynamicBlock({id}, \''.bx_html_attribute($_SERVER['PHP_SELF']).'?ads_mode='.$this->aCurrent['sorting'].$aLinkAddon['params'].'&page={page}&per_page={per_page}\');',
        ));

        $sPaginate = '<div class="clear_both"></div>'.$oPaginate->getSimplePaginate($sAllUrl);

        return $sPaginate;
    }

    function _getPseud ()
    {
        return array(
            'id' => 'ID',
            'title' => 'Subject',
            'date' => 'DateTime',
            'uri' => 'EntryUri',
            'ownerId' => 'IDProfile',
            'bodyText' => 'Message',
            'tag' => 'Tags',
            'media' => 'Media',
            'subcategoryId' => 'ID',
            'subcategoryName' => 'NameSub',
            'subcategoryUri' => 'SEntryUri',
            'categoryId' => 'ID',
            'categoryName' => 'Name',
            'categoryUri' => 'CEntryUri'
        );
    }

}
