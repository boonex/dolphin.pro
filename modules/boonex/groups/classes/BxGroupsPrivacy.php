<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');

class BxGroupsPrivacy extends BxDolPrivacy
{
    var $oModule;

    /**
     * Constructor
     */
    function __construct(&$oModule)
    {
        $this->oModule = $oModule;
        parent::__construct($oModule->_oDb->getPrefix() . 'main', 'id', 'author_id');
    }

    /**
     * Check whethere viewer is a member of dynamic group.
     *
     * @param  mixed   $mixedGroupId   dynamic group ID.
     * @param  integer $iObjectOwnerId object owner ID.
     * @param  integer $iViewerId      viewer ID.
     * @return boolean result of operation.
     */
    function isDynamicGroupMember($mixedGroupId, $iObjectOwnerId, $iViewerId, $iObjectId)
    {
        $aDataEntry = array ('id' => $iObjectId, 'author_id' => $iObjectOwnerId);
        if ('f' == $mixedGroupId)  // fans only
            return $this->oModule->isFan ($aDataEntry, $iViewerId, true);
        elseif ('a' == $mixedGroupId) // admins only
            return $this->oModule->isEntryAdmin ($aDataEntry, $iViewerId);
        return false;
    }
}
