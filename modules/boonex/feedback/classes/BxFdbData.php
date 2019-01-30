<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextData');

class BxFdbData extends BxDolTextData
{
    function __construct(&$oModule)
    {
        parent::__construct($oModule);

        $this->_aForm['params']['db']['table'] = $this->_oModule->_oDb->getPrefix() . 'entries';
        $this->_aForm['form_attrs']['action'] = BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'post/';

        $this->_aForm['inputs']['content']['html'] = 2;
        unset($this->_aForm['inputs']['snippet']);
        unset($this->_aForm['inputs']['when']);
        unset($this->_aForm['inputs']['categories']);

        if(!$this->_oModule->_oConfig->isCommentsEnabled())
            $this->_aForm['inputs']['allow_comment_to']['type'] = 'hidden';

        if(!$this->_oModule->_oConfig->isVotesEnabled())
            $this->_aForm['inputs']['allow_vote_to']['type'] = 'hidden';
    }
}
