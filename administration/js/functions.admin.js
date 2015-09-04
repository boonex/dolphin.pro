/**
 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
 */

function adminMenuCollapse(oItem) {
	oItem = $(oItem);
	if(oItem.siblings('.adm-menu-items-wrapper').length == 0)
		return;

    if(oItem.hasClass('adm-mmh-opened')) {
    	oItem.find('.adm-menu-arrow').removeClass('adm-mma-opened').removeClass('chevron-up').addClass('chevron-down');
    	oItem.siblings('.adm-menu-items-wrapper').removeClass('adm-mmi-opened');
    	oItem.removeClass('adm-mmh-opened');
    }
    else {
    	oItem.find('.adm-menu-arrow').addClass('adm-mma-opened').removeClass('chevron-down').addClass('chevron-up');
    	oItem.siblings('.adm-menu-items-wrapper').addClass('adm-mmi-opened')
    	oItem.addClass('adm-mmh-opened');
    }
}