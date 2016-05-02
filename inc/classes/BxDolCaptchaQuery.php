<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

/**
 * Database queries for captcha objects.
 * @see BxDolCaptcha
 */
class BxDolCaptchaQuery extends BxDolDb
{
    protected $_aObject;

    public function __construct($aObject)
    {
        parent::__construct();
        $this->_aObject = $aObject;
    }

    static public function getCaptchaObject ($sObject)
    {
        $oDb = $GLOBALS['MySQL'];
        $sQuery = "SELECT * FROM `sys_objects_captcha` WHERE `object` = ?";
        $aObject = $oDb->getRow($sQuery, [$sObject]);
        if (!$aObject || !is_array($aObject))
            return false;

        return $aObject;
    }

}
