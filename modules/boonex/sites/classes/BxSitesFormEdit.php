<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

global $aModule;
bx_import('FormAdd', $aModule);
bx_import('BxDolCategories');

class BxSitesFormEdit extends BxSitesFormAdd
{
    function __construct($oModule, $aParam = array())
    {
        $this->_oModule = $oModule;
        $this->_aParam = $aParam;

        if (count($aParam) && isset($aParam['photo']) && $aParam['photo'] != 0) {
            $aFile = BxDolService::call('photos', 'get_photo_array', array($aParam['photo'], 'browse'), 'Search');

            if (!$aFile['no_image']) {
                $aParam = array_merge($aParam, array(
                    'thumbnail' => $GLOBALS['oBxSitesModule']->_oTemplate->parseHtmlByName('thumb110.html', array(
                        'image' => $aFile['file'],
                        'spacer' => getTemplateIcon('spacer.gif')
                    ))
                ));
            }
        }

        $this->_aCustomForm = $this->getFullForm();
        $this->_aCustomForm['form_attrs']['action'] = BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'edit/' . $aParam['id'];

        $oCategories = new BxDolCategories();
        $oCategories->getTagObjectConfig ();
        $this->_aCustomForm['inputs']['categories'] = $oCategories->getGroupChooser ('bx_sites', (int)$this->_oModule->iOwnerId, true, $this->_aParam['categories']);

        $this->_aCustomForm['inputs']['photo']['info'] = '';

        $aFormInputsSubmit = array (
            'Submit' => array (
                'type' => 'submit',
                'name' => 'submit_form',
                'value' => _t('_Submit'),
                'colspan' => false,
            ),
        );

        $this->_aCustomForm['inputs'] = array_merge($this->_aCustomForm['inputs'], $aFormInputsSubmit);

        BxTemplFormView::__construct ($this->_aCustomForm);
    }

    function checkUploadPhoto()
    {
        $aFileInfo = array (
            'medTitle' => stripslashes($this->getCleanValue('title')),
            'medDesc' => stripslashes($this->getCleanValue('title')),
            'medTags' => 'sites',
            'Categories' => array('Sites'),
        );
        $sTmpFile = BX_DIRECTORY_PATH_ROOT . 'tmp/' . time() . $this->_oModule->iOwnerId;

        if (move_uploaded_file($_FILES['photo']['tmp_name'],  $sTmpFile)) {
            if ($this->_aParam['photo'] != 0)
                BxDolService::call('photos', 'remove_object', array($this->_aParam['photo']), 'Module');

            $iRet = BxDolService::call('photos', 'perform_photo_upload', array($sTmpFile, $aFileInfo, false), 'Uploader');
            if (!$iRet)
                @unlink ($sTmpFile);

            return $iRet;
        }

        return $this->_aParam['photo'];
    }
}
