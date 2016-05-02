<?php

    /**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

    bx_import('BxDolMenuService');

    /**
     * @see BxDolMenuService;
     */
    class BxBaseMenuService extends BxDolMenuService
    {
        /**
         * Class constructor;
         */
        function __construct()
        {
            parent::__construct();
        }

        function getItems()
        {
            $sContent = parent::getItems();

            return $GLOBALS['oSysTemplate']->parseHtmlByName('extra_sm_thumbnail.html', array(
                'bx_if:show_thumbail' => array(
                    'condition' => $this->aMenuInfo['memberID'] != 0,
                    'content' => array(
                        'thumbnail' => get_member_icon($this->aMenuInfo['memberID'], 'left')
                    )
                ),
                'content' => $sContent
            ));
        }
    }
