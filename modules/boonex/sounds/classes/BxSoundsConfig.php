<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolFilesConfig.php');
require_once(BX_DIRECTORY_PATH_ROOT . "flash/modules/mp3/inc/constants.inc.php");

class BxSoundsConfig extends BxDolFilesConfig
{
    /**
     * Constructor
     */
    function BxSoundsConfig (&$aModule)
    {
        parent::BxDolFilesConfig($aModule);

        // only image files can added/removed here, changing list of sound files requires source code modification
        // image files support square resizing, just specify 'square' => true
        $this->aFilesConfig = array (
        	'browse' => array('postfix' => SCREENSHOT_EXT, 'fallback' => 'default.png', 'image' => true, 'w' => 240, 'h' => 240, 'square' => true),
        	'browse2x' => array('postfix' => '-2x' . SCREENSHOT_EXT, 'fallback' => 'default.png', 'image' => true, 'w' => 480, 'h' => 480, 'square' => true),
            'file' => array('postfix' => MP3_EXTENSION),
        );

        $this->aGlParams = array(
            'mode_top_index' => 'bx_sounds_mode_index',
            'category_auto_approve' => 'category_auto_activation_bx_sounds',
            'number_all' => 'bx_sounds_number_all',
            'number_index' => 'bx_sounds_number_index',
            'number_related' => 'bx_sounds_number_related',
            'number_top' => 'bx_sounds_number_top',
            'number_previous_rated' => 'bx_sounds_number_previous_rated',
            'number_albums_browse' => 'bx_sounds_number_albums_browse',
            'number_albums_home' => 'bx_sounds_number_albums_home',
            'file_width' => 'bx_sounds_file_width',
            'file_height' => 'bx_sounds_file_height',
            'allowed_exts' => 'bx_sounds_allowed_exts',
            'profile_album_name' => 'bx_sounds_profile_album_name',
        );

        $this->initConfig();
    }

    function getFilesPath ()
    {
        return BX_DIRECTORY_PATH_ROOT . 'flash/modules/mp3/files/';
    }

    function getFilesUrl ()
    {
        return BX_DOL_URL_ROOT . 'flash/modules/mp3/files/';
    }
}
