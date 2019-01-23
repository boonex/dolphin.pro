<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigSearchResult');

class BxStoreSearchResult extends BxDolTwigSearchResult
{
    var $aCurrent = array(
        'name' => 'bx_store',
        'title' => '_bx_store_page_title_browse',
        'table' => 'bx_store_products',
        'ownFields' => array('id', 'title', 'uri', 'created', 'author_id', 'thumb', 'price_range', 'rate', 'desc'),
        'searchFields' => array('title', 'desc', 'tags', 'categories'),
        'join' => array(
            'profile' => array(
                    'type' => 'left',
                    'table' => 'Profiles',
                    'mainField' => 'author_id',
                    'onField' => 'ID',
                    'joinFields' => array('NickName'),
            ),
        ),
        'restriction' => array(
            'activeStatus' => array('value' => 'approved', 'field'=>'status', 'operator'=>'='),
            'owner' => array('value' => '', 'field' => 'author_id', 'operator' => '='),
            'tag' => array('value' => '', 'field' => 'tags', 'operator' => 'against'),
            'category' => array('value' => '', 'field' => 'Category', 'operator' => '=', 'table' => 'sys_categories'),
            'category_type' => array('value' => '', 'field' => 'Type', 'operator' => '=', 'table' => 'sys_categories'),
            'public' => array('value' => '', 'field' => 'allow_view_product_to', 'operator' => '='),
        ),
        'paginate' => array('perPage' => 14, 'page' => 1, 'totalNum' => 0, 'totalPages' => 1),
        'sorting' => 'last',
        'rss' => array(
            'title' => '',
            'link' => '',
            'image' => '',
            'profile' => 0,
            'fields' => array (
                'Link' => '',
                'Title' => 'title',
                'DateTimeUTS' => 'created',
                'Desc' => 'desc',
                'Image' => 'thumb',
            ),
        ),
        'ident' => 'id'
    );

    function __construct($sMode = '', $sValue = '', $sValue2 = '', $sValue3 = '')
    {
        switch ($sMode) {

            case 'pending':
                if (false !== bx_get('bx_store_filter'))
                    $this->aCurrent['restriction']['keyword'] = array('value' => process_db_input(bx_get('bx_store_filter'), BX_TAGS_STRIP), 'field' => '','operator' => 'against');
                $this->aCurrent['restriction']['activeStatus']['value'] = 'pending';
                $this->sBrowseUrl = "administration";
                $this->aCurrent['title'] = _t('_bx_store_page_title_pending_approval');
                unset($this->aCurrent['rss']);
            break;

            case 'my_pending':
                $oMain = $this->getMain();
                $this->aCurrent['restriction']['owner']['value'] = $oMain->_iProfileId;
                $this->aCurrent['restriction']['activeStatus']['value'] = 'pending';
                $this->sBrowseUrl = "browse/user/" . getNickName($oMain->_iProfileId);
                $this->aCurrent['title'] = _t('_bx_store_page_title_pending_approval');
                unset($this->aCurrent['rss']);
            break;

            case 'search':
                if ($sValue)
                    $this->aCurrent['restriction']['keyword'] = array('value' => $sValue,'field' => '','operator' => 'against');

                if ($sValue2) {

                    $this->aCurrent['join']['category'] = array(
                        'type' => 'inner',
                        'table' => 'sys_categories',
                        'mainField' => 'id',
                        'onField' => 'ID',
                        'joinFields' => '',
                    );

                    $this->aCurrent['restriction']['category_type']['value'] = $this->aCurrent['name'];
                    $this->aCurrent['restriction']['category']['value'] = $sValue2;
                    if (is_array($sValue2)) {
                        $this->aCurrent['restriction']['category']['operator'] = 'in';
                    }
                }
                $sValue = $GLOBALS['MySQL']->unescape($sValue);
                $sValue2 = $GLOBALS['MySQL']->unescape($sValue2);
                $this->sBrowseUrl = "search/$sValue/" . (is_array($sValue2) ? implode(',',$sValue2) : $sValue2);
                $this->aCurrent['title'] = _t('_bx_store_page_title_search_results') . ' ' . (is_array($sValue2) ? implode(', ',$sValue2) : $sValue2) . ' ' . $sValue;
                unset($this->aCurrent['rss']);
                break;

            case 'user':
                $iProfileId = $GLOBALS['oBxStoreModule']->_oDb->getProfileIdByNickName ($sValue, false);
                $GLOBALS['oTopMenu']->setCurrentProfileID($iProfileId); // select profile subtab, instead of module tab
                if (!$iProfileId)
                    $this->isError = true;
                else
                    $this->aCurrent['restriction']['owner']['value'] = $iProfileId;
                $sValue = $GLOBALS['MySQL']->unescape($sValue);
                $this->sBrowseUrl = "browse/user/$sValue";

                $iProfileId = getID($sValue);
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_by_author', $iProfileId ? getNickName($iProfileId) : $sValue);

                if (bx_get('rss')) {
                    $aData = getProfileInfo($iProfileId);
                    if ($aData['Avatar']) {
                        $a = array ('ID' => $aData['author_id'], 'Avatar' => $aData['thumb']);
                        $aImage = BxDolService::call('photos', 'get_image', array($a, 'browse'), 'Search');
                        if (!$aImage['no_image'])
                            $this->aCurrent['rss']['image'] = $aImage['file'];
                    }
                }
                break;

            case 'admin':
                $this->aCurrent['restriction']['owner']['value'] = 0;
                $this->sBrowseUrl = "browse/admin";
                $this->aCurrent['title'] = _t('_bx_store_page_title_admin_products');
                break;

            case 'category':
                $this->aCurrent['join']['category'] = array(
                    'type' => 'inner',
                    'table' => 'sys_categories',
                    'mainField' => 'id',
                    'onField' => 'ID',
                    'joinFields' => '',
                );
                $this->aCurrent['restriction']['category_type']['value'] = $this->aCurrent['name'];
                $this->aCurrent['restriction']['category']['value'] = $sValue;
                $sValue = $GLOBALS['MySQL']->unescape($sValue);
                $this->sBrowseUrl = "browse/category/" .  title2uri($sValue);
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_by_category', $sValue);
                break;

            case 'tag':
                $this->aCurrent['restriction']['tag']['value'] = $sValue;
                $sValue = $GLOBALS['MySQL']->unescape($sValue);
                $this->sBrowseUrl = "browse/tag/" . title2uri($sValue);
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_by_tag', $sValue);
                break;

            case 'free':
                $this->aCurrent['restriction']['price'] = array('value' => 'Free', 'field' => 'price_range', 'operator' => '=');
                $this->sBrowseUrl = "browse/free";
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_free_products') . ' ' . $sValue;
                break;

            case 'recent':
                $this->sBrowseUrl = 'browse/recent';
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_recent');
                break;

            case 'top':
                $this->sBrowseUrl = 'browse/top';
                $this->aCurrent['sorting'] = 'top';
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_top_rated');
                break;

            case 'popular':
                $this->sBrowseUrl = 'browse/popular';
                $this->aCurrent['sorting'] = 'popular';
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_popular');
                break;

            case 'featured':
                $this->aCurrent['restriction']['featured'] = array('value' => 1, 'field' => 'featured', 'operator' => '=');
                $this->sBrowseUrl = 'browse/featured';
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_featured');
                break;

            case 'calendar':
                $this->aCurrent['restriction']['calendar-min'] = array('value' => "UNIX_TIMESTAMP('{$sValue}-{$sValue2}-{$sValue3} 00:00:00')", 'field' => 'created', 'operator' => '>=', 'no_quote_value' => true);
                $this->aCurrent['restriction']['calendar-max'] = array('value' => "UNIX_TIMESTAMP('{$sValue}-{$sValue2}-{$sValue3} 23:59:59')", 'field' => 'created', 'operator' => '<=', 'no_quote_value' => true);
                $this->sEventsBrowseUrl = "browse/calendar/{$sValue}/{$sValue2}/{$sValue3}";
                $this->aCurrent['title'] = _t('_bx_store_page_title_browse_by_day', getLocaleDate(strtotime("{$sValue}-{$sValue2}-{$sValue3}"), BX_DOL_LOCALE_DATE_SHORT));
                break;

            case '':
                $this->sBrowseUrl = 'browse/';
                $this->aCurrent['title'] = _t('_bx_store');
                unset($this->aCurrent['rss']);
                break;

            default:
                $this->isError = true;
        }

        $oMain = $this->getMain();

        $this->aCurrent['paginate']['perPage'] = $oMain->_oDb->getParam('bx_store_perpage_browse');

        if (isset($this->aCurrent['rss']))
            $this->aCurrent['rss']['link'] = BX_DOL_URL_ROOT . $oMain->_oConfig->getBaseUri() . $this->sBrowseUrl;

        if (bx_get('rss')) {
            $this->aCurrent['ownFields'][] = 'desc';
            $this->aCurrent['ownFields'][] = 'created';
            $this->aCurrent['paginate']['perPage'] = $oMain->_oDb->getParam('bx_store_max_rss_num');
        }

        bx_store_import('Voting', $this->getModuleArray());
        $oVotingView = new BxStoreVoting ('bx_store', 0);
        $this->oVotingView = $oVotingView->isEnabled() ? $oVotingView : null;

        $this->sFilterName = 'bx_store_filter';

        parent::__construct();
    }

    function getAlterOrder()
    {
        if ($this->aCurrent['sorting'] == 'last') {
            $aSql = array();
            $aSql['order'] = " ORDER BY `bx_store_products`.`created` DESC";
            return $aSql;
        } elseif ($this->aCurrent['sorting'] == 'top') {
            $aSql = array();
            $aSql['order'] = " ORDER BY `bx_store_products`.`rate` DESC, `bx_store_products`.`rate_count` DESC";
            return $aSql;
        } elseif ($this->aCurrent['sorting'] == 'popular') {
            $aSql = array();
            $aSql['order'] = " ORDER BY `bx_store_products`.`views` DESC";
            return $aSql;
        }
        return array();
    }

    function displayResultBlock ()
    {
        global $oFunctions;
        $s = parent::displayResultBlock ();
        if($s) {
            $s = $oFunctions->centerContent ($s, '.bx_store_unit');

            $oMain = $this->getMain();
            $GLOBALS['oSysTemplate']->addDynamicLocation($oMain->_oConfig->getHomePath(), $oMain->_oConfig->getHomeUrl());
            $GLOBALS['oSysTemplate']->addCss(array('unit.css', 'twig.css'));
            return $GLOBALS['oSysTemplate']->parseHtmlByName('default_padding.html', array('content' => $s));
        }
        return '';
    }

    function getModuleArray()
    {
        return db_arr ("SELECT * FROM `sys_modules` WHERE `title` = 'Store' AND `class_prefix` = 'BxStore' LIMIT 1");
    }

    function getMain()
    {
        if ($GLOBALS['oBxStoreModule']) {
           return $GLOBALS['oBxStoreModule'];
        } else {
            $aModule = BxStoreSearchResult::getModuleArray();
            bx_import ('Module', $aModule);
            bx_import ('Template', $aModule);
            bx_import ('Config', $aModule);
            $GLOBALS['oBxStoreModule'] = new BxStoreModule($aModule);
            return $GLOBALS['oBxStoreModule'];
        }
    }

    function getRssUnitLink (&$a)
    {
        $oMain = $this->getMain();
        return BX_DOL_URL_ROOT . $oMain->_oConfig->getBaseUri() . 'view/' . $a['uri'];
    }

    function _getPseud ()
    {
        return array(
            'id' => 'id',
            'title' => 'title',
            'uri' => 'uri',
            'created' => 'created',
            'author_id' => 'author_id',
            'NickName' => 'NickName',
            'thumb' => 'thumb',
            'price_range' => 'price_range',
        );
    }
}
