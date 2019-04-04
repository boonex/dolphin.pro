
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
