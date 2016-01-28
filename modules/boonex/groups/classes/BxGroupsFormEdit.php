<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_groups_import ('FormAdd');

class BxGroupsFormEdit extends BxGroupsFormAdd
{
    function __construct ($oMain, $iProfileId, $iEntryId, &$aDataEntry)
    {
        parent::__construct ($oMain, $iProfileId, $iEntryId, $aDataEntry['thumb']);

        $aFormInputsId = array (
            'id' => array (
                'type' => 'hidden',
                'name' => 'id',
                'value' => $iEntryId,
            ),
        );

        bx_import('BxDolCategories');
        $oCategories = new BxDolCategories();
        $oCategories->getTagObjectConfig ();
        $this->aInputs['categories'] = $oCategories->getGroupChooser ('bx_groups', (int)$iProfileId, true, $aDataEntry['categories']);

        $this->aInputs = array_merge($this->aInputs, $aFormInputsId);
    }

}
