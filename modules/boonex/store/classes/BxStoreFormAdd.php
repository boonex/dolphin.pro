<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolProfileFields');
bx_import('BxDolFormMedia');

class BxStoreFormAdd extends BxDolFormMedia
{
    var $_oMain;
    var $_oDb;

    function __construct ($oMain, $iProfileId, $isRemovePhotoFieldAllowed = true, $iEntryId = 0, $iThumb = 0)
    {
        $this->_aMedia = array ();
        if (BxDolRequest::serviceExists('photos', 'perform_photo_upload', 'Uploader'))
            $this->_aMedia['images'] = array (
                'post' => 'ready_images',
                'upload_func' => 'uploadPhotos',
                'tag' => BX_STORE_PHOTOS_TAG,
                'cat' => BX_STORE_PHOTOS_CAT,
                'thumb' => 'thumb',
                'module' => 'photos',
                'title_upload_post' => 'images_titles',
                'title_upload' => _t('_bx_store_form_caption_file_title'),
                'service_method' => 'get_photo_array',
            );

        if (BxDolRequest::serviceExists('videos', 'perform_video_upload', 'Uploader'))
            $this->_aMedia['videos'] = array (
                'post' => 'ready_videos',
                'upload_func' => 'uploadVideos',
                'tag' => BX_STORE_VIDEOS_TAG,
                'cat' => BX_STORE_VIDEOS_CAT,
                'thumb' => false,
                'module' => 'videos',
            	'title_upload' => _t('_bx_store_form_caption_file_title'),
                'service_method' => 'get_video_array',
            );

        $this->_oMain = $oMain;
        $this->_oDb = $oMain->_oDb;

        bx_import('BxDolCategories');
        $oCategories = new BxDolCategories();
        $oCategories->getTagObjectConfig ();

        // generate templates for form custom elements

        $aCustomMediaTemplates = $this->generateCustomMediaTemplates ($iProfileId, $iEntryId, $iThumb);

        // files

        $aFilesChoiceOrig = $this->_getFilesInEntry ('files', 'get_file_array', 'ready_files', 'files', (int)$iProfileId, $iEntryId);
        $aFilesChoice = array ();
        $sCurrencySign = getParam('pmt_default_currency_sign');
        foreach ($aFilesChoiceOrig as $k => $r) {
            if (!$r['checked']) continue;
            $a = $GLOBALS['oBxStoreModule']->_oDb->getFileInfo ($iEntryId, $r['id']);
            $r['file_id'] = $a['id'];
            $r['title'] .= ' - ' . $sCurrencySign . ' ' . $a['price'] . ' (' . $GLOBALS['oBxStoreModule']->getGroupName($a['allow_purchase_to']) . ')';
            $r['visibility'] = $a['hidden'] ? _t('_bx_store_product_file_hidden') : _t('_bx_store_product_file_visible');
            $aFilesChoice[] = $r;
        }
        $aVarsFilesChoice = array(
            'bx_store_base_url' => $this->_oMain->_oConfig->getBaseUri(),
            'bx_if:empty' => array(
                'condition' => empty($aFilesChoice),
                'content' => array ()
            ),
            'bx_repeat:files' => $aFilesChoice,
        );

        $aInputPrivacyPurchase = $GLOBALS['oBxStoreModule']->_oPrivacyProduct->getGroupChooser($iProfileId, 'store', 'purchase');
        foreach ($aInputPrivacyPurchase['values'] as $k => $r) {
            if ($r['key'] == BX_DOL_PG_ALL) {
                unset ($aInputPrivacyPurchase['values'][$k]);
                break;
            }
        }

        require_once(BX_DIRECTORY_PATH_INC . 'membership_levels.inc.php');
        $aMemberships = getMemberships ();
        unset ($aMemberships[MEMBERSHIP_ID_NON_MEMBER]); // unset Non-member
        unset ($aMemberships[MEMBERSHIP_ID_STANDARD]); // unset member

        $aMemberships = array('' => '----') + $aMemberships;
        $aMembershipsValues = array ();
        foreach ($aMemberships as $k => $v)
            $aMembershipsValues[] = array ('key' => $k ? 'm' . $k : $k, 'value' => $v);
        $aInputPrivacyPurchase['values'] = array_merge($aInputPrivacyPurchase['values'], $aMembershipsValues);
/*
        if (empty($aInputPrivacyPurchase['value']) || !$aInputPrivacyPurchase['value'])
            $aInputPrivacyPurchase['value'] = BX_DOL_PG_MEMBERS;
 */
        $sInputPrivacyPurchase = str_replace('name="allow_purchase_to"', 'name="allow_purchase_to[]"', $this->genInputSelect($aInputPrivacyPurchase));

        $aVarsFilesUpload = array (
            'file' => 'files',
            'title' => 'files_titles',
            'file_upload_title' => _t('_bx_store_form_caption_file_title'),
            'bx_if:price' => array (
                'condition' => true, 'content' => array('price' => 'files_prices', 'file_price_title' => _t('_bx_store_form_caption_file_price'))
            ),
            'bx_if:privacy' => array (
                'condition' => true, 'content' => array('select' => $sInputPrivacyPurchase, 'file_permission_title' => _t('_bx_store_form_caption_file_allow_purcase_to'))
            ),
        );

        // privacy

        $aInputPrivacyCustom = array ();
        $aInputPrivacyCustom[] = array ('key' => '', 'value' => '----');
        $aInputPrivacyCustom[] = array ('key' => 'c', 'value' => _t('_bx_store_privacy_customers_only'));

        $aInputPrivacyPostInForum = $GLOBALS['oBxStoreModule']->_oPrivacyProduct->getGroupChooser($iProfileId, 'store', 'post_in_forum');
        $aInputPrivacyPostInForum['values'] = array_merge($aInputPrivacyPostInForum['values'], $aInputPrivacyCustom);
        $aInputPrivacyPostInForum['db'] = array (
            'pass' => 'Preg',
            'params' => array('/^([0-9c]+)$/'),
        );

        $aInputPrivacyViewForum = $GLOBALS['oBxStoreModule']->_oPrivacyProduct->getGroupChooser($iProfileId, 'store', 'view_forum');
        $aInputPrivacyViewForum['values'] = array_merge($aInputPrivacyViewForum['values'], $aInputPrivacyCustom);
        $aInputPrivacyViewForum['db'] = $aInputPrivacyPostInForum['db'];

        $aCustomForm = array(

            'form_attrs' => array(
                'name'     => 'form_store',
                'action'   => '',
                'method'   => 'post',
                'enctype' => 'multipart/form-data',
            ),

            'params' => array (
                'db' => array(
                    'table' => 'bx_store_products',
                    'key' => 'id',
                    'uri' => 'uri',
                    'uri_title' => 'title',
                    'submit_name' => 'submit_form',
                ),
            ),

            'inputs' => array(

                'header_info' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_store_form_header_info')
                ),
                'title' => array(
                    'type' => 'text',
                    'name' => 'title',
                    'caption' => _t('_bx_store_form_caption_title'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,100),
                        'error' => _t ('_bx_store_form_err_title'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'desc' => array(
                    'type' => 'textarea',
                    'name' => 'desc',
                    'caption' => _t('_bx_store_form_caption_desc'),
                    'required' => true,
                    'html' => 2,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(20,64000),
                        'error' => _t ('_bx_store_form_err_desc'),
                    ),
                    'db' => array (
                        'pass' => 'XssHtml',
                    ),
                ),
                'tags' => array(
                    'type' => 'text',
                    'name' => 'tags',
                    'caption' => _t('_Tags'),
                    'info' => _t('_sys_tags_note'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'avail',
                        'error' => _t ('_bx_store_form_err_tags'),
                    ),
                    'db' => array (
                        'pass' => 'Tags',
                    ),
                ),

                'categories' => $oCategories->getGroupChooser ('bx_store', (int)$iProfileId, true),

                'header_images' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_store_form_header_images'),
                    'collapsable' => true,
                    'collapsed' => false,
                ),
                'thumb' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['images']['thumb_choice'],
                    'name' => 'thumb',
                    'caption' => _t('_bx_store_form_caption_thumb_choice'),
                    'info' => _t('_bx_store_form_info_thumb_choice'),
                    'required' => false,
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                'images_choice' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['images']['choice'],
                    'name' => 'images_choice[]',
                    'caption' => _t('_bx_store_form_caption_images_choice'),
                    'info' => _t('_bx_store_form_info_images_choice'),
                    'required' => false,
                ),
                'images_upload' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['images']['upload'],
                    'name' => 'images_upload[]',
                    'caption' => _t('_bx_store_form_caption_images_upload'),
                    'info' => _t('_bx_store_form_info_images_upload'),
                    'required' => false,
                ),

                'header_videos' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_store_form_header_videos'),
                    'collapsable' => true,
                    'collapsed' => false,
                ),
                'videos_choice' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['videos']['choice'],
                    'name' => 'videos_choice[]',
                    'caption' => _t('_bx_store_form_caption_videos_choice'),
                    'info' => _t('_bx_store_form_info_videos_choice'),
                    'required' => false,
                ),
                'videos_upload' => array(
                    'type' => 'custom',
                    'content' => $aCustomMediaTemplates['videos']['upload'],
                    'name' => 'videos_upload[]',
                    'caption' => _t('_bx_store_form_caption_videos_upload'),
                    'info' => _t('_bx_store_form_info_videos_upload'),
                    'required' => false,
                ),

                'header_files' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_store_form_header_files'),
                    'collapsable' => true,
                    'collapsed' => false,
                ),
                'files_choice' => array(
                    'type' => 'custom',
                    'content' => $aFilesChoice ? $GLOBALS['oBxStoreModule']->_oTemplate->parseHtmlByName('form_field_product_files_choice', $aVarsFilesChoice) : '',
                    'name' => 'files_choice[]',
                    'caption' => _t('_bx_store_form_caption_files_choice'),
                    'info' => _t('_bx_store_form_info_files_choice'),
                    'required' => false,
                ),
                'files_upload' => array(
                    'type' => 'custom',
                    'content' => $GLOBALS['oBxStoreModule']->_oTemplate->parseHtmlByName('form_field_files_upload', $aVarsFilesUpload),
                    'name' => 'files_upload[]',
                    'caption' => _t('_bx_store_form_caption_files_upload'),
                    'info' => _t('_bx_store_form_info_files_upload'),
                    'required' => false,
                ),

                'header_privacy' => array(
                    'type' => 'block_header',
                    'caption' => _t('_bx_store_form_header_privacy'),
                ),

                'allow_view_product_to' => $GLOBALS['oBxStoreModule']->_oPrivacyProduct->getGroupChooser($iProfileId, 'store', 'view_product'),

                'allow_post_in_forum_to' => $aInputPrivacyPostInForum,

                'allow_view_forum_to' => $aInputPrivacyViewForum,

            ),
        );

        if (!$aCustomForm['inputs']['images_choice']['content']) {
            unset ($aCustomForm['inputs']['thumb']);
            unset ($aCustomForm['inputs']['images_choice']);
        }

        if (!$aCustomForm['inputs']['videos_choice']['content'])
            unset ($aCustomForm['inputs']['videos_choice']);

        if (!$aFilesChoice)
            unset ($aCustomForm['inputs']['files_choice']);

        if (!isset($this->_aMedia['images'])) {
            unset ($aCustomForm['inputs']['header_images']);
            unset ($aCustomForm['inputs']['PrimPhoto']);
            unset ($aCustomForm['inputs']['images_choice']);
            unset ($aCustomForm['inputs']['images_upload']);
            unset ($aCustomForm['inputs']['allow_upload_photos_to']);
        }

        if (!isset($this->_aMedia['videos'])) {
            unset ($aCustomForm['inputs']['header_videos']);
            unset ($aCustomForm['inputs']['videos_choice']);
            unset ($aCustomForm['inputs']['videos_upload']);
            unset ($aCustomForm['inputs']['allow_upload_videos_to']);
        }

        $aFormInputsSubmit = array (
            'Submit' => array (
                'type' => 'submit',
                'name' => 'submit_form',
                'value' => _t('_Submit'),
            ),
        );

        $aCustomForm['inputs'] = array_merge($aCustomForm['inputs'], $aFormInputsSubmit);

        $this->processMembershipChecksForMediaUploads ($aCustomForm['inputs']);

        parent::__construct ($aCustomForm);
    }

    function uploadFiles ($sTag, $sCat, $sName = 'files', $sTitle = 'files_titles', $sTitleAlt = 'title', $sPrivacy = 'allow_purchase_to')
    {
        if(empty($sTag))
            $sTag = BX_STORE_FILES_TAG;

        if(empty($sCat))
            $sCat = BX_STORE_FILES_CAT;

        $aTitles = $this->getCleanValue($sTitle);
        $aPrivacy = $this->getCleanValue($sPrivacy);
        $aPrices = $_POST['files_prices'];

        $aRet = array();
        foreach ($_FILES[$sName]['tmp_name'] as $i => $sUploadedFile) {
            $aFileInfo = array (
                'medTitle' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'medDesc' => is_array($aTitles) && isset($aTitles[$i]) && $aTitles[$i] ? stripslashes($aTitles[$i]) : stripslashes($this->getCleanValue($sTitleAlt)),
                'medTags' => $sTag,
                'Categories' => array($sCat),
                'Type' => $_FILES[$sName]['type'][$i],
            );
            $aPathInfo = pathinfo ($_FILES[$sName]['name'][$i]);
            $sTmpFile = BX_DIRECTORY_PATH_ROOT . 'tmp/v' . time() . getLoggedId() . $i . '.' . $aPathInfo['extension'];
            if (move_uploaded_file($sUploadedFile,  $sTmpFile)) {
                $iRet = BxDolService::call('files', 'perform_file_upload', array($sTmpFile, $aFileInfo), 'Uploader');
                @unlink ($sTmpFile);
                $sPrice = 0;
                if (preg_match('/([0-9]+[\.0-9]*)/', $aPrices[$i], $m))
                    $sPrice = $m[1];
                if ($iRet)
                    $aRet[] = array ('id' => $iRet, 'price' => $sPrice, 'privacy' => $aPrivacy[$i]);
            }
        }
        return $aRet;
    }

    function processMedia ($iEntryId, $iProfileId)
    {
        parent::processMedia ($iEntryId, $iProfileId);

        if ($aMedia = $this->uploadFiles(BX_STORE_FILES_TAG, BX_STORE_FILES_CAT)) {
            $this->_oDb->insertMediaFiles ($iEntryId, $aMedia, $this->_oMain->_iProfileId);
        }
    }
}
