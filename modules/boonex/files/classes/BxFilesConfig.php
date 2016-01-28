<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolFilesConfig.php');

class BxFilesConfig extends BxDolFilesConfig
{
    var $_oDb;
    var $_aMimeTypes;
    /**
     * Constructor
     */
    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->aFilesConfig = array (
            'original' => array('postfix' => '_{ext}'),
        );

        $this->aGlParams = array(
            'auto_activation' => 'bx_files_activation',
            'mode_top_index' => 'bx_files_mode_index',
            'category_auto_approve' => 'category_auto_activation_bx_files',
            'number_all' => 'bx_files_number_all',
            'number_index' => 'bx_files_number_index',
            'number_user' => 'bx_files_number_user',
            'number_related' => 'bx_files_number_related',
            'number_top' => 'bx_files_number_top',
            'number_browse' => 'bx_files_number_browse',
            'number_albums_browse' => 'bx_files_number_albums_browse',
            'number_albums_home' => 'bx_files_number_albums_home',
            'browse_width' => 'bx_files_thumb_width',
            'allowed_exts' => 'bx_files_allowed_exts',
            'profile_album_name' => 'bx_files_profile_album_name',
        );

        $this->_aMimeTypes = array();

        $this->initConfig();
    }

    function init(&$oDb)
    {
        $this->_oDb = $oDb;

        $this->_aMimeTypes = $this->_oDb->getTypeToIconArray();
    }

    function getMimeTypeIcon($sType)
    {
        if(isset($this->_aMimeTypes[$sType]))
            return $this->_aMimeTypes[$sType];

        return 'default.png';
    }
}
