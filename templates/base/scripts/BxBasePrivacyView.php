<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacyView');
bx_import('BxDolPrivacySearch');
bx_import('BxTemplFormView');

class BxBasePrivacyView extends BxDolPrivacyView
{
    /**
     * constructor
     */
    function __construct($iOwnerId)
    {
        parent::__construct($iOwnerId);
    }
    function getBlockCode_GetMembers($iGroupId)
    {
        $sSearchUnitTmpl = $this->getHtml('ps_search_unit.html');
        $aIds = $this->_oDb->getMembersIds($iGroupId);

        $sResult = "";
        foreach($aIds as $aId)
           $sResult .= $this->parseHtmlByContent($sSearchUnitTmpl, array(
            'action' => 'del',
            'member_id' => $aId['id'],
            'member_thumbnail' => get_member_thumbnail($aId['id'], 'none', true)
        ));

        return $sResult;
    }
    function getBlockCode_AddMembers()
    {
         $aForm = array(
            'form_attrs' => array(
                'id' => 'ps-search-member-form',
                'name' => 'ps-search-member-form',
                'action' => BX_DOL_URL_ROOT . 'member_privacy.php',
                'method' => 'post'
            ),
            'params' => array (
                'db' => array(),
            ),
            'inputs' => array (
                'keyword' => array(
                    'type' => 'text',
                    'name' => 'keyword',
                    'caption' => _t("_ps_fcpt_keyword"),
                    'value' => '',
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,64),
                        'error' => _t('_ps_ferr_incorrect_length'),
                    ),
                    'db' => array (),
                ),
                'search' => array(
                    'type' => 'button',
                    'name' => 'search',
                    'value' => _t("_ps_btncpt_search"),
                    'attrs' => array(
                        'onclick' => 'javascript: ps_ad_search()'
                    )
                ),
            )
        );
        $oForm = new BxTemplFormView($aForm);

        //--- Get Control ---//
        $aButtons = array(
            'ps-add-members-add' => _t('_ps_btncpt_add')
        );
        $sControl = BxDolPrivacySearch::showAdminActionsPanel('ps-add-member-form', $aButtons, 'ps-add-member-ids');

        return PopupBox('ps-add-members', _t('_ps_bcpt_add_members'), $this->parseHtmlByName('ps_group_members.html', array(
            'wnd_action' => 'add',
            'bx_if:search_form' => array(
                'condition' => true,
                'content' => array(
                    'form' => $oForm->getCode(),
                )
            ),
            'js_site_url' => BX_DOL_URL_ROOT,
            'results' => '',
            'control' => $sControl,
            'loading' => LoadingBox('ps-add-members-loading')
        )));
    }
    function getBlockCode_DeleteMembers()
    {
        //--- Get Control ---//
        $aButtons = array(
            'ps-del-members-delete' => _t('_ps_btncpt_delete')
        );
        $sControl = BxDolPrivacySearch::showAdminActionsPanel('ps-del-member-form', $aButtons, 'ps-del-member-ids');

        return PopupBox('ps-del-members', _t('_ps_bcpt_delete_members'), $this->parseHtmlByName('ps_group_members.html', array(
            'wnd_action' => 'del',
            'bx_if:search_form' => array(
                'condition' => false,
                'content' => array()
            ),
            'js_site_url' => BX_DOL_URL_ROOT,
            'results' => '',
            'control' => $sControl,
            'loading' => LoadingBox('ps-add-members-loading')
        )));
    }
    function getBlockCode_DefaultValues()
    {
        $aActions = $this->_oDb->getActions($this->_iOwnerId);
        $aValues = $this->_getSelectItems(array('type' => 'extendable', 'owner_id' => $this->_iOwnerId));

        $aForm = array(
            'form_attrs' => array(
                'id' => 'ps-default-values-form',
                'name' => 'ps-default-values-form',
                'action' => BX_DOL_URL_ROOT . 'member_privacy.php',
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'params' => array(),
            'inputs' => array()
        );
        $sModuleUri = '';
        $bCollapsed = false;
        foreach($aActions as $aAction) {
            if($sModuleUri != $aAction['module_uri']) {
                if(!empty($sModuleUri))
                    $aForm['inputs'][$sModuleUri . '_end'] = array(
                        'type' => 'block_end'
                    );
                $aForm['inputs'][$aAction['module_uri'] . '_begin'] = array(
                    'type' => 'block_header',
                    'caption' => BxDolModule::getTitle($aAction['module_uri']),
                    'collapsable' => true,
                    'collapsed' => $bCollapsed
                );

                $sModuleUri = $aAction['module_uri'];
                $bCollapsed = true;
            }

            $sName = 'ps-default-values_' . $aAction['action_id'];
            $aForm['inputs'][$sName] = array(
                'type' => 'select',
                'name' => $sName,
                'caption' => _t($aAction['action_title']),
                'value' => !empty($aAction['default_value']) ? $aAction['default_value'] : $aAction['action_default_value'],
                'values' => $aValues,
                'checker' => array (
                    'func' => 'length',
                    'params' => array(1,4),
                    'error' => _t('_ps_ferr_incorrect_select'),
                )
            );
        }
        $aForm['inputs'][$sModuleUri . '_end'] = array(
            'type' => 'block_end'
        );
        $aForm['inputs']['owner_id'] = array(
            'type' => 'hidden',
            'name' => 'owner_id',
            'value' => $this->_iOwnerId,
        );
        $aForm['inputs']['ps-default-values-save'] = array(
            'type' => 'submit',
            'name' => 'ps-default-values-save',
            'value' => _t("_ps_btncpt_save")
        );
        $oForm = new BxTemplFormView($aForm);
        $sContent = $oForm->getCode();

        $sContent = $this->parseHtmlByName('ps_default_values.html', array(
            'form' => !empty($sContent) ? $sContent : MsgBox(_t('_Empty'))
        ));
        return DesignBoxContent( _t("_ps_bcpt_default_values"), $sContent, 1);
    }
    function getBlockCode_DefaultGroup()
    {
        $sValue = $this->_oDb->getDefaultGroup($this->_iOwnerId);
        $aValues = $this->_getSelectItems(array('type' => 'extendable', 'owner_id' => $this->_iOwnerId));

        $aForm = array(
            'form_attrs' => array(
                'id' => 'ps-default-group-form',
                'name' => 'ps-default-group-form',
                'action' => BX_DOL_URL_ROOT . 'member_privacy.php',
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'inputs' => array (
                'ps-default-group-ids' => array(
                    'type' => 'select',
                    'name' => 'ps-default-group-ids',
                    'caption' => _t("_ps_fcpt_groups"),
                    'info' => _t("_ps_fnote_default_group"),
                    'value' => $sValue,
                    'values' => $aValues,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(1,4),
                        'error' => _t('_ps_ferr_incorrect_select'),
                    ),
                ),
                'ps-default-group-save' => array(
                    'type' => 'submit',
                    'name' => 'ps-default-group-save',
                    'value' => _t("_ps_btncpt_save")
                ),
            )
        );
        $oForm = new BxTemplFormView($aForm);
        $sContent = $oForm->getCode();

        $sContent = $this->parseHtmlByName('ps_default_group.html', array(
            'form' => !empty($sContent) ? $sContent : MsgBox(_t('_Empty'))
        ));
        return DesignBoxContent( _t("_ps_bcpt_default_group"), $sContent, 1);
    }
    function getBlockCode_MyGroups()
    {
        $sExtendedTxt = _t("_ps_cpt_extended");

        //--- Get Content ---//
        $aContent = array();
        $aGroups = $this->_oDb->getGroupsBy(array('type' => 'owner', 'owner_id' => $this->_iOwnerId));
        foreach($aGroups as $aGroup) {
            if(!empty($aGroup['parent_id'])) {
                $aParentGroup = $this->_oDb->getGroupsBy(array('type' => 'id', 'id' => $aGroup['parent_id']));

                $sTitle = ((int)$aParentGroup['owner_id'] == 0 ? _t('_ps_group_' . $aParentGroup['id'] . '_title') : $aParentGroup['title']);
                $sExtend = ' ' . $sExtendedTxt . ' ';
                $sExtend .= !empty($aParentGroup['home_url']) ? '<a href="' . BX_DOL_URL_ROOT . $aParentGroup['home_url'] . '" target="_blank">' . $sTitle . '</a>' : $sTitle;
            }
            $aContent[] = array(
                'group_id' => $aGroup['id'],
                'group_title' => $aGroup['title'],
                'group_members' => $aGroup['members_count'],
                'bx_if:extended' => array(
                    'condition' => !empty($aGroup['parent_id']),
                    'content' => array(
                        'group_extended' => $sExtend,
                    )
                ),
                'add_img_url' => $sAddImgUrl,
                'add_img_title' => $sAddImgTxt,
                'delete_img_url' => $sDeleteImgUrl,
                'delete_img_title' => $sDeleteImgTxt
            );
        }
        //--- Get Control ---//
        $aButtons = array(
            'ps-my-groups-delete' => _t('_ps_btncpt_delete')
        );
        $sControl = BxDolPrivacySearch::showAdminActionsPanel('ps-my-groups-form', $aButtons, 'ps-my-groups-ids');

        $sContent = $this->parseHtmlByName('ps_my_groups.html', array(
            'bx_repeat:groups' => !empty($aContent) ? $aContent : MsgBox(_t('_Empty')),
            'control' => $sControl
        ));
        return DesignBoxContent( _t("_ps_bcpt_my_groups"), $sContent, 1);
    }

    function getBlockCode_CreateGroup()
    {
        $sContent = "";
        $aValues = array_merge(array('0' => _t('_ps_cpt_none')), $this->_getSelectItems(array('type' => 'extendable', 'owner_id' => $this->_iOwnerId)));

        $aForm = array(
            'form_attrs' => array(
                'id' => 'ps-create-group-form',
                'name' => 'ps-create-group-form',
                'action' => BX_DOL_URL_ROOT . 'member_privacy.php',
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ),
            'params' => array (
                'db' => array(
                    'table' => 'sys_privacy_groups',
                    'key' => 'id',
                    'submit_name' => 'create'
                ),
            ),
            'inputs' => array (
                'owner_id' => array(
                    'type' => 'hidden',
                    'name' => 'owner_id',
                    'value' => $this->_iOwnerId,
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                'title' => array(
                    'type' => 'text',
                    'name' => 'title',
                    'caption' => _t("_ps_fcpt_title"),
                    'value' => '',
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(3,64),
                        'error' => _t('_ps_ferr_incorrect_length'),
                    ),
                    'db' => array (
                        'pass' => 'Xss',
                    ),
                ),
                'parent_id' => array(
                    'type' => 'select',
                    'name' => 'parent_id',
                    'caption' => _t("_ps_fcpt_extends"),
                    'value' => '',
                    'values' => $aValues,
                    'checker' => array (
                        'func' => 'length',
                        'params' => array(1,4),
                        'error' => _t('_ps_ferr_incorrect_select'),
                    ),
                    'db' => array (
                        'pass' => 'Int',
                    ),
                ),
                'create' => array(
                    'type' => 'submit',
                    'name' => 'create',
                    'value' => _t("_ps_btncpt_create"),
                ),
            )
        );
        $oForm = new BxTemplFormView($aForm);
        $oForm->initChecker();

        if($oForm->isSubmittedAndValid()) {
            $iId = $oForm->insert();

            header('Location: ' . $oForm->aFormAttrs['action']);
            exit;
        } else
            $sContent = $oForm->getCode();

        $sContent = $this->parseHtmlByName('ps_create_group.html', array(
            'form' => !empty($sContent) ? $sContent : MsgBox(_t('_Empty'))
        ));
        return DesignBoxContent( _t("_ps_bcpt_create_group"), $sContent, 1);
    }
}
