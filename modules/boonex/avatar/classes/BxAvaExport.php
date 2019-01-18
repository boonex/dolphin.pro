<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolExport');

class BxAvaExport extends BxDolExport
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem);
        $this->_aTables = array(
            'bx_avatar_images' => '`author_id` = {profile_id}',
        );
        $this->_sFilesBaseDir = 'modules/boonex/avatar/data/images/';
        $this->_aTablesWithFiles = array(
            'bx_avatar_images' => array( // table name
                'id' => array ( // field name
                    // prefixes & extensions
                    '.jpg', 
                    'b.jpg',
                    'i.jpg'
                ),
            ),
        );
    }
}
