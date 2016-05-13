<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolCron');
bx_import('BxDolAlerts');
bx_import('BxDolCategories');

class BxDolTextCron extends BxDolCron
{
    var $_oModule;

    function __construct()
    {
        parent::__construct();

        $this->_oModule = null;
    }

    function processing()
    {
        $aIds = array();
        if($this->_oModule->_oDb->publish($aIds))
            foreach($aIds as $iId) {
                //--- Entry -> Publish for Alerts Engine ---//
                $oAlert = new BxDolAlerts($this->_oModule->_oConfig->getAlertsSystemName(), 'publish', $iId);
                $oAlert->alert();
                //--- Entry -> Publish for Alerts Engine ---//

                //--- Reparse Global Tags ---//
                $oTags = new BxDolTags();
                $oTags->reparseObjTags($this->_oModule->_oConfig->getTagsSystemName(), $iId);
                //--- Reparse Global Tags ---//

                //--- Reparse Global Categories ---//
                $oCategories = new BxDolCategories();
                $oCategories->reparseObjTags($this->_oModule->_oConfig->getCategoriesSystemName(), $iId);
                //--- Reparse Global Categories ---//
            }
    }
}
