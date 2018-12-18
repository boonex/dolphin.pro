<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

class BxMbpResponse extends BxDolAlertsResponse
{
	var $_oModule;

	function response($oAlert)
	{
            $sMethod = 'processAlert' . str_replace(' ', '', ucwords(str_replace(array('_', '-'), array(' ', ' '), $oAlert->sUnit . '_' . $oAlert->sAction)));
            if(!method_exists($this, $sMethod))
                return;

            $this->_oModule = BxDolModule::getInstance('BxMbpModule');

            $this->$sMethod($oAlert);
	}

	protected function processAlertSystemPageOutput($oAlert)
	{
            if($oAlert->aExtras['page_name'] != 'join')
                return;

            if(!$this->_oModule->_oConfig->isDisableFreeJoin())
                return;

            bx_import('PageJoin', $this->_oModule->_aModule);
            $oPage = new BxMbpPageJoin($this->_oModule);

            $oAlert->aExtras['page_code'] = $oPage->getCode();
	}

	protected function processAlertProfileShowJoinForm($oAlert)
	{
            if(!$this->_oModule->_oConfig->isDisableFreeJoin())
                return;

            list($oAlert->aExtras['sCode']) = $this->_oModule->getSelectLevelBlock(true);
	}
}
