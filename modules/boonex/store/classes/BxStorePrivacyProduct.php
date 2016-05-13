<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');

class BxStorePrivacyProduct extends BxDolPrivacy
{
    var $oModule;

    /**
     * Constructor
     */
    function __construct(&$oModule)
    {
        $this->oModule = $oModule;
        parent::__construct($oModule->_oDb->getPrefix() . 'products', 'id', 'author_id');
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
        if ('c' == $mixedGroupId) { // customers only
            $aDataEntry = array ('id' => $iObjectId, 'author_id' => $iObjectOwnerId);
            return $this->oModule->isCustomer ($aDataEntry);
        }
        return false;
    }
}
