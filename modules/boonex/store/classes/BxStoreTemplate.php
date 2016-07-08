<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigTemplate');

/*
 * Store module View
 */
class BxStoreTemplate extends BxDolTwigTemplate
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
            $this->_oMain = BxDolModule::getInstance('BxStoreModule');

        if (!$this->_oMain->isAllowedView ($aData)) {
            $aVars = array ('extra_css_class' => 'bx_store_unit');
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
            'product_url' => BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . 'view/' . $aData['uri'],
            'product_title' => $aData['title'],
            'price_range' => $this->_oMain->_formatPriceRange($aData),
            'snippet_text' => $this->_oMain->_formatSnippetText($aData, 500),
            'bx_if:full' => array (
                'condition' => !$isShort,
                'content' => array (
                    'created' => defineTimeInterval($aData['created']),
                    'author' => $aData['author_id'] ? getNickName($aData['author_id']) : _t('_bx_store_admin'),
                    'author_url' => $aData['author_id'] ? getProfileLink($aData['author_id']) : 'javascript:void(0);',
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

    function blockFiles (&$aData)
    {
        $iEntryId = $aData['id'];
        $aReadyMedia = array ();
        if ($iEntryId)
            $aReadyMedia = $GLOBALS['oBxStoreModule']->_oDb->getFiles($iEntryId, true);

        if (!$aReadyMedia)
            return '';

        $aVars = array (
            'bx_repeat:files' => array (),
        );

        bx_import('BxDolPayments');
        $oPayment = BxDolPayments::getInstance();

        $sCurrencySign = getParam('pmt_default_currency_sign');
        foreach ($aReadyMedia as $r) {

            $iMediaId = $r['media_id'];

            $a = BxDolService::call('files', 'get_file_array', array($iMediaId), 'Search');
            if (!$a['date'])
                continue;

            bx_import('BxTemplFormView');
            $oForm = new BxTemplFormView(array());

            $aInputBtnDownload = array (
                'type' => 'submit',
                'name' => 'bx_store_download',
                'value' => _t ('_bx_store_download'),
                'attrs' => array(
                    'onclick' => "window.open ('" . BX_DOL_URL_ROOT . $this->_oConfig->getBaseUri() . "download/{$r['id']}','_self');",
                ),
            );

            $aVars['bx_repeat:files'][] = array (
                'id' => $iMediaId,
                'title' => $a['title'],
                'icon' => $a['file'],
                'price' => $sCurrencySign . ' ' . $r['price'],
                'for_group' => sprintf(_t('_bx_store_for_group'), $GLOBALS['oBxStoreModule']->getGroupName($r['allow_purchase_to'])),
                'date' => defineTimeInterval($a['date']),
                'bx_if:purchase' => array (
                    'condition' => $GLOBALS['oBxStoreModule']->isAllowedPurchase($r),
                    'content' => array (
                        'btn_purchase' => $oPayment->getAddToCartLink($r['author_id'], $this->_oConfig->getId(), $r['id'], 1),
                    ),
                ),
                'bx_if:download' => array (
                    'condition' => $GLOBALS['oBxStoreModule']->isAllowedDownload($r),
                    'content' => array (
                        'btn_download' => $oForm->genInputButton ($aInputBtnDownload),
                    ),
                ),
            );
        }

        if (!$aVars['bx_repeat:files'])
            return '';

        return $this->parseHtmlByName('block_files', $aVars);
    }

    function blockFields (&$aDataEntry)
    {
        $sRet = '<table class="bx_store_fields">';
        bx_store_import ('FormAdd');
        $oForm = new BxStoreFormAdd ($GLOBALS['oBxStoreModule'], getLoggedId());
        foreach ($oForm->aInputs as $k => $a) {
            if (!isset($a['display'])) continue;
            $sRet .= '<tr><td class="bx_store_field_name bx-def-font-grayed bx-def-padding-sec-right" valign="top">' . $a['caption'] . '</td><td class="bx_store_field_value">';
            if (is_string($a['display']) && is_callable(array($this, $a['display'])))
                $sRet .= call_user_func_array(array($this, $a['display']), array($aDataEntry[$k]));
            else
                $sRet .= $aDataEntry[$k];
            $sRet .= '</td></tr>';
        }
        $sRet .= '</table>';
        return $sRet;
    }
}
