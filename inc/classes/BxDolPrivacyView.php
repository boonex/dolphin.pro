<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTemplate');
bx_import('BxDolPrivacyQuery');
bx_import('BxDolPrivacySearch');

class BxDolPrivacyView extends BxDolTemplate
{
    var $_iOwnerId;
    var $_oDb;

    /**
     * constructor
     */
    function __construct($iOwnerId)
    {
        parent::__construct();

        $this->_iOwnerId = (int)$iOwnerId;
        $this->_oDb = new BxDolPrivacyQuery();
    }
    function deleteGroups($aValues)
    {
        $this->_oDb->deleteGroupsById($aValues);
    }
    function searchMembers($sValue)
    {
        $oSearch = new BxDolPrivacySearch($this->_iOwnerId, $sValue);
        return $oSearch->displayResultBlock();
    }
    function addMembers($iGroupId, $aValues)
    {
        $this->_oDb->addToGroup($iGroupId, $aValues);
    }
    function deleteMembers($iGroupId, $aValues)
    {
        $this->_oDb->deleteFromGroup($iGroupId, $aValues);
    }
    function setDefaultGroup($iGroupId)
    {
        $this->_oDb->setDefaultGroup($this->_iOwnerId, $iGroupId);
        createUserDataFile($this->_iOwnerId);
    }
    function setDefaultValues($aValues)
    {
        $aActions = $this->_oDb->getActions($this->_iOwnerId);

        foreach($aActions as $aAction) {
            $sName = 'ps-default-values_' . $aAction['action_id'];

            if(isset($aValues[$sName]))
                $this->_oDb->replaceDefaulfValue($this->_iOwnerId, $aAction['action_id'], (int)$aValues[$sName]);
        }
    }

    function _getSelectItems($aParams)
    {
        $aGroups = $this->_oDb->getGroupsBy($aParams);

        $aValues = array();
        foreach($aGroups as $aGroup) {
            if((int)$aGroup['owner_id'] == 0 && $this->_oDb->getParam('sys_ps_enabled_group_' . $aGroup['id']) != 'on')
               continue;

            $aValues[] = array('key' => $aGroup['id'], 'value' => ((int)$aGroup['owner_id'] == 0 ? _t('_ps_group_' . $aGroup['id'] . '_title') : $aGroup['title']));
        }

        return $aValues;
    }
}
