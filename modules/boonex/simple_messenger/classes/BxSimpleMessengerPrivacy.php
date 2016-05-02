<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolPrivacy');

    class BxSimpleMessengerPrivacy extends BxDolPrivacy
    {
        /**
         * Constructor
         */
        function __construct(&$oModule)
        {
            parent::__construct($oModule -> _oDb -> sTablePrefix . 'privacy', 'author_id', 'author_id');
        }

           /**
         * Check whether the viewer can make requested action.
         *
         * @param string $sAction action name from 'sys_priacy_actions' table.
         * @param integer $iObjectId object ID the action to be performed with.
         * @param integer $iViewerId viewer ID.
         * @return boolean result of operation.
         */
        function check($sAction, $iObjectId, $iViewerId = 0)
        {
            if(empty($iViewerId))
                $iViewerId = getLoggedId();

            $aObject = $this->_oDb->getObjectInfo($this->getFieldAction($sAction), $iObjectId);
            if(empty($aObject) || !is_array($aObject))
                return true;

            if($iViewerId == $aObject['owner_id'])
                return true;

            if($this->_oDb->isGroupMember($aObject['group_id'], $aObject['owner_id'], $iViewerId))
                return true;

            return $this->isDynamicGroupMember($aObject['group_id'], $aObject['owner_id'], $iViewerId, $iObjectId);
        }
    }
