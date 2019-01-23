<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplSearchResult');

class BxSitesSearchResult extends BxTemplSearchResult
{
    var $isError;
    var $_oMain;
    var $sUnitTemplate;
    var $oVotingView = null;
    var $sSitesBrowseUrl;
    var $sSitesBrowseAll;
    var $sUnitName;
    var $sMode;
    var $sThumbSize;
    var $aCurrent = array(
        'name' => 'bx_sites',
        'table' => 'bx_sites_main',
        'ownFields' => array('id', 'url', 'title', 'entryUri', 'description', 'photo', 'commentsCount',
                             'date', 'ownerid', 'categories', 'tags', 'rate'),
        'searchFields' => array('title', 'description', 'tags', 'categories'),
        'restriction' => array(
            'activeStatus' => array('value' => 'approved', 'field'=>'status', 'operator'=>'='),
            'tag' => array('value' => '', 'field' => 'tags', 'operator' => 'like'),
            'category' => array('value' => '', 'field' => 'categories', 'operator' => 'like'),
            'featured' => array('value' => '', 'field' => 'featured', 'operator' => '='),
            'public' => array('value' => '', 'field' => 'allowView', 'operator' => 'in'),
        ),
        'paginate' => array('perPage' => 10, 'page' => 1, 'totalNum' => 2, 'totalPages' => 1),
        'sorting' => 'last',
        'rss' => array(
            'title' => '',
            'link' => '',
            'image' => '',
            'profile' => 0,
            'fields' => array (
                'Link' => '',
                'Title' => 'title',
                'DateTimeUTS' => 'date',
                'Desc' => 'description',
                'Image' => 'photo',
            ),
        ),
        'ident' => 'id'
    );

    function __construct($sMode = '', $sValue = '', $sValue2 = '', $sValue3 = '')
    {
        $this->_oMain = $this->getSitesMain();
        $this->isError = false;
        $this->sUnitTemplate = 'unit';
        $this->sUnitName = 'unit';
        $this->sThumbSize = 'browse';
        $this->sMode = $sMode;

        bx_import("BxTemplVotingView");
        $oVotingView = new BxTemplVotingView('bx_sites', 0);
        $this->oVotingView = $oVotingView->isEnabled() ? $oVotingView : null;

        $this->aCurrent['title'] = _t('_bx_sites');
        $this->aCurrent['paginate']['perPage'] = getParam('bx_sites_per_page');

        switch ($sMode) {
            case 'pending':
                unset($this->aCurrent['rss']);
                break;

            case 'adminpending':
                unset($this->aCurrent['rss']);
                $this->aCurrent['restriction']['activeStatus']['value'] = 'pending';
                $this->sSitesBrowseUrl = 'administration';
                break;

            case 'my_pending':
                $this->aCurrent['restriction']['owner'] = array(
                    'value' => $this->_oMain->iOwnerId,
                    'field' => 'ownerid',
                    'operator' => '='
                );
                $this->aCurrent['restriction']['activeStatus']['value'] = 'pending';
                $this->sSitesBrowseUrl = 'browse/my';
                unset($this->aCurrent['rss']);
                break;

            case 'user':
                if ($sValue) {
                    $iProfileId = $this->_oMain->_oDb->getProfileIdByNickName($sValue);
                    if ($iProfileId) {
                        $this->aCurrent['title'] = _t('_bx_sites_caption_browse_by_user') . $sValue;
                        $GLOBALS['oTopMenu']->setCurrentProfileID($iProfileId);
                        $this->aCurrent['restriction']['owner'] = array(
                            'value' => $iProfileId,
                            'field' => 'ownerid',
                            'operator' => '='
                        );
                        $this->sSitesBrowseUrl = 'browse/user/' . $sValue;
                    } else
                        $this->isError = true;
                } else
                    $this->isError = true;
                break;

            case 'category':
                $sCategory = uri2title($sValue);
                $this->aCurrent['restriction']['category']['value'] = $sCategory;
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_by_category') . ' ' . $sCategory;
                $GLOBALS['oTopMenu']->setCustomSubHeader(_t('_bx_sites_caption_browse_by_category') . ' ' . $sCategory);
                $this->sSitesBrowseUrl = 'browse/category/' . $sValue . '/';
                break;

            case 'tag':
                $sTag = uri2title($sValue);
                $this->aCurrent['restriction']['tag']['value'] = $sTag;
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_by_tag') . ' ' . $sTag;
                $GLOBALS['oTopMenu']->setCustomSubHeader(_t('_bx_sites_caption_browse_by_tag') . ' ' . $sTag);
                $this->sSitesBrowseUrl = 'browse/all';
                break;

            case 'all':
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_all');
                $this->sSitesBrowseUrl = 'browse/all';
                break;

            case 'recent':
                $this->aCurrent['paginate']['perPage'] = 1;
                $this->aCurrent['restriction']['public']['value'] = BX_DOL_PG_ALL;
                $this->sUnitTemplate = 'block_percent';
                $this->sThumbSize = 'file';
                break;

            case 'featured':
                $this->aCurrent['restriction']['featured']['value'] = 1;
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_featured');
                $this->sSitesBrowseUrl = 'browse/featured';
                $this->sSitesBrowseAll = 'browse/featured';
                break;

            case 'featuredlast':
                $this->aCurrent['paginate']['perPage'] = 1;
                $this->aCurrent['restriction']['featured']['value'] = 1;
                $this->aCurrent['restriction']['public']['value'] = BX_DOL_PG_ALL;
                $this->sUnitTemplate = 'block_percent';
                $this->sThumbSize = 'file';
                break;

            case 'featuredshort':
                $this->aCurrent['restriction']['featured']['value'] = 1;
                $this->aCurrent['restriction']['public']['value'] = BX_DOL_PG_ALL;
                $this->sUnitTemplate = 'unit_short';
                $this->sSitesBrowseUrl = 'browse/featuredshort';
                $this->sSitesBrowseAll = 'browse/featured';
                $this->aCurrent['paginate']['perPage'] = 5;
                break;

            case 'top':
                $this->aCurrent['sorting'] = 'top';
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_top_rated');
                $this->sSitesBrowseUrl = 'browse/top';
                break;

            case 'popular':
                $this->aCurrent['sorting'] = 'popular';
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_popular');
                $this->sSitesBrowseUrl = 'browse/popular';
                break;

            case 'search':
                if ($sValue)
                    $this->aCurrent['restriction']['keyword'] = array('value' => $sValue,'field' => '','operator' => 'against');
                $this->aCurrent['title'] = _t('_bx_sites_caption_search_results') . ' ' . $sValue;
                $this->sSitesBrowseUrl = 'browse/search/' . $sValue;
                unset($this->aCurrent['rss']);
                break;

            case 'admin':

                $this->aCurrent['join'] = array(
                    'profile' => array(
                        'type' => 'left',
                        'table' => 'Profiles',
                        'mainField' => 'ownerid',
                        'onField' => 'ID',
                        'joinFields' => array('Role')
                    )
                );

                $this->aCurrent['restriction'] = array(
                    'admin' => array(
                        'value' => '3',
                        'field' => 'Role',
                        'operator' => '=',
                        'table' => 'Profiles'
                    )
                );

                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_admin');
                $this->sSitesBrowseUrl = 'browse/admin';
                break;

            case 'users':

                $this->aCurrent['join'] = array(
                    'profile' => array(
                        'type' => 'left',
                        'table' => 'Profiles',
                        'mainField' => 'ownerid',
                        'onField' => 'ID',
                        'joinFields' => array('Role')
                    )
                );

                $this->aCurrent['restriction']['role'] = array(
                    'value' => '3',
                    'field' => 'Role',
                    'operator' => '<>',
                    'table' => 'Profiles'
                );
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_profile');
                $this->sSitesBrowseUrl = 'browse/profile';
                break;

            case 'calendar':
                $this->aCurrent['restriction']['calendar-min'] = array('value' => "UNIX_TIMESTAMP('{$sValue}-{$sValue2}-{$sValue3} 00:00:00')", 'field' => 'date', 'operator' => '>=', 'no_quote_value' => true);
                $this->aCurrent['restriction']['calendar-max'] = array('value' => "UNIX_TIMESTAMP('{$sValue}-{$sValue2}-{$sValue3} 23:59:59')", 'field' => 'date', 'operator' => '<=', 'no_quote_value' => true);
                $this->sSitesBrowseUrl = "browse/calendar/{$sValue}/{$sValue2}/{$sValue3}";
                $this->aCurrent['title'] = _t('_bx_sites_caption_browse_by_day')
                    . getLocaleDate( strtotime("{$sValue}-{$sValue2}-{$sValue3}"), BX_DOL_LOCALE_DATE_SHORT);

                $GLOBALS['oTopMenu']->setCustomSubHeader($this->aCurrent['title']);
                break;

            case 'hon_rate':
                $ip = getVisitorIP();
                $aVotedItems = $oVotingView->getVotedItems($ip);
                $aList = array();
                $sPrefix = $oVotingView->_aSystem['row_prefix'] . 'id';
                foreach ($aVotedItems as $iKey => $aVal)
                    $aList[$iKey] = $aVal[$sPrefix];

                $this->aCurrent['restriction']['public']['value'] = BX_DOL_PG_ALL;
                $this->aCurrent['paginate']['perPage'] = 1;
                $this->aCurrent['sorting'] = 'rand';
                $this->aCurrent['restriction']['id'] = array(
                    'value' => $aList,
                    'field' => 'id',
                    'operator' => 'not in'
                );

                break;

            case 'hon_prev_rate':
                $this->aCurrent['join']['rateTrack'] = array(
                    'type' => 'inner',
                    'table' => 'bx_sites_rating_track',
                    'mainField' => 'id',
                    'onField' => 'sites_id',
                    'joinFields' => array('sites_ip', 'sites_date')
                );
                $this->aCurrent['paginate']['perPage'] = 1;
                $this->aCurrent['sorting'] = 'sites_date';
                $sIp = getVisitorIP();
                $this->aCurrent['restriction']['ip'] = array(
                    'value' => $sIp,
                    'field' => 'sites_ip',
                    'table' => 'bx_sites_rating_track',
                    'operator' => '='
                );
                break;

            case 'index':
                $this->sSitesBrowseUrl = 'index';
                $this->sSitesBrowseAll = 'browse/all';
                $this->aCurrent['paginate']['perPage'] = 3;
                $aVis = array(BX_DOL_PG_ALL);
                if (getLoggedId())
                    $aVis[] = BX_DOL_PG_MEMBERS;
                $this->aCurrent['restriction']['public']['value'] = $aVis;
                break;

            case 'profile':
                if ($sValue) {
                    $iProfileId = $this->_oMain->_oDb->getProfileIdByNickName(process_db_input($sValue));
                    if ($iProfileId) {
                        $this->aCurrent['restriction']['owner'] = array(
                            'value' => $iProfileId,
                            'field' => 'ownerid',
                            'operator' => '='
                        );
                        $this->sSitesBrowseUrl = 'profile/' . $sValue;
                        $this->sSitesBrowseAll = 'browse/user/' . $sValue;
                        $this->aCurrent['paginate']['perPage'] = 3;
                        $this->aCurrent['restriction']['public']['value'] = BX_DOL_PG_ALL;
                    } else
                        $this->isError = true;
                } else
                    $this->isError = true;

                break;

            case 'home':
                $this->sSitesBrowseUrl = 'browse/home';
                $this->sSitesBrowseAll = 'browse/all';
                $this->aCurrent['paginate']['perPage'] = 5;
                $this->aCurrent['restriction']['public']['value'] = BX_DOL_PG_ALL;
                break;

            case '':
                $this->sSitesBrowseUrl = 'browse/';
                $this->aCurrent['title'] = _t('_bx_sites');
                unset($this->aCurrent['rss']);
                break;

            default:
                $this->isError = true;
        }

        if (!$this->isError) {
            if (isset($this->aCurrent['rss']))
                $this->aCurrent['rss']['link'] = BX_DOL_URL_ROOT . $this->_oMain->_oConfig->getBaseUri() . $this->sSitesBrowseUrl;

            if (bx_get('rss') !== false && bx_get('rss')) {
                $this->aCurrent['ownFields'][] = 'description';
                $this->aCurrent['ownFields'][] = 'date';
                $this->aCurrent['paginate']['perPage'] = $this->_oMain->_oDb->getParam('bx_sites_max_rss_num');
            }
        }

        parent::__construct();
    }

    function displayResultBlock ($bPagination = false, $isAjax = false)
    {
        $s = parent::displayResultBlock ();
        if ($s) {
            $GLOBALS['oSysTemplate']->addDynamicLocation($this->_oMain->_oConfig->getHomePath(), $this->_oMain->_oConfig->getHomeUrl());
            $GLOBALS['oSysTemplate']->addCss(array('main.css', 'twig.css'));

            $s = $GLOBALS['oSysTemplate']->parseHtmlByName('default_padding.html', array('content' => $s));

            if ($bPagination)
                $s .= $isAjax ? $this->showPaginationAjax() : $this->showPagination();

            $s = '<div id="search_result_block_' . $this->sMode . '">' . $s . '</div>';
        }

        return $s;
    }

    function displaySearchUnit($aData)
    {
        switch ($this->sUnitName) {
            case 'unit':
                return $this->_oMain->_oTemplate->unit($aData, $this->sUnitTemplate, $this->oVotingView, $this->sThumbSize);
                break;

            case 'hon':
                return $this->_oMain->_oTemplate->blockHon($aData);
                break;
        }
    }

    function showPagination($aParams = array())
    {
        bx_import('BxDolPaginate');
        $oConfig = $this->_oMain->_oConfig;
        $sUrlStart = BX_DOL_URL_ROOT . $oConfig->getBaseUri() . $this->sSitesBrowseUrl;
        $sUrlStart .= (false === strpos($sUrlStart, '?') ? '?' : '&');
        $oPaginate = new BxDolPaginate(array(
            'page_url' => $sUrlStart . 'page={page}&per_page={per_page}',
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'on_change_page' => '',
            'on_change_per_page' => '',
        ));
        return '<div class="clear_both"></div>'.$oPaginate->getPaginate();
    }

    function showPaginationAjax()
    {
        bx_import('BxDolPaginate');
        $oConfig = $this->_oMain->_oConfig;
        $sUrlStart = BX_DOL_URL_ROOT . $oConfig->getBaseUri() . $this->sSitesBrowseUrl;
        $sUrlStart .= (false === strpos($sUrlStart, '?') ? '?' : '&');
        $sUrlStart .= 'page={page}&per_page={per_page}';
        $oPaginate = new BxDolPaginate(array(
            'page_url' => 'javascript:void(0);',
            'count' => $this->aCurrent['paginate']['totalNum'],
            'per_page' => $this->aCurrent['paginate']['perPage'],
            'page' => $this->aCurrent['paginate']['page'],
            'view_all_url' => BX_DOL_URL_ROOT . $oConfig->getBaseUri() . $this->sSitesBrowseAll,
            'info' => false,
            'view_all' => true,
            'page_links' => false,
            'on_change_page' => "getHtmlData('search_result_block_{$this->sMode}', '$sUrlStart')"
        ));
        return '<div class="clear_both"></div>'.$oPaginate->getPaginate();
    }

    function getSitesMain()
    {
        return BxDolModule::getInstance('BxSitesModule');
    }

    function getRssUnitLink (&$a)
    {
        $oMain = $this->getSitesMain();
        return BX_DOL_URL_ROOT . $oMain->_oConfig->getBaseUri() . 'view/' . $a['entryUri'];
    }

    function getRssUnitImage (&$a, $sField)
    {
        $aImage = array ('ID' => $a['author_id'], 'Avatar' => $a[$sField]);
        $aImage = BxDolService::call('photos', 'get_image', array($aImage, 'browse'), 'Search');

        return $aImage['no_image'] ? '' : $aImage['file'];
    }

    function getAlterOrder()
    {
        if ($this->aCurrent['sorting'] == 'sites_date')
            return array('order' => " ORDER BY `sites_date` DESC");
        else
            return array();
    }
}
