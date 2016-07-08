<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_BASE . 'scripts/BxBaseConfig.php' );

/***
 template variables
***/

// path to the images used in the template
$site['images']	= $site['url'] . "templates/tmpl_{$GLOBALS['tmpl']}/images/";
$site['zodiac']	= $site['url'] . "templates/base/images/zodiac/";
$site['icons']	= $site['images'] . "icons/";
$site['css_dir']= "templates/tmpl_{$GLOBALS['tmpl']}/css/";

class BxTemplConfig extends BxBaseConfig
{
    function __construct($site)
    {
        BxBaseConfig::__construct($site);
    }
}
