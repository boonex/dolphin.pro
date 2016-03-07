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
            'browse_width' => 'bx_files_thumb_width',
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
