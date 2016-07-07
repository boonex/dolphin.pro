<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolAlbums.php');
require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolPrivacy.php');
require_once(BX_DIRECTORY_PATH_ROOT . 'templates/tmpl_'.$GLOBALS['tmpl'].'/scripts/BxTemplFormView.php');

class BxBaseAlbumForm
{
    var $iOwnerId;
    var $sType;
    var $iAlbumId;
    var $aForm;
    var $aInfo = array();

    function __construct ($sType, $iAlbum = 0)
    {
        $this->iOwnerId = getLoggedId();
        if ($this->iOwnerId == 0)
            return;
        
        $this->iAlbumId = (int)$iAlbum;
        $this->sType = strip_tags($sType);
        $oPrivacy = new BxDolPrivacy('sys_albums', 'ID', 'Owner');
        $aPrivField = $oPrivacy->getGroupChooser($this->iOwnerId, 'sys_albums', 'view');
        $this->aForm = array(
            'form_attrs' => array(
                'name'     => 'form_album',
                'action'   => '',
                'method'   => 'post',
                'enctype' => 'multipart/form-data',
            ),
            'params' => array (
                'db' => array(
                    'table' => 'sys_albums',
                    'key' => 'ID',
                    'uri' => 'Uri',
                    'uri_title' => 'Caption',
                    'submit_name' => 'save',
                ),
            ),
            'inputs' => array(
                'Caption' => array(
                    'type' => 'text',
                    'name' => 'Caption',
                    'caption' => _t('_sys_album_caption_capt'),
                    'required' => true,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,128),
                        'error' => _t ('_sys_album_err_capt'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                    'display' => true
                ),
                'Location' => array(
                    'type' => 'text',
                    'name' => 'Location',
                    'caption' => _t('_sys_album_caption_loc'),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                    'display' => true
                ),
                'Description' => array(
                    'type' => 'textarea',
                    'name' => 'Description',
                    'caption' => _t('_sys_album_caption_desc'),
                    'required' => true,
                    'html' => false,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,255),
                        'error' => _t ('_sys_album_err_desc'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    )
                ),
                'allow_view_to' => $aPrivField,
                'Type' => array(
                    'type' => 'hidden',
                    'name' => 'Type',
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                    'value' => $this->sType
                ),
                'Control' => array(
                    'type' => 'input_set',
                    0 => array(
                        'type' => 'submit',
                        'name' => 'save',
                        'value' => _t("_sys_album_create"),
                    ),
                    1 => array(
                        'type' => 'reset',
                        'name' => 'cancel',
                        'value' => _t("_sys_album_cancel"),
                    ),
                )
            )
        );
        $iAlbum = (int)$iAlbum;
        if ($this->iAlbumId > 0) {
            $oAlbum = new BxDolAlbums($this->sType);
            $this->aInfo = $oAlbum->getAlbumInfo(array('fileId'=>$iAlbum));
            if ($this->iOwnerId != $this->aInfo['Owner'])
                return;
            $this->aForm['inputs']['Control'] = array(
                'type' => 'input_set',
                'colspan' => true,
                0 => array(
                    'type' => 'submit',
                    'name' => 'save',
                    'value' => _t("_sys_album_save_changes"),
                ),
                1 => array(
                    'type' => 'submit',
                    'name' => 'delete',
                    'value' => _t("_sys_album_delete"),
                ),
                2 => array(
                    'type' => 'submit',
                    'name' => 'launch',
                    'value' => _t("_sys_album_add"),
                ),
                3 => array(
                    'type' => 'submit',
                    'name' => 'launch',
                    'value' => _t("_sys_album_edit_items"),
                ),
                4 => array(
                    'type' => 'submit',
                    'name' => 'launch',
                    'value' => _t("_sys_album_organize"),
                ),
                5 => array(
                    'type' => 'reset',
                    'name' => 'cancel',
                    'value' => _t("_sys_album_cancel"),
                )
            );

            foreach ($this->aForm['inputs'] as $sKey => $aValue) {
                if ($sKey != 'Control')
                    $this->aForm['inputs'][$sKey]['value'] = $this->aInfo[$sKey];
            }
        }
    }

    function initControlSet ($aRedeclSet)
    {
    }

    function getFormCode ()
    {
        $oForm = new BxTemplFormView($this->aForm);
        $oForm->initChecker();
        if ($oForm->isSubmittedAndValid()) {
            $aValsAdd = array(
                'Date' => time(),
                'Uri' => $oForm->generateUri(),
                'Status' => 'active',
                'Owner' => $this->iOwnerId
            );
            if ($this->iAlbumId > 0 && $this->aInfo['Owner'] == $this->iOwnerId) {
                $aValsAdd = array('Date' => time());
                if (!$oForm->update($this->iAlbumId, $aValsAdd))
                    return MsgBox(_t('_sys_album_save_error'));
                else
                    return MsgBox(_t('_sys_album_save_succ'));
            } else {
                $iAlbumId = $oForm->insert($aValsAdd);
                if (!$iAlbumId)
                    return MsgBox(_t('_sys_album_save_error'));
                else
                    return MsgBox(_t('_sys_album_save_succ'));
            }
        } else
            return $oForm->getCode();
    }
}
