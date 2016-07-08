<?php

/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

/**
 * Spam detection based on the message content and logged in user
 */
class BxDolAkismet
{
    var $oAkismet = null;

    /**
     * Constructor
     */
    public function __construct($iProfileID = 0)
    {
        $sKey = getParam('sys_akismet_api_key');
        if ($sKey) {
            require_once (BX_DIRECTORY_PATH_PLUGINS . 'akismet/Akismet.class.php');
            $this->oAkismet = new Akismet(BX_DOL_URL_ROOT, $sKey);
            $aProfile = getProfileInfo($iProfileID);
            if ($aProfile) {
                $this->oAkismet->setCommentAuthor($aProfile['NickName']);
                $this->oAkismet->setCommentAuthorEmail($aProfile['Email']);
                $this->oAkismet->setCommentAuthorURL(getProfileLink($aProfile['ID']));
            }
        }
    }

    public function isSpam ($s, $sPermalink = false)
    {
        if (!$this->oAkismet)
            return false;

        $this->oAkismet->setCommentContent($s);
        if ($sPermalink)
            $this->oAkismet->setPermalink($sPermalink);

        return $this->oAkismet->isCommentSpam();
    }

    public function onPositiveDetection ($sExtraData = '')
    {
        $o = bx_instance('BxDolDNSBlacklists');
        $o->onPositiveDetection (getVisitorIP(), $sExtraData, 'akismet');
    }
}
