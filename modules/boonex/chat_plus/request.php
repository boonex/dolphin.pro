<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolRequest.php' );

check_logged();

if ( empty($aRequest) || empty($aRequest[0]) ) {
    BxDolRequest::processAsFile($aModule, $aRequest);
} else {
    BxDolRequest::processAsAction($aModule, $aRequest);
}
