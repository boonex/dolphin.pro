<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolMenuBottom');

    /**
     * @see BxDolMenuBottom;
     */
    class BxBaseMenuBottom extends BxDolMenuBottom
    {
        /**
         * Class constructor;
         */
        function BxBaseMenuBottom()
        {
            parent::BxDolMenuBottom();
        }

        function getItems()
        {
            $sContent = parent::getItems();
            $sContent .= $this->getSwitcherLanguage();
            $sContent .= $this->getSwitcherTemplate();
            return $sContent;
        }

        function getSwitcherLanguage()
        {
            $sContent = '';

            $iLangsCount = count(getLangsArr());
            if($iLangsCount <= 1)
                return '';

            $sLangName = getCurrentLangName();

            $aTmplVars = array();
            $aTmplVars[] = array(
                'caption' => _t('_sys_bm_language', $sLangName),
                'link' => 'javascript:void(0)',
                'script' => 'onclick="javascript:showPopupLanguage()"',
                'target' => ''
            );

            $sContent .= $GLOBALS['oSysTemplate']->parseHtmlByName('extra_' . $this->sName . '_menu.html', array('bx_repeat:items' => $aTmplVars));
            $sContent .= $this->getListLanguage($sLangName);

            return $sContent;
        }

        function getSwitcherTemplate()
        {
            $sContent = '';
            if(getParam('enable_template') != 'on')
                return $sContent;

            $iTmplsCount = count(get_templates_array());
            if($iTmplsCount <= 1)
                return $sContent;

            $sTemplName = $GLOBALS['oSysTemplate']->getCode();

            $aTmplVars = array();
            $aTmplVars[] = array(
                'caption' => _t('_sys_bm_design', $sTemplName),
                'link' => 'javascript:void(0)',
                'script' => 'onclick="javascript:showPopupTemplate()"',
                'target' => ''
            );

            $sContent .= $GLOBALS['oSysTemplate']->parseHtmlByName('extra_' . $this->sName . '_menu.html', array('bx_repeat:items' => $aTmplVars));
            $sContent .= $this->getListTemplate($sTemplName);

            return $sContent;
        }

        function getListLanguage($sCurrent)
        {
            $sOutputCode = '';

            $aLangs = getLangsArrFull();
            if(count( $aLangs ) < 2)
                return $sOutputCode;

            $sGetTransfer = bx_encode_url_params($_GET, array('lang'));

            $aTmplVars = array();
            foreach( $aLangs as $sName => $aLang ) {
                $sFlag  = $GLOBALS['site']['flags'] . $aLang['Flag'] . '.gif';
                $aTmplVars[] = array (
                    'bx_if:show_icon' => array (
                        'condition' => $sFlag,
                        'content' => array (
                            'icon_src'      => $sFlag,
                            'icon_alt'      => $sName,
                            'icon_width'    => 18,
                            'icon_height'   => 12,
                        ),
                    ),
                    'class' => $sName == $sCurrent ? 'sys-bm-sub-item-selected' : '',
                    'link'    => bx_html_attribute($_SERVER['PHP_SELF']) . '?' . $sGetTransfer . 'lang=' . $sName,
                    'onclick' => '',
                    'title'   => $aLang['Title'],
                );
            }

            $sOutputCode .= $GLOBALS['oSysTemplate']->parseHtmlByName( 'extra_bottom_menu_sub_items.html', array(
                'name_method' => 'Language',
                'name_block' => 'language',
                'bx_repeat:items' => $aTmplVars
            ));

            return PopupBox('sys-bm-switcher-language', _t('_sys_bm_popup_cpt_language'), $sOutputCode);
        }

        function getListTemplate($sCurrent)
        {
            $sOutputCode = "";

            $aTemplates = get_templates_array();
            if(count($aTemplates) < 2)
                return $sOutputCode;

            $sGetTransfer = bx_encode_url_params($_GET, array('skin'));

            $aTmplVars = array();
            foreach($aTemplates as $sName => $sTitle) {
                $aTmplVars[] = array (
                    'bx_if:show_icon' => array (
                        'condition' => false,
                        'content' => array(),
                    ),
                    'class' => $sName == $sCurrent ? 'sys-bm-sub-item-selected' : '',
                    'link'    => bx_html_attribute($_SERVER['PHP_SELF']) . '?' . $sGetTransfer . 'skin=' . $sName,
                    'onclick' => '',
                    'title'   => $sTitle
                );
            }

            $sOutputCode .= $GLOBALS['oSysTemplate']->parseHtmlByName( 'extra_bottom_menu_sub_items.html', array(
                'name_method' => 'Template',
                'name_block' => 'template',
                'bx_repeat:items' => $aTmplVars
            ));

            return PopupBox('sys-bm-switcher-template', _t('_sys_bm_popup_cpt_design'), $sOutputCode);
        }
    }
