<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('Module', $aModule);

check_logged();

$oPayments = new BxPmtModule($aModule);
$oPayments->actionJoin();