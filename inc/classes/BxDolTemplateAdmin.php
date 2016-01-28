<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTemplate');

class BxDolTemplateAdmin extends BxDolTemplate
{
    /**
     * Constructor
     */
    function __construct($sHomeFolder)
    {
        parent::__construct(BX_DIRECTORY_PATH_ROOT . $sHomeFolder . DIRECTORY_SEPARATOR, BX_DOL_URL_ROOT . $sHomeFolder . '/');

        $this->_sPrefix = 'BxDolTemplateAdmin';
        $this->_sInjectionsTable = 'sys_injections_admin';
        $this->_sInjectionsCache = 'sys_injections_admin.inc';

        $this->_sCodeKey = 'askin';
        $this->_sCode = isset($_COOKIE[$this->_sCodeKey]) && preg_match('/^[A-Za-z0-9_-]+$/', $_COOKIE[$this->_sCodeKey]) ? $_COOKIE[$this->_sCodeKey] : BX_DOL_TEMPLATE_DEFAULT_CODE;
        $this->_sCode = isset($_GET[$this->_sCodeKey]) && preg_match('/^[A-Za-z0-9_-]+$/', $_GET[$this->_sCodeKey]) ? $_GET[$this->_sCodeKey] : $this->_sCode;

        $this->addLocationJs('system_admin_js', $this->_sRootPath . 'js/' , $this->_sRootUrl . 'js/');
    }

    /**
     * Parse system keys.
     *
     * @param  string $sKey key
     * @return string value associated with the key.
     */
    function parseSystemKey($sKey, $mixedKeyWrapperHtml = null)
    {
        global $logged;

        $aKeyWrappers = $this->_getKeyWrappers($mixedKeyWrapperHtml);

        $sRet = '';
        switch( $sKey ) {
            case 'current_version':
                $sRet = $GLOBALS['site']['ver'] . '.' . $GLOBALS['site']['build'];
                break;
            case 'dir':
                $a = bx_lang_info();
                return $a['Direction'];
            case 'page_charset':
                $sRet = 'UTF-8';
                break;
            case 'page_keywords':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageKeywords']) && is_array($GLOBALS[$this->_sPrefix . 'PageKeywords']))
                    $sRet = '<meta name="keywords" content="' . process_line_output(implode(',', $GLOBALS[$this->_sPrefix . 'PageKeywords'])) . '" />';
                break;
            case 'page_description':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageDescription']) && is_string($GLOBALS[$this->_sPrefix . 'PageDescription']))
                    $sRet = '<meta name="description" content="' . process_line_output($GLOBALS[$this->_sPrefix . 'PageDescription']) . '" />';
                break;
            case 'page_header':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageTitle']))
                    $sRet = $GLOBALS[$this->_sPrefix . 'PageTitle'];
                else if(isset($GLOBALS['_page']['header']))
                    $sRet = $GLOBALS['_page']['header'];

                $sRet = process_line_output($sRet);
                break;
            case 'page_header_text':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageMainBoxTitle']))
                    $sRet = process_line_output($GLOBALS[$this->_sPrefix . 'PageMainBoxTitle']);
                else if(isset($GLOBALS['_page']['header_text']))
                    $sRet = $GLOBALS['_page']['header_text'];

                $sRet = process_line_output($sRet);
                break;
            case 'main_div_width':
                if(!empty($GLOBALS[$this->_sPrefix . 'PageWidth']))
                    $sRet = process_line_output($GLOBALS[$this->_sPrefix . 'PageWidth']);
                break;
            case 'top_menu':
                $sRet = BxDolAdminMenu::getTopMenu();
                break;
            case 'main_menu':
                $sRet = BxDolAdminMenu::getMainMenu();
                break;
            case 'dol_images':
                $sRet = $this->_processJsImages();
                break;
            case 'dol_lang':
                $sRet = $this->_processJsTranslations();
                break;
            case 'dol_options':
                $sRet = $this->_processJsOptions();
                break;
            case 'promo_code':
                if (defined('BX_PROMO_CODE'))
                    $sRet = BX_PROMO_CODE;
                else
                    $sRet = ' ';
                break;
            case 'copyright':
                $sRet = _t( '_copyright',   date('Y') ) . getVersionComment();
                break;
            }

        $sRet = BxDolTemplate::processInjection($GLOBALS['_page']['name_index'], $sKey, $sRet);
        return $sRet;
    }
}
