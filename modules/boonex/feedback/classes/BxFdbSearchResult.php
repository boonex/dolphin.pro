<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextSearchResult');

class BxFdbSearchResult extends BxDolTextSearchResult
{
    function __construct($oModule = null)
    {
        $oModule = !empty($oModule) ? $oModule : BxDolModule::getInstance('BxFdbModule');
        parent::__construct($oModule);

        $this->aCurrent['searchFields'] = array('caption', 'content', 'tags');
        unset($this->aCurrent['restriction']['category']);
    }
    function getAlterOrder()
    {
        return array('order' => 'ORDER BY `date` DESC');
    }
}
