<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolPrivacy');
bx_import('BxDolTextSiteMaps');

/**
 * Sitemaps generator for Articles
 */
class BxArlSiteMaps extends BxDolTextSiteMaps
{
    protected function __construct($aSystem)
    {
        parent::__construct($aSystem, BxDolModule::getInstance('BxArlModule'));
    }
}
