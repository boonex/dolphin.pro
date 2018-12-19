<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplFormView');

/**
 * Global settings visualisation in admin panel.
 *
 * Example of usage:
 * 1. Add form with settings
 *
 * $oSettings = new BxDolAdminSettings($mixedCategory);
 * $oSettings->getForm();
 *
 * 2. Save sattings on form submit
 *
 * if(isset($_POST['save']) && isset($_POST['cat'])) {
 *  $sResult = $oSettings->saveChanges($_POST);
 * }
 *
 *
 * Memberships/ACL:
 * Doesn't depend on user's membership.
 *
 *
 * Alerts:
 * no alerts available
 *
 */
class BxDolAdminSettings
{
    var $_oDb;
    var $_sActionUrl;
    var $_mixedCategory;
    var $_iCategoryActive;

    var $_iResultTimer;
    var $_aCustomCategories;

    /**
     * constructor
     */
    function __construct($mixedCategory, $sActionUrl = '')
    {
        $this->_oDb = $GLOBALS['MySQL'];
        $this->_sActionUrl = !empty($sActionUrl) ? $sActionUrl : bx_html_attribute($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');

        $this->_mixedCategory = $mixedCategory;
        $this->_iCategoryActive = 0;

        $this->_iResultTimer = 3;
        $this->_aCustomCategories = array(
            'ap' => array(
                'title' => '_getCatTitleAdminPassword',
                'content' => '_getCatContentAdminPassword',
                'save' => '_saveCatAdminPassword'
            ),
            16 => array(
                'save' => '_saveCatWatermark'
            ),
            26 => array(
                'on_save' => '_onSavePermalinks'
            )
        );
    }

    function setActiveCategory($mixed)
    {
        if (is_int($mixed))
            $this->_iCategoryActive = $mixed;
        else
            $this->_iCategoryActive = (int)$this->_oDb->getOne("SELECT `ID` FROM `sys_options_cats` WHERE `name`= ?", [$mixed]);
    }
    function saveChanges(&$aData)
    {
        $aCategories = explode(',', process_db_input($aData['cat'], BX_TAGS_STRIP));
        foreach($aCategories as $mixedCategory) {
            if(!is_numeric($mixedCategory) || isset($this->_aCustomCategories[$mixedCategory]['save'])) {
                $mixedResult = $this->{$this->_aCustomCategories[$mixedCategory]['save']}($aData);
                if($mixedResult !== true)
                    return $mixedResult;
            } else if(is_numeric($mixedCategory)) {
                $aItems = $this->_oDb->getAll("SELECT `Name` AS `name`, `desc` AS `title`, `Type` AS `type`, `AvailableValues` AS `extra`, `check` AS `check`, `err_text` AS `check_error` FROM `sys_options` WHERE `kateg`= ?", [$mixedCategory]);

                $aItemsData = array();
                foreach($aItems as $aItem) {
                    if(is_array($aData[$aItem['name']]))
                        foreach($aData[$aItem['name']] as $sKey => $sValue)
                            $aItemsData[$aItem['name']][$sKey] = process_db_input($sValue, BX_TAGS_STRIP);
                    else
                        $aItemsData[$aItem['name']] = process_db_input($aData[$aItem['name']], BX_TAGS_STRIP);

                    if(!empty($aItem['check'])) {
                        $oFunction = function($arg0) use ($aItem) {
                            return eval($aItem['check']);
                        };

                        if(!$oFunction($aItemsData[$aItem['name']])) {
                            $this->_iCategoryActive = (int)$mixedCategory;
                            return MsgBox("'" . $aItem['title'] .  "' " . $aItem['check_error'], $this->_iResultTimer);
                        }
                    }

                    $bIsset = isset($aItemsData[$aItem['name']]);
                    if($bIsset && is_array($aItemsData[$aItem['name']]))
                        $aItemsData[$aItem['name']] = implode(',', $aItemsData[$aItem['name']]);
                    else if(!$bIsset)
                        $aItemsData[$aItem['name']] = $this->_empty($aItem);

                    setParam ($aItem['name'], $aItemsData[$aItem['name']]);
                }
            }
            if(isset($this->_aCustomCategories[$mixedCategory]['on_save']))
                $this->{$this->_aCustomCategories[$mixedCategory]['on_save']}();
        }
        return MsgBox(_t('_adm_txt_settings_success'), $this->_iResultTimer);
    }
    function getTitle()
    {
        $sResult = '';

        if(!is_numeric($this->_mixedCategory) || isset($this->_aCustomCategories[$this->_mixedCategory]['title']))
            $sResult = $this->{$this->_aCustomCategories[$this->_mixedCategory]['title']}();
        else if(is_numeric($this->_mixedCategory))
            $sResult = $this->_oDb->getOne("SELECT `name` AS `name` FROM `sys_options_cats` WHERE `ID`='" . $this->_mixedCategory . "' LIMIT 1");

        return $sResult;
    }
    function getFormObject($aCategories = array())
    {
        if(empty($aCategories))
            $aCategories[] = $this->_mixedCategory;

        $bWrap = count($aCategories) > 1;

        $aForm = array(
            'form_attrs' => array(
                'id' => 'adm-settings-form',
                'name' => 'adm-settings-form',
                'action' => $this->_sActionUrl,
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'params' => array(
                'db' => array(
                    'table' => 'sys_options',
                    'key' => 'Name',
                    'uri' => '',
                    'uri_title' => '',
                    'submit_name' => 'save'
                ),
            ),
            'inputs' => array()
        );
        foreach($aCategories as $mixedCategory) {
            $aFields = array();

            if(!is_numeric($mixedCategory) || isset($this->_aCustomCategories[$mixedCategory]['content']))
                $aFields = $this->{$this->_aCustomCategories[$mixedCategory]['content']}();
            else if(is_numeric($mixedCategory) && (int)$mixedCategory != 0) {
                $aCategory = $this->_oDb->getRow("SELECT `ID` AS `id`, `name` AS `name` FROM `sys_options_cats` WHERE `ID`= ?", [$mixedCategory]);
                $aItems = $this->_oDb->getAll("SELECT `Name` AS `name`, `VALUE` AS `value`, `Type` AS `type`, `desc` AS `description`, `AvailableValues` AS `extra`, `check` AS `check`, `err_text` AS `check_error` 
                                               FROM `sys_options` WHERE `kateg`= ? ORDER BY `order_in_kateg`", [$mixedCategory]);

                foreach($aItems as $aItem)
                    $aFields[] = $this->_field($aItem);

                if($bWrap)
                    $aFields = $this->_wrap($aCategory, $aFields);
            }

            $aForm['inputs'] = array_merge($aForm['inputs'], $aFields);
        }
        $aForm['inputs'] = array_merge($aForm['inputs'], array(
            'cat' => array(
                'type' => 'hidden',
                'name' => 'cat',
                'value' => implode(',', $aCategories)
            ),
            'save' => array(
                'type' => 'submit',
                'name' => 'save',
                'value' => _t("_adm_btn_settings_save"),
            )
        ));
        $oForm = new BxTemplFormView($aForm);
        return $oForm;

    }
    function getForm($aCategories = array())
    {
        $oForm = $this->getFormObject($aCategories);
        $oForm->initChecker();
        return $oForm->getCode();
    }

    function _wrap($aCategory, $aFields)
    {
        $aFields = array_merge(
            array(
                'category_' . $aCategory['id'] . '_beg' => array(
                    'type' => 'block_header',
                    'caption' => $aCategory['name'],
                    'collapsable' => true,
                    'collapsed' => $aCategory['id'] != $this->_iCategoryActive
                )
            ),
            $aFields);
        $aFields['category_' . $aCategory['id'] . '_end'] = array(
            'type' => 'block_end'
        );
        return $aFields;
    }
    function _field($aItem)
    {
        $aField = array();
        switch($aItem['type']) {
            case 'digit':
                $aField = array(
                    'type' => 'text',
                    'name' => $aItem['name'],
                    'caption' => $aItem['description'],
                    'value' => $aItem['value'],
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                );
                break;

            case 'text':
                $aField = array(
                    'type' => 'textarea',
                    'name' => $aItem['name'],
                    'caption' => $aItem['description'],
                    'value' => $aItem['value'],
                    'db' => array (
                        'pass' => 'XssHtml',
                    ),
                );
                break;

            case 'checkbox':
                $aField = array(
                    'type' => 'checkbox',
                    'name' => $aItem['name'],
                    'caption' => $aItem['description'],
                    'value' => 'on',
                    'checked' => $aItem['value'] == 'on',
                    'db' => array (
                        'pass' => 'Boolean',
                    ),
                );
                break;

            case 'list':
                $aField = array(
                    'type' => 'checkbox_set',
                    'name' => $aItem['name'],
                    'caption' => $aItem['description'],
                    'value' => explode(',', $aItem['value']),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                );

                if(substr($aItem['extra'], 0, 4) == 'PHP:')
                    $aField['values'] = eval(substr($aItem['extra'], 4));
                else
                    foreach(explode(',', $aItem['extra']) as $sValue)
                        $aField['values'][$sValue] = $sValue;
                break;

            case 'select':
                $aField = array(
                    'type' => 'select',
                    'name' => $aItem['name'],
                    'caption' => $aItem['description'],
                    'value' => $aItem['value'],
                    'values' => array(),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                );

                if(substr($aItem['extra'], 0, 4) == 'PHP:')
                    $aField['values'] = eval(substr($aItem['extra'], 4));
                else
                    foreach(explode(',', $aItem['extra']) as $sValue)
                        $aField['values'][] = array('key' => $sValue, 'value' => $sValue);
                break;

            case 'select_multiple':
                $aField = array(
                    'type' => 'select_multiple',
                    'name' => $aItem['name'],
                    'caption' => $aItem['description'],
                    'value' => explode(',', $aItem['value']),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                );

                if(substr($aItem['extra'], 0, 4) == 'PHP:')
                    $aField['values'] = eval(substr($aItem['extra'], 4));
                else
                    foreach(explode(',', $aItem['extra']) as $sValue)
                        $aField['values'][$sValue] = $sValue;
                break;

            case 'file':
                $aField = array(
                    'type' => 'file',
                    'name' => $aItem['name'],
                    'caption' => $aItem['description'],
                    'value' => $aItem['value'],
                );
                break;
        }
        return $aField;
    }
    function _empty($aItem)
    {
        $mixedValue = '';
        switch($aItem['type']) {
            case 'digit':
                $mixedValue = 0;
                break;
            case 'select':
                $aValues = explode(",", $aItem['extra']);
                $mixedValue = $aValues[0];
                break;
            case 'text':
            case 'checkbox':
            case 'file':
                $mixedValue = "";
                break;
        }
        return $mixedValue;
    }

    /**
     *
     * CUSTOM CATEGORIES METHODS
     *
     */
    function _getCatTitleAdminPassword()
    {
        return _t('_adm_box_cpt_admin_password');
    }
    function _getCatContentAdminPassword()
    {
        return array(
            'pwd_old' => array(
                'type' => 'password',
                'name' => 'pwd_old',
                'caption' => _t('_adm_txt_settings_old_password'),
                'value' => ''
            ),
            'pwd_new' => array(
                'type' => 'password',
                'name' => 'pwd_new',
                'caption' => _t('_adm_txt_settings_new_password'),
                'value' => ''
            ),
            'pwd_conf' => array(
                'type' => 'password',
                'name' => 'pwd_conf',
                'caption' => _t('_adm_txt_settings_conf_password'),
                'value' => ''
            )
        );
    }
    function _saveCatAdminPassword(&$aData)
    {
        $iId = (int)$_COOKIE['memberID'];

        $aAdmin = $this->_oDb->getRow("SELECT `Password`, `Salt` FROM `Profiles` WHERE `ID`= ?", [$iId]);

        if(encryptUserPwd($aData['pwd_old'], $aAdmin['Salt']) != $aAdmin['Password'])
            return MsgBox(_t('_adm_txt_settings_wrong_old_pasword'), $this->_iResultTimer);

        $iLength = strlen($aData['pwd_new']);
        if($iLength < 3)
            return MsgBox(_t('_adm_txt_settings_wrong_new_pasword'), $this->_iResultTimer);

        if($aData['pwd_new'] != $aData['pwd_conf'])
            return MsgBox(_t('_adm_txt_settings_wrong_conf_pasword'), $this->_iResultTimer);

        $this->_oDb->query("UPDATE `Profiles` SET `Password`='" . encryptUserPwd($aData['pwd_new'], $aAdmin['Salt']) . "' WHERE `ID`='$iId'");
        createUserDataFile($iId);

        return true;
    }

    function _saveCatWatermark(&$aData)
    {
        global $dir;
        $bResult = false;
        $iImgWidth = (int)getParam('bx_photos_file_width');
        if(empty($iImgWidth))
            $iImgWidth = 100;
        $iImgHeight = (int)getParam('bx_photos_file_height');
        if(empty($iImgHeight))
            $iImgHeight = 100;

        if(!empty($aData['transparent1']))
            $bResult = $GLOBALS['MySQL']->query("UPDATE `sys_options` SET `VALUE`='" . (int)$aData['transparent1'] . "' WHERE `Name`='transparent1'") !== false;

        if(!empty($aData['enable_watermark']))
            $sValue = process_db_input($aData['enable_watermark'], BX_TAGS_STRIP);
        else
            $sValue = '';
        $bResult = $GLOBALS['MySQL']->query("UPDATE `sys_options` SET `VALUE`='$sValue' WHERE `Name`='enable_watermark'") !== false;

        if($_FILES['Water_Mark'] && $_FILES['Water_Mark']['error'] == UPLOAD_ERR_OK) {
            $aImage = getimagesize($_FILES['Water_Mark']['tmp_name']);

            if(!empty($aImage) && in_array($aImage[2], array(1, 2, 3, 6))) {
                $sPath = $dir['profileImage'] . $_FILES['Water_Mark']['name'];
                if(move_uploaded_file($_FILES['Water_Mark']['tmp_name'], $sPath)) {
                    $sOldImage = getParam('Water_Mark');
                    if(!empty($sOldImage) && ($dir['profileImage'] . $sOldImage) != $sPath)
                        @unlink($dir['profileImage'] . $sOldImage);

                    imageResize($sPath, $sPath, $iImgWidth, $iImgHeight);
                    @chmod($sPath, 0644);

                    $bResult = $GLOBALS['MySQL']->query("UPDATE `sys_options` SET  `VALUE` ='". addslashes($_FILES['Water_Mark']['name']) . "' WHERE `Name`='Water_Mark'") !== false;
                }
            }
        }

        $GLOBALS['MySQL']->oParams->clearCache();

        return $bResult ? $bResult : MsgBox(_t('_adm_txt_settings_error'), $this->_iResultTimer);
    }

    function _onSavePermalinks()
    {
        $oPermalinks = new BxDolPermalinks();
        $oPermalinks->cache();

        $oMenu = new BxDolMenu();
        $oMenu->compile();

        $GLOBALS['MySQL']->cleanCache ('sys_menu_member');
    }
}
