<?php
/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

bx_import('BxDolTwigFormBroadcast');

class BxEventsFormBroadcast extends BxDolTwigFormBroadcast
{
    function __construct ()
    {
        parent::__construct (_t('_bx_events_form_caption_broadcast_title'), _t('_bx_events_form_err_broadcast_title'), _t('_bx_events_form_caption_broadcast_message'), _t('_bx_events_form_err_broadcast_message'));
    }
}
