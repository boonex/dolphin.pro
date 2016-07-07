<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once( BX_DIRECTORY_PATH_CLASSES . 'BxDolRequest.php' );
if(empty($aRequest[0]) || $aRequest[0] == 'index')
    BxDolRequest::processAsFile($aModule, $aRequest);
else
    echo BxDolRequest::processAsAction($aModule, $aRequest);
