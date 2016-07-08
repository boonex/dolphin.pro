<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_events_import ('FormAdd');

class BxEventsFormEdit extends BxEventsFormAdd
{
    function __construct ($oMain, $iProfileId, $iEventId, &$aEvent)
    {
        parent::__construct ($oMain, $iProfileId, $iEventId, $aEvent['PrimPhoto']);

        $aFormInputsId = array (
            'ID' => array (
                'type' => 'hidden',
                'name' => 'ID',
                'value' => $iEventId,
            ),
        );

        bx_import('BxDolCategories');
        $oCategories = new BxDolCategories();
        $oCategories->getTagObjectConfig ();
        $this->aInputs['Categories'] = $oCategories->getGroupChooser ('bx_events', (int)$iProfileId, true, $aEvent['Categories']);

        $this->aInputs = array_merge($this->aInputs, $aFormInputsId);
    }

}
