<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolDb');

class BxDolPaymentsQuery extends BxDolDb
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getObjects()
    {
        $aObjects = $this->getAll("SELECT * FROM `sys_objects_payments` WHERE 1");
        if(empty($aObjects) || !is_array($aObjects))
            return array();

        return $aObjects;
    }
}
