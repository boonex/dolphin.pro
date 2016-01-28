<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTextData');

class BxNewsData extends BxDolTextData
{
    function __construct(&$oModule)
    {
        parent::__construct($oModule);

        $this->_aForm['params']['db']['table'] = $this->_oModule->_oDb->getPrefix() . 'entries';
        $this->_aForm['form_attrs']['action'] = BX_DOL_URL_ROOT . $this->_oModule->_oConfig->getBaseUri() . 'admin/';
        $this->_aForm['inputs']['author_id']['value'] = 0;
        $this->_aForm['inputs']['snippet']['checker']['params'][1] = $this->_oModule->_oConfig->getSnippetLength();
        $this->_aForm['inputs']['allow_comment_to'] = array(
            'type' => 'hidden',
            'name' => 'comment',
            'value' => 0,
            'db' => array (
                'pass' => 'Int',
            ),
        );
        $this->_aForm['inputs']['allow_vote_to'] = array(
            'type' => 'hidden',
            'name' => 'vote',
            'value' => 0,
            'db' => array (
                'pass' => 'Int',
            ),
        );
    }
}
