<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolConfig');

class BxDolConnectConfig extends BxDolConfig
{
    public $sDefaultRedirectUrl;
    public $sRedirectPage;

    public $sSessionKey;
    public $sSessionUid;
    public $sSessionProfile;

    public $sEmailTemplatePasswordGenerated;
    public $sDefaultTitleLangKey;

    function __construct($aModule)
    {
        parent::__construct($aModule);

        $this->sDefaultRedirectUrl = BX_DOL_URL_ROOT . 'member.php';
    }
}
