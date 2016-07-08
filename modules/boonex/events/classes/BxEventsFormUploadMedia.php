<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_events_import ('FormEdit');

class BxEventsFormUploadMedia extends BxEventsFormEdit
{
    function __construct ($oMain, $iProfileId, $iEntryId, &$aDataEntry, $sMedia, $aMediaFields)
    {
        parent::__construct ($oMain, $iProfileId, $iEntryId, $aDataEntry);

        foreach ($this->_aMedia as $k => $a) {
            if ($k == $sMedia)
                continue;
            unset($this->_aMedia[$k]);
        }

        array_push($aMediaFields, 'Submit', 'id');

        foreach ($this->aInputs as $k => $a) {
            if (in_array($k, $aMediaFields))
                continue;
            unset($this->aInputs[$k]);
        }
    }

}
