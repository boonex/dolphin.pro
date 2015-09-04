<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');

class BxVideosPrivacy extends BxDolPrivacy
{
    /**
     * Constructor
     */
    function BxVideosPrivacy($sTable = 'RayMp3Files', $sId = 'ID', $sOwner = 'Owner')
    {
        parent::BxDolPrivacy($sTable, $sId, $sOwner);
    }

    function getFieldAction($sAction)
    {
        return 'Allow' . str_replace(' ', '', ucwords(str_replace('_', ' ', $sAction)));
    }
}
