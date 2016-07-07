<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPageView');

class BxStorePageMy extends BxDolPageView
{
    var $_oMain;
    var $_oTemplate;
    var $_oDb;
    var $_oConfig;
    var $_aProfile;

    function __construct(&$oMain, &$aProfile)
    {
        $this->_oMain = &$oMain;
        $this->_oTemplate = $oMain->_oTemplate;
        $this->_oDb = $oMain->_oDb;
        $this->_oConfig = $oMain->_oConfig;
        $this->_aProfile = $aProfile;
        parent::__construct('bx_store_my');
    }

    function getBlockCode_Owner()
    {
        if (!$this->_oMain->_iProfileId || !$this->_aProfile)
            return '';

        $sContent = '';
        switch (bx_get('bx_store_filter')) {
        case 'add_product':
            $sContent = $this->getBlockCode_Add ();
            break;
        case 'manage_products':
            $sContent = $this->getBlockCode_My ();
            break;
        case 'pending_products':
            $sContent = $this->getBlockCode_Pending ();
            break;
        default:
            $sContent = $this->getBlockCode_Main ();
        }

        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "browse/my";
        $aMenu = array(
            _t('_bx_store_block_submenu_main') => array('href' => $sBaseUrl, 'active' => !bx_get('bx_store_filter')),
            _t('_bx_store_block_submenu_add_product') => array('href' => $sBaseUrl . '&bx_store_filter=add_product', 'active' => 'add_product' == bx_get('bx_store_filter')),
            _t('_bx_store_block_submenu_manage_products') => array('href' => $sBaseUrl . '&bx_store_filter=manage_products', 'active' => 'manage_products' == bx_get('bx_store_filter')),
            _t('_bx_store_block_submenu_pending_products') => array('href' => $sBaseUrl . '&bx_store_filter=pending_products', 'active' => 'pending_products' == bx_get('bx_store_filter')),
        );
        return array($sContent, $aMenu, '', '');
    }

    function getBlockCode_Browse()
    {
        bx_store_import ('SearchResult');
        $o = new BxStoreSearchResult('user', process_db_input ($this->_aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION));
        $o->aCurrent['rss'] = 0;

        $o->sBrowseUrl = "browse/my";
        $o->aCurrent['title'] = _t('_bx_store_page_title_my_store');

        if ($o->isError) {
            return DesignBoxContent(_t('_bx_store_block_users_products'), MsgBox(_t('_Empty')), 1);
        }

        if ($s = $o->processing()) {
            $this->_oTemplate->addCss (array('unit.css', 'twig.css', 'main.css'));
            return $s;
        } else {
            return DesignBoxContent(_t('_bx_store_block_users_products'), MsgBox(_t('_Empty')), 1);
        }
    }

    function getBlockCode_Main()
    {
        $iActive = $this->_oDb->getCountByAuthorAndStatus($this->_aProfile['ID'], 'approved');
        $iPending = $this->_oDb->getCountByAuthorAndStatus($this->_aProfile['ID'], 'pending');
        $sBaseUrl = BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "browse/my";
        $aVars = array ('msg' => '');
        if ($iPending)
            $aVars['msg'] = sprintf(_t('_bx_store_msg_you_have_pending_approval_products'), $sBaseUrl . '&bx_store_filter=pending_products', $iPending);
        elseif (!$iActive)
            $aVars['msg'] = sprintf(_t('_bx_store_msg_you_have_no_products'), $sBaseUrl . '&bx_store_filter=add_product');
        else
            $aVars['msg'] = sprintf(_t('_bx_store_msg_you_have_some_products'), $sBaseUrl . '&bx_store_filter=manage_products', $iActive, $sBaseUrl . '&bx_store_filter=add_product');
        return $this->_oTemplate->parseHtmlByName('my_store_main', $aVars);
    }

    function getBlockCode_Add()
    {
        if (!$this->_oMain->isAllowedAdd()) {
            return MsgBox(_t('_Access denied'));
        }
        ob_start();
        $this->_oMain->_addForm(BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'browse/my');
        $aVars = array ('form' => ob_get_clean(), 'id' => '');
        $this->_oTemplate->addCss ('forms_extra.css');
        return $this->_oTemplate->parseHtmlByName('my_store_create_product', $aVars);
    }

    function getBlockCode_Pending()
    {
        $sForm = $this->_oMain->_manageEntries ('my_pending', '', false, 'bx_store_pending_user_form', array(
                'action_delete' => '_bx_store_admin_delete',
        ), 'bx_store_my_pending', false, 7);
        if (!$sForm)
            return MsgBox(_t('_Empty'));
        $aVars = array ('form' => $sForm, 'id' => 'bx_store_my_pending');
        return $this->_oTemplate->parseHtmlByName('my_store_manage', $aVars);
    }

    function getBlockCode_My()
    {
        $sForm = $this->_oMain->_manageEntries ('user', process_db_input ($this->_aProfile['NickName'], BX_TAGS_NO_ACTION, BX_SLASHES_NO_ACTION), false, 'bx_store_user_form', array(
            'action_delete' => '_bx_store_admin_delete',
        ), 'bx_store_my_active', true, 7);
        $aVars = array ('form' => $sForm, 'id' => 'bx_store_my_active');
        return $this->_oTemplate->parseHtmlByName('my_store_manage', $aVars);
    }
}
