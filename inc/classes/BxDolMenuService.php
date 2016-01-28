<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxTemplMenuSimple');

    /**
     * Service menu
     *
     * Related classes:
     *  @see BxBaseMenuService - service menu base representation
     *  @see BxTemplMenuService - service menu template representation
     *
     * Table structure - `sys_menu_service`;
     *
     * Memberships/ACL:
     * no levels
     *
     * Alerts:
     * no alerts
     */
    class BxDolMenuService extends BxTemplMenuSimple
    {
        function __construct()
        {
            parent::__construct();

            $this->sName = 'service';
            $this->sDbTable = 'sys_menu_service';
            $this->sCacheKey = 'sys_menu_service';
        }
    }
