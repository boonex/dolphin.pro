<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigTemplate');

/*
 * Groups module View
 */
class BxGroupsTemplate extends BxDolTwigTemplate
{
    var $_iPageIndex = 500;

    /**
     * Constructor
     */
    function __construct(&$oConfig, &$oDb)
    {
        parent::__construct($oConfig, $oDb);
    }

    function unit ($aData, $sTemplateName, &$oVotingView, $isShort = false)
    {
        if (null == $this->_oMain)
            $this->_oMain = BxDolModule::getInstance('BxGroupsModule');

        if (!$this->_oMain->isAllowedView ($aData)) {
            $aVars = array ('extra_css_class' => 'bx_groups_unit');
            return $this->parseHtmlByName('twig_unit_private', $aVars);
        }

        $sImage = '';
        if ($aData['thumb']) {
            $a = array ('ID' => $aData['author_id'], 'Avatar' => $aData['thumb']);
            $aImage = BxDolService::call('photos', 'get_image', array($a, 'browse'), 'Search');
            $sImage = $aImage['no_image'] ? '' : $aImage['file'];
        }

        $aVars = array (
            'id' => $aData['id'],
            'thumb_url' => $sImage ? $sImage : $this->getImageUrl('no-image-thumb.png'),
            'group_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aData['uri'],
            'group_title' => $aData['title'],
            'created' => defineTimeInterval($aData['created']),
            'fans_count' => $aData['fans_count'],
            'country_city' => $this->_oMain->_formatLocation($aData),
            'snippet_text' => $this->_oMain->_formatSnippetText($aData),
            'bx_if:full' => array (
                'condition' => !$isShort,
                'content' => array (
                    'author' => getNickName($aData['author_id']),
                    'author_url' => $aData['author_id'] ? getProfileLink($aData['author_id']) : 'javascript:void(0);',
                    'created' => defineTimeInterval($aData['created']),
                    'rate' => $oVotingView ? $oVotingView->getJustVotingElement(0, $aData['id'], $aData['rate']) : '&#160;',
                ),
            ),
        );

        return $this->parseHtmlByName($sTemplateName, $aVars);
    }

    // ======================= ppage compose block functions

    function blockDesc (&$aDataEntry)
    {
        $aVars = array (
            'description' => $aDataEntry['desc'],
        );
        return $this->parseHtmlByName('block_description', $aVars);
    }

    function blockFields (&$aDataEntry)
    {
        $sRet = '<table class="bx_groups_fields">';
        bx_groups_import ('FormAdd');
        $oForm = new BxGroupsFormAdd ($GLOBALS['oBxGroupsModule'], getLoggedId());
        foreach ($oForm->aInputs as $k => $a) {
            if (!isset($a['display']) || !$aDataEntry[$k]) continue;
            $sRet .= '<tr><td class="bx_groups_field_name bx-def-font-grayed bx-def-padding-sec-right" valign="top">' . $a['caption'] . '</td><td class="bx_groups_field_value">';
            if (is_string($a['display']) && is_callable(array($this, $a['display'])))
                $sRet .= call_user_func_array(array($this, $a['display']), array($aDataEntry[$k]));
            else if (0 === strcasecmp($k, 'country'))
                $sRet .= _t($GLOBALS['aPreValues']['Country'][$aDataEntry[$k]]['LKey']);
            else
                $sRet .= $aDataEntry[$k];
            $sRet .= '</td></tr>';
        }
        $sRet .= '</table>';
        return $sRet;
    }
}
