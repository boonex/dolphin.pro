<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

require_once(BX_DIRECTORY_PATH_INC . 'profiles.inc.php');

check_logged();

require_once(BX_DIRECTORY_PATH_CLASSES . 'BxDolRequest.php');
BxDolRequest::processAsAction($GLOBALS['aModule'], $GLOBALS['aRequest']);
