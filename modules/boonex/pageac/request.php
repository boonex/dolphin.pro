<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolRequest.php' );
if(isset($aRequest[0]) && strpos($aRequest[0], 'action_') !== false ) {
    $aRequest[0] = str_replace('action_', '', $aRequest[0]);
    echo BxDolRequest::processAsAction($aModule, $aRequest);
} else BxDolRequest::processAsFile($aModule, $aRequest);
