/***************************************************************************
 *                            Dolphin Web Community Software
 *                              -------------------
 *     begin                : Mon Mar 23 2006
 *     copyright            : (C) 2007 BoonEx Group
 *     website              : http://www.boonex.com
 *
 *
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This is a free software; you can modify it under the terms of BoonEx
 *   Product License Agreement published on BoonEx site at http://www.boonex.com/downloads/license.pdf
 *   You may not however distribute it for free or/and a fee.
 *   This notice may not be removed from the source code. You may not also remove any other visible
 *   reference and links to BoonEx Group as provided in source code.
 *
 ***************************************************************************/

function BxDolFiles(oOptions) {
	this.oOptions = oOptions;
}

BxDolFiles.prototype.edit = function(iId) {
	var oPopupOptions = {
		closeOnOuterClick: false, 
		onShow: function() {
			$(document).addWebForms();
		}
	};
	showPopupAnyHtml(this.oOptions.sBaseUrl + 'edit/' + iId, oPopupOptions);
};