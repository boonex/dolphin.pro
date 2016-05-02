<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');

class BxBlogsPrivacy extends BxDolPrivacy
{
    /**
    * Constructor
    */
    function __construct(&$oModule)
    {
        parent::__construct('bx_blogs_posts', 'PostID', 'OwnerID');
    }

    /**
    * Get database field name for action.
    *
    * @param string $sAction action name.
    * @return string with field name.
    */
    function getFieldAction($sAction)
    {
        return 'allow' . str_replace(' ', '', ucwords(str_replace('_', ' ', $sAction)));
    }
}
