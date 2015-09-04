<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxTemplProfileView');

class BxDolProfilePrivatePageView extends BxTemplProfileView
{
    function BxDolProfilePrivatePageView(&$oPr, &$aSite, &$aDir)
    {
        $this->oProfileGen = &$oPr;
        $this->aConfSite = $aSite;
        $this->aConfDir  = $aDir;
        parent::BxDolPageView('profile_private');
    }

    function getBlockCode_PrivacyExplain()
    {
        return array(_t('_sys_profile_private_text'));
    }
}
